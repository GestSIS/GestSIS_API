<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\BadRequestException;
use App\Exceptions\InternalException;

/**
 * Model for manipulating 'maintenance_exec' database table
 * Available public methods
 * @static getExecution($id)
 * @static createExecutionPhase1($execution)
 * @static createExecutionPhase2($execution)
 * @static editExecution($id, $data)
 * @static deleteExecution($id)
 */
class ExecutionBusiness
{
  const STATUS_NONE = "NONE";
  const STATUS_SUCCESS = "SUCCESS";
  const STATUS_FAILURE = "FAILURE";

  /**
   * Get informations about an execution for exiting purposes
   * @param integer $id ID of the execution to retrieve
   * @return #execution_existing_read
   */
  public static function getExecution($id)
  {

    // Build query
    $query = <<<EOF
      SELECT
        E.id AS id,
        E.name AS name,
        E.date AS date,
        E.people AS people,
        E.remark AS remark,
        U.id AS user_id,
        U.name AS user_name
      FROM maintenance_exec E
      INNER JOIN user U ON E.user_id = U.id
      WHERE E.id = ?
EOF;

    // Execute query
    $executions = self::db()->select($query, [$id]);
    $count = Arrays::size($executions);

    // If no execution found, return null
    if ($count === 0) {
      return null;
    }

    // Otherwise return first execution
    $execution = Arrays::first($executions);
    return [
      'id' => $execution->id,
      'name' => $execution->name,
      'date' => $execution->date,
      'people' => $execution->people,
      'remark' => $execution->remark,
      'user' => [
        'id' => $execution->user_id,
        'name' => $execution->user_name
      ]
    ];
  }

  /**
   * Create a maintenance execution, phase 1
   * @param #execution_new $execution Properties of the new execution
   * @param integer $userId ID of the user who performs creation
   * @return #execution_rows Infos to fill in phase 2
   */
  public static function createExecutionPhase1($execution, $userId)
  {

    // Get maintenance ID
    $maintenanceId = $execution['maintenance']['id'];

    // Create execution in database
    $idobj = self::create("maintenance_exec", [
      'maintenance_id' => $maintenanceId,
      'name' => $execution['name'],
      'date' => $execution['date'],
      'people' => $execution['people'],
      'user_id' => $userId,
      'remark' => $execution['remark']
    ]);

    // Get rows to fill
    $locations = MaintenanceModel::getMaintenanceRowsToFill($maintenanceId, $execution['date']);

    // Map output to correct JSON format
    return [
      'id' => $idobj['id'],
      'locations' => $locations
    ];
  }

  /**
   * Create a maintenance execution, phase 2
   * @param #execution_rows Rows to fill with maintenance infos
   * @param integer $userId ID of the user who performs creation (unused)
   */
  public static function createExecutionPhase2($execution, $userId)
  {

    // Check if rows exist for the current execution
    $query = <<<EOF
      SELECT COUNT(*) AS `count`
      FROM maintenance_exec_row
      WHERE exec_id = ?
EOF;
    $count = Arrays::first(self::db()->select($query, [$execution['id']]))->count;
    if ($count > 0) {
      throw new BadRequestException(
        BadRequestException::FORBIDDEN_OPERATION,
        "L'exécution avec l'ID {$execution['id']} existe déjà et des lignes ne peuvent plus être ajoutées."
      );
    }

    // Create all rows according to given input
    foreach ($execution['locations'] as $location) {
      foreach ($location['items'] as $item) {
        self::create("maintenance_exec_row", Arrays::merge(
          [
            'exec_id' => $execution['id'],
            'item_id' => $item['item']['id'],
            'executed' => $item['status'] === self::STATUS_NONE ? 0 : 1,
            'success' => $item['status'] === self::STATUS_SUCCESS ? 1 : 0
          ],
          $item['status'] === self::STATUS_FAILURE
          ? [
            'remark' => $item['remark']
          ]
          : []
        ));
      }
    }
  }

  /**
   * Edit basic informations of an existing maintenance execution
   * @param integer $id ID of the execution to edit
   * @param #execution_existing_write $data Properties of the execution to modify
   */
  public static function editExecution($id, $data)
  {
    return self::edit("maintenance_exec", $id, [
      'name' => $data['name'],
      'people' => $data['people'],
      'remark' => $data['remark']
    ]);
  }

  /**
   * Delete a being-created execution
   * @param integer $id ID of the execution to delete
   * @return boolean true if deleted successfully
   * @throws BadRequestException::FORBIDDEN_OPERATION when trying to delete an execution which has been fully created
   */
  public static function deleteExecution($id)
  {

    // Check if rows exist for the current execution
    $query = <<<EOF
      SELECT COUNT(*) AS `count`
      FROM maintenance_exec_row
      WHERE exec_id = ?
EOF;
    $count = Arrays::first(self::db()->select($query, [$id]))->count;
    if ($count > 0) {
      throw new BadRequestException(
        BadRequestException::FORBIDDEN_OPERATION,
        "L'exécution avec l'ID $id ne peut pas être supprimée car elle contient des lignes de maintenance."
      );
    }

    // Proceed with deletion
    return self::delete("maintenance_exec", $id);
  }
}
