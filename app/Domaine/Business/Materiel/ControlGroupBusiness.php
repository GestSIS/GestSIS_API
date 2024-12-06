<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;

/**
 * Model for manipulating 'control_group', 'control_group_help',
 * 'control_group_location' and 'control_group_exec' database tables
 * Available public methods
 * @static getControlGroup($id)
 * @static listControlGroups($id, $isFull)
 * @static listControlGroupsByUser($id)
 * @static createControlGroup($group)
 * @static editControlGroup($id, $data)
 * @static deleteControlGroup($id)
 * @static executeControlForGroup($id, $data)
 */
class ControlGroupBusiness
{
  /**
   * Get infos of a control group
   * @param integer $id ID of the control group to get
   * @return #control_group_existing_full
   */
  public static function getControlGroup($id)
  {
    return self::getControlGroupsInternal($id, true, true);
  }

  /**
   * Get list of control groups of a given control
   * @param integer $id ID of the control
   * @param boolean $isFull True to get full details, False for name and next
   * @return Collection of #control_group_existing_full or #control_group_existing_basic
   */
  public static function listControlGroups($id, $isFull)
  {
    return self::getControlGroupsInternal($id, false, $isFull);
  }

  /**
   * Get list of control groups for which a user is manager or helper
   * @param integer $id ID of the user
   * @return Collection of #control_group_existing_foruser
   */
  public static function listControlGroupsByUser($id)
  {

    // Build query to get control groups for which user is manager
    $query = <<<EOF
      SELECT CG.id AS id, CG.name AS name,
             C.id AS control_id, C.name AS control_name
      FROM control_group CG
        INNER JOIN control C ON CG.control_id = C.id
      WHERE CG.manager_id = ?
EOF;

    // Execute query
    $managedGroups = self::db()->select($query, [$id]);

    // Build query to get control groups for which user is helper
    $query = <<<EOF
      SELECT CG.id AS id, CG.name AS name,
             C.id AS control_id, C.name AS control_name
      FROM control_group_help CGH
        INNER JOIN control_group CG ON CGH.control_group_id = CG.id
        INNER JOIN control C ON CG.control_id = C.id
      WHERE CGH.user_id = ?
EOF;

    // Execute query
    $helpedGroups = self::db()->select($query, [$id]);

    // Function for returning one result
    $formatResult = function ($isManager) {
      return function ($group) use ($isManager) {
        return [
          'id' => $group->id,
          'name' => $group->name,
          'control' => [
            'id' => $group->control_id,
            'name' => $group->control_name
          ],
          'isManager' => $isManager
        ];
      };
    };

    // Return results
    return Arrays::merge(
      Arrays::each(
        $managedGroups,
        $formatResult(true)
      ),
      Arrays::each(
        $helpedGroups,
        $formatResult(false)
      )
    );
  }

  /**
   * Get control group(s) by control group ID or control ID with desired details
   * @param integer $id ID of the control group or control
   * @param boolean $isOnlyOne True to get only one control group, false for full control
   * @param boolean $isFullDetails True to get all details, False for names and next
   * @return Collection|Object of #control_group_existing_full or #control_group_existing_basic
   */
  private static function getControlGroupsInternal($id, $isOnlyOne, $isFullDetails)
  {

    // Build query
    $where = $isOnlyOne ? "CG.id = ?" : "C.id = ?";
    $query = <<<EOF
      SELECT CG.id AS id, CG.name AS name,
             C.id AS control_id, C.name AS control_name,
             U.id AS manager_id, U.name AS manager_name
      FROM control_group CG
        INNER JOIN control C ON CG.control_id = C.id
        INNER JOIN user U ON CG.manager_id = U.id
      WHERE $where
EOF;

    // Execute query
    $groups = self::db()->select($query, [$id]);
    $count = Arrays::size($groups);

    // If no control group found, return null or []
    if ($count === 0) {
      return $isOnlyOne ? null : [];
    }

    // Function for returning one control group
    $formatResult = function ($group) use ($isFullDetails) {
      $nextExecutionDate = self::getNextExecutionDateForControlGroup($group->id);
      return Arrays::merge(
        [
          'id' => $group->id,
          'name' => $group->name
        ],
        $isFullDetails
        ? [
          'control' => [
            'id' => $group->control_id,
            'name' => $group->control_name
          ],
          'manager' => [
            'id' => $group->manager_id,
            'name' => $group->manager_name
          ],
          'locations' => LocationModel::getLocationsOfControlGroup($group->id),
          'helpers' => UserModel::getUsersForControlGroup($group->id),
          'executions' => self::getExecutionsForControlGroup($group->id)
        ]
        : [],
        $nextExecutionDate
        ? [
          'next' => $nextExecutionDate
        ]
        : []
      );
    };

    // Otherwise return results
    return $isOnlyOne
      ? $formatResult(Arrays::first($groups))
      : Arrays::each($groups, $formatResult);
  }

  /**
   * Create a new control group
   * @param Array $group #control_group_new Properties of the new control group
   * @return #idobj ID of the created control group
   */
  public static function createControlGroup($group)
  {

    // Create control group
    $groupId = self::create("control_group", [
      'control_id' => $group['control']['id'],
      'name' => $group['name'],
      'manager_id' => $group['manager']['id']
    ]);

    // Add helpers (users)
    foreach ($group['helpers'] as $helper) {
      self::create("control_group_help", [
        'control_group_id' => $groupId['id'],
        'user_id' => $helper['id']
      ]);
    }

    // Add locations
    foreach ($group['locations'] as $location) {
      self::create("control_group_location", [
        'control_group_id' => $groupId['id'],
        'location_id' => $location['id']
      ]);
    }

    // Return control group ID
    return $groupId;
  }

  /**
   * Edit an existing control group
   * @param integer $id ID of the control group to edit
   * @param Array $data #control_group_edit Properties of the control group to modify
   */
  public static function editControlGroup($id, $data)
  {

    // Edit basic infos about control group
    self::edit("control_group", $id, [
      'name' => $data['name'],
      'manager_id' => $data['manager']['id']
    ]);

    // Get list of locations currently registered in control group
    $oldLocations = Arrays::each(
      LocationModel::getLocationsOfControlGroup($id),
      function ($location) {
        return $location['id'];
      }
    );

    // Get list of locations newly registered in control group
    $newLocations = Arrays::each(
      $data['locations'],
      function ($location) {
        return $location['id'];
      }
    );

    // Go through old list and remove locations not in new list
    foreach ($oldLocations as $oldLocation) {
      if (!Arrays::contains($newLocations, $oldLocation)) {
        self::deleteFromAssociative("control_group_location", $id, "location_id", $oldLocation, "emplacement");
      }
    }

    // Go through new list and add locations not in old list
    foreach ($newLocations as $newLocation) {
      if (!Arrays::contains($oldLocations, $newLocation)) {
        self::create("control_group_location", [
          'control_group_id' => $id,
          'location_id' => $newLocation
        ]);
      }
    }

    // Get list of helpers currently registered in control group
    $oldHelpers = Arrays::each(
      UserModel::getUsersForControlGroup($id),
      function ($user) {
        return $user['id'];
      }
    );

    // Get list of helpers newly registered in control group
    $newHelpers = Arrays::each(
      $data['helpers'],
      function ($user) {
        return $user['id'];
      }
    );

    // Go through old list and remove helpers not in new list
    foreach ($oldHelpers as $oldHelper) {
      if (!Arrays::contains($newHelpers, $oldHelper)) {
        self::deleteFromAssociative("control_group_help", $id, "user_id", $oldHelper, "aide");
      }
    }

    // Go through new list and add helpers not in old list
    foreach ($newHelpers as $newHelper) {
      if (!Arrays::contains($oldHelpers, $newHelper)) {
        self::create("control_group_help", [
          'control_group_id' => $id,
          'user_id' => $newHelper
        ]);
      }
    }
  }

  /**
   * Delete an existing control group
   * @param integer $id ID of the control group to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteControlGroup($id)
  {
    return self::delete("control_group", $id);
  }

  /**
   * Record execution of a control for a group
   * @param integer $id ID of the control group to execute
   * @param Array $data #control_group_execution Properties of the execution
   */
  public static function executeControlForGroup($id, $data)
  {
    return self::create("control_group_exec", [
      'control_group_id' => $id,
      'date' => $data['date']
    ]);
  }

  /**
   * @internal
   * Get executions of a given control group, sorted most recent first
   * @param integer $groupId ID of the control group
   * @return Collection of #control_group_execution
   */
  private static function getExecutionsForControlGroup($groupId)
  {

    // Build query
    $query = <<<EOF
      SELECT date
      FROM control_group_exec
      WHERE control_group_id = ?
      ORDER BY date DESC
EOF;

    // Execute query
    $executions = self::db()->select($query, [$groupId]);

    // If no execution found, return empty array
    if (Arrays::size($executions) === 0) {
      return [];
    }

    // Build an array of #control_group_execution
    return array_values(
      Arrays::each(
        $executions,
        function ($execution) {
          return [
            'date' => $execution->date
          ];
        }
      )
    );
  }

  /**
   * @internal
   * Get date of next execution of a given control group, or null if not periodic
   * @param integer $groupId ID of the control group
   * @return string|null Date string of next execution
   */
  private static function getNextExecutionDateForControlGroup($groupId)
  {

    // Retrieve control periodicity indication
    $query = <<<EOF
      SELECT C.recurrence_periodic AS recurrence_periodic
      FROM control_group G
      INNER JOIN control C ON G.control_id = C.id
      WHERE G.id = ?
EOF;
    $controls = self::db()->select($query, [$groupId]);
    $control = Arrays::first($controls);

    // If control is not periodic, return null
    if (!($control->recurrence_periodic)) {
      return null;
    }

    // Retrieve all executions of the group
    $executions = self::getExecutionsForControlGroup($groupId);

    // If no execution yet, must be executed ASAP
    if (Arrays::size($executions) === 0) {
      return 'ASAP';
    }

    // Get last execution date
    $lastExecutionDate = Arrays::first($executions)['date'];

    // Compute next execution date
    $nextExecutionDate = new \DateTime($lastExecutionDate);
    $nextExecutionDate->add(new \DateInterval('P' . $control->recurrence_periodic . 'M'));
    return $nextExecutionDate->format('Y-m-d');
  }

  /**
   * @internal
   * Delete from associative tables (helpers and locations)
   * @param string $table Name of the table to delete from
   * @param integer $groupId ID of the control group
   * @param string $assocKey Name of the associative column
   * @param integer $assocVal Value of the associative column
   * @param string $entityName Entity name for error messages
   */
  private static function deleteFromAssociative($table, $groupId, $assocKey, $assocVal, $entityName)
  {

    // Get entity strings
    $entityPlural = static::entityPlural();
    $entityFeminine = static::entityFeminine();

    // Build query
    $query = "DELETE FROM $table WHERE control_group_id = ? AND $assocKey = ?";

    // Execute query
    $count = self::db()->delete($query, [$groupId, $assocVal]);

    // If count > 1, internal error
    if ($count > 1) {
      throw new InternalException(
        InternalException::WRONG_DATABASE_BEHAVIOUR,
        "Erreur lors du traitement d'un $entityName avec l'ID $assocVal : $count ont été supprimés"
      );
    }

    // If count = 0, entity does not exist
    else if ($count === 0) {
      throw new InternalException(
        InternalException::WRONG_DATABASE_BEHAVIOUR,
        "Erreur lors du traitement d'un $entityName avec l'ID $assocVal : impossible de supprimer"
      );
    }
  }

}
