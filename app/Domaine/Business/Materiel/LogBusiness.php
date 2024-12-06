<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;

/**
 * Model for manipulating 'log' database table
 * Available public methods
 * @static record($what, $entityType, $entityId)
 */
class LogBusiness
{


  const ENTITY = [
    'BATTERY' => 'BATTERY',
    'CATEGORY' => 'CATEGORY',
    'COLOR' => 'COLOR',
    'CONTROL' => [
      'CONTROL' => 'CONTROL',
      'GROUP' => 'CONTROL_GROUP',
      'PRODUCT' => 'CONTROL_PRODUCT',
      'TASK' => 'CONTROL_TASK'
    ],
    'DIAMETER' => 'DIAMETER',
    'MAINTENANCE' => [
      'MAINTENANCE' => 'MAINTENANCE',
      'EXECUTION' => 'MAINTENANCE_EXECUTION'
    ],
    'INVENTORY' => 'INVENTORY',
    'ITEM' => 'ITEM',
    'LOCATION' => 'LOCATION',
    'OWNER' => 'OWNER',
    'PRODUCT' => 'PRODUCT',
    'USER' => 'USER'
  ];

  /**
   * Record a log entry
   * @param string $what Description of the log entry
   * @param string $entityType Type of entity (see constants above)
   * @param integer $entityId ID of target entity
   */
  public static function record($what, $entityType = null, $entityId = null, $user = null)
  {
    $request = app('request');
    if ($user === null) {
      $user = $request->user();
    }
    self::create('log', Arrays::merge(
      [
        'when' => date('Y-m-d H:i:s'),
        'what' => $what
      ],
      $user !== null
      ? [
        'who' => $user['name'],
        'user_id' => $user['id']
      ]
      : [
        'who' => '-'
      ],
      $entityType !== null
      ? [
        'entity_type' => $entityType
      ]
      : [],
      $entityId !== null
      ? [
        'entity_id' => $entityId
      ]
      : [],
      [
        'req_verb' => $request->method(),
        'req_url' => $request->fullUrl(),
        'req_body' => json_encode($request->all())
      ]
    ));
  }
}
