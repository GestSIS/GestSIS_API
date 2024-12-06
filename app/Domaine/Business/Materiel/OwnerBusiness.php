<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;

/**
 * Model for manipulating 'owner' database table
 * Available public methods
 * @static listOwners()
 * @static getOwner($id)
 * @static createOwner($owner)
 * @static editOwner($id, $owner)
 * @static deleteOwner($id)
 */
class OwnerBusiness
{
  /**
   * Get list of owners
   * @return Collection of #owner_existing
   */
  public static function listOwners()
  {
    // TODO:
//     // Build query
//     $query = <<<EOF
//       SELECT O.id as id, O.name as name, U.id AS manager_id, U.name as manager_name
//       FROM owner O
//       INNER JOIN user U ON O.manager_id = U.id
//       ORDER BY O.name ASC
// EOF;

    //     // Execute query
//     $owners = self::db()->select($query);

    //     // Map output to correct JSON format
//     return Arrays::each($owners, function ($owner) {
//       return [
//         'id' => $owner->id,
//         'name' => $owner->name,
//         'manager' => [
//           'id' => $owner->manager_id,
//           'name' => $owner->manager_name
//         ]
//       ];
//     });
  }

  /**
   * Get list of owners having a given user as manager
   * @return Collection of #owner_existing
   */
  public static function listOwnersByManager($userId)
  {

    // Build query
    $query = <<<EOF
      SELECT id, name
      FROM owner
      WHERE manager_id = ?
      ORDER BY name ASC
EOF;

    // Execute query
    $owners = self::db()->select($query, [$userId]);

    // Map output to correct JSON format
    return Arrays::each($owners, function ($owner) {
      return [
        'id' => $owner->id,
        'name' => $owner->name
      ];
    });
  }

  /**
   * Get a single owner
   * @param integer $id ID of the owner to get
   * @return #owner_existing
   */
  public static function getOwner($id)
  {

    // Build query
    $query = <<<EOF
      SELECT O.id as id, O.name as name, U.id AS manager_id, U.name as manager_name
      FROM owner O
      INNER JOIN user U ON O.manager_id = U.id
      WHERE O.id = ?
EOF;

    // Execute query
    $owners = self::db()->select($query, [$id]);
    $count = Arrays::size($owners);

    // If no owner found, return null
    if ($count === 0) {
      return null;
    }

    // Otherwise return first owner
    $owner = Arrays::first($owners);
    return [
      'id' => $owner->id,
      'name' => $owner->name,
      'manager' => [
        'id' => $owner->manager_id,
        'name' => $owner->manager_name
      ]
    ];
  }

  /**
   * Create a new owner
   * @param Array $owner #owner_new Properties of the new owner
   * @return #idobj ID of the created owner
   */
  public static function createOwner($owner)
  {
    return self::create("owner", [
      'name' => $owner['name'],
      'manager_id' => $owner['manager']['id']
    ]);
  }

  /**
   * Edit an existing owner
   * @param integer $id ID of the owner to edit
   * @param Array $data #owner_new Properties of the owner to modify
   */
  public static function editOwner($id, $data)
  {
    return self::edit("owner", $id, [
      'name' => $data['name'],
      'manager_id' => $data['manager']['id']
    ]);
  }

  /**
   * Delete an existing owner
   * @param integer $id ID of the owner to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteOwner($id)
  {
    return self::delete("owner", $id);
  }
}
