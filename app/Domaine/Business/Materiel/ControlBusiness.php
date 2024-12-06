<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;

/**
 * Model for manipulating 'control' database table
 * Available public methods
 * @static listControls()
 * @static listControlsNext($productId)
 * @static getControlBasic($id)
 * @static getControlFull($id)
 * @static createControl($control)
 * @static editControl($id, $data)
 * @static deleteControl($id)
 */
class ControlBusiness
{
  /**
   * Get list of controls
   * @return Collection of #control_existing_forlist
   */
  public static function listControls()
  {

    // Build query
    $query = <<<EOF
      SELECT id, name, recurrence_periodic, recurrence_custom
      FROM control
EOF;

    // Execute query
    $controls = self::db()->select($query);

    // Map output to correct JSON format
    return Arrays::each($controls, function ($control) {
      return [
        'id' => $control->id,
        'name' => $control->name,
        'recurrence' => [
          'periodic' => $control->recurrence_periodic
            ? true
            : false,
          'value' => $control->recurrence_periodic
            ? intval($control->recurrence_periodic)
            : $control->recurrence_custom
        ],
        'groups' => ControlGroupModel::listControlGroups($control->id, false)
      ];
    });
  }

  /**
   * Get list of controls to display, with next computation, sorted by next
   * @param integer $productId ID of the product for which to find next controls
   * @return Collection of #maintenance_control_lastexec
   */
  public static function listControlsNext($productId)
  {

    // Prepare query
    $query = <<<EOF
      SELECT control_id, operation
      FROM control_product
      WHERE product_id = ?
EOF;

    // Execute query
    $controlledProducts = self::db()->select($query, [$productId]);

    // Map output to correct JSON format
    return Arrays::each($controlledProducts, function ($controlledProduct) {

      // Get control infos
      $controlId = $controlledProduct->control_id;
      $control = self::getControlBasic($controlId);

      // Get control groups sorted by next date ASC (first one is very next to have to be done)
      $controlGroups = Arrays::sort(
        ControlGroupModel::listControlGroups($controlId, false),
        function ($group) {
          return $group && array_key_exists('next', $group)
            ? (
              $group['next'] === 'ASAP'
              ? '2000-01-01'
              : $group['next']
            )
            : '2000-01-01';
        },
        'asc'
      );

      // Compute next date overall
      $next = $control['recurrence']['periodic']
        ? (
          Arrays::size($controlGroups) === 0
          ? 'ASAP'
          : Arrays::first($controlGroups)['next']
        )
        : null;

      // Format output
      return [
        'control' => [
          'control' => $control,
          'operation' => $controlledProduct->operation
        ],
        'next' => $next
      ];
    });
  }

  /**
   * Get basic infos about a control
   * @param integer $id ID of the control to retrieve
   * @return #control_existing_basic
   */
  public static function getControlBasic($id)
  {

    // Build query
    $query = <<<EOF
      SELECT id, name, recurrence_periodic, recurrence_custom, remark
      FROM control
      WHERE id = ?
EOF;

    // Execute query
    $controls = self::db()->select($query, [$id]);
    $count = Arrays::size($controls);

    // If no control found, return null
    if ($count === 0) {
      return null;
    }

    // Otherwise return first control
    $control = Arrays::first($controls);
    return [
      'id' => $control->id,
      'name' => $control->name,
      'recurrence' => [
        'periodic' => $control->recurrence_periodic
          ? true
          : false,
        'value' => $control->recurrence_periodic
          ? intval($control->recurrence_periodic)
          : $control->recurrence_custom
      ],
      'remark' => $control->remark
    ];
  }

  /**
   * Get full infos about a control
   * @param integer $id ID of the control to retrieve
   * @return #control_existing_full
   */
  public static function getControlFull($id)
  {

    // Get basic infos
    $control = self::getControlBasic($id);

    // If null, return
    if ($control === null) {
      return null;
    }

    // Add extra infos
    return Arrays::merge(
      $control,
      [
        'products' => ControlProductModel::getControlledProducts($id),
        'tasks' => ControlTaskModel::listControlTasks($id),
        'groups' => ControlGroupModel::listControlGroups($id, true)
      ]
    );
  }

  /**
   * Create a new control
   * @param Array $control #control_new Properties of the new control
   * @return #idobj ID of the created control
   */
  public static function createControl($control)
  {
    return self::create("control", Arrays::merge(
      [
        'name' => $control['name'],
        'remark' => $control['remark']
      ],
      $control['recurrence']['periodic']
      ? [
        'recurrence_periodic' => $control['recurrence']['value']
      ]
      : [
        'recurrence_custom' => $control['recurrence']['value']
      ]
    ));
  }

  /**
   * Edit an existing control
   * @param integer $id ID of the control to edit
   * @param Array $data #control_new Properties of the control to modify
   */
  public static function editControl($id, $data)
  {
    return self::edit("control", $id, [
      'name' => $data['name'],
      'remark' => $data['remark'],
      'recurrence_periodic' => $data['recurrence']['periodic']
        ? $data['recurrence']['value']
        : null,
      'recurrence_custom' => $data['recurrence']['periodic']
        ? null
        : $data['recurrence']['value']
    ]);
  }

  /**
   * Delete an existing control
   * @param integer $id ID of the control to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteControl($id)
  {
    return self::delete("control", $id);
  }
}
