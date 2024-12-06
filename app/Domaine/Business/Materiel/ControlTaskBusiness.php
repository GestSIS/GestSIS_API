<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;

/**
 * Model for manipulating 'control_simpletask' database table
 * Available public methods
 * @static getControlTask($id)
 * @static listControlTasks($id)
 * @static getControlTasksByUser($userId)
 * @static createControlTask($task)
 * @static editControlTask($id, $data)
 * @static deleteControlTask($id)
 */
class ControlTaskBusiness
{
  /**
   * Get a single control task
   * @param integer $id ID of the control task to get
   * @return #control_task_existing
   */
  public static function getControlTask($id)
  {
    return self::getControlTasksInternal($id, true);
  }

  /**
   * Get all tasks in a given control
   * @param integer $id ID of the control
   * @return Collection of #control_task_existing
   */
  public static function listControlTasks($id)
  {
    return self::getControlTasksInternal($id, false);
  }

  /**
   * @internal
   * Get control task(s) by ID or control
   * @param integer $id ID of the task or control
   * @param boolean $isOnlyOne True to get one task, False to get a control
   * @return Collection|Object #control_task_existing
   */
  private static function getControlTasksInternal($id, $isOnlyOne)
  {

    // Build query
    $where = $isOnlyOne ? "T.id = ?" : "C.id = ?";
    $query = <<<EOF
      SELECT T.id AS id, T.description AS description, T.contact_type AS contact_type, C.id AS control_id, C.name AS control_name, U.id AS user_id, U.name AS user_name
      FROM control_simpletask T
      INNER JOIN control C ON T.control_id = C.id
      LEFT JOIN user U ON T.contact_id = U.id
      WHERE $where
EOF;

    // Execute query
    $tasks = self::db()->select($query, [$id]);
    $count = Arrays::size($tasks);

    // If no task found, return null or []
    if ($count === 0) {
      return $isOnlyOne ? null : [];
    }

    // Function for returning one task
    $formatResult = function ($task) {
      $contactType = self::convertContactTypeToString($task->contact_type);
      return [
        'id' => $task->id,
        'control' => [
          'id' => $task->control_id,
          'name' => $task->control_name
        ],
        'description' => $task->description,
        'contact' => Arrays::merge(
          [
            'type' => $contactType
          ],
          $contactType === 'SPECIFIC'
          ? [
            'user' => [
              'id' => $task->user_id,
              'name' => $task->user_name
            ]
          ]
          : []
        )
      ];
    };

    // Otherwise return results
    return $isOnlyOne
      ? $formatResult(Arrays::first($tasks))
      : Arrays::each($tasks, $formatResult);
  }

  /**
   * List tasks having a specific user as contact
   * @param integer $userId ID of the user
   * @return Collection of #control_contact_foruser
   */
  public static function getControlTasksByUser($userId)
  {

    // Build query
    $query = <<<EOF
      SELECT T.description AS task,
             C.id AS control_id, C.name AS control_name
      FROM control_simpletask T
        INNER JOIN control C ON T.control_id = C.id
      WHERE T.contact_type = 2
        AND T.contact_id = ?
EOF;

    // Execute query
    $tasks = self::db()->select($query, [$userId]);

    // Function for returning one result
    $formatResult = function ($task) {
      return [
        'control' => [
          'id' => $task->control_id,
          'name' => $task->control_name
        ],
        'task' => $task->task
      ];
    };

    // Return results
    return Arrays::each(
      $tasks,
      $formatResult
    );
  }

  /**
   * Create a new control task
   * @param Array $task #control_task_new Properties of the new control task
   * @return #idobj ID of the created control task
   */
  public static function createControlTask($task)
  {
    return self::create("control_simpletask", [
      'control_id' => $task['control']['id'],
      'description' => $task['description'],
      'contact_type' => self::convertContactTypeToInt($task['contact']['type']),
      'contact_id' => $task['contact']['type'] === 'SPECIFIC'
        ? $task['contact']['user']['id']
        : null
    ]);
  }

  /**
   * Edit an existing control task
   * @param integer $id ID of the control task to edit
   * @param Array $data #control_task_edit Properties of the control task to modify
   */
  public static function editControlTask($id, $data)
  {
    return self::edit("control_simpletask", $id, [
      'description' => $data['description'],
      'contact_type' => self::convertContactTypeToInt($data['contact']['type']),
      'contact_id' => $data['contact']['type'] === 'SPECIFIC'
        ? $data['contact']['user']['id']
        : null
    ]);
  }

  /**
   * Delete an existing control task
   * @param integer $id ID of the control task to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteControlTask($id)
  {
    return self::delete("control_simpletask", $id);
  }

}
