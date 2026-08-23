<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Hangar;
use DB;
use \Illuminate\Database\Eloquent\Collection;
use App\Models\Emplacement;

/**
 * Model for manipulating 'emplacement' database table
 * Available public methods
 * @static listEmplacements()
 * @static getEmplacement($id)
 * @static createEmplacement($emplacement)
 * @static editEmplacement($id, $data)
 * @static deleteEmplacement($id)
 */
class EmplacementBusiness
{

  /**
   * Get list of batteries
   * @return Collection of #emplacement_existing
   */
  public static function listEmplacements()
  {
    return Emplacement::with(['article', 'hangar'])->get();
  }

  /**
   * Get a single emplacement
   * @param integer $id ID of the emplacement to get
   * @return #emplacement_existing
   */
  public static function getEmplacement($id)
  {
    return Emplacement::with(['article', 'hangar'])->find($id);
  }

  /**
   * Create a new emplacement
   * @param array $emplacement #emplacement_new Properties of the new emplacement, avec
   * éventuellement un sous-objet hangar (rue, no_rue, localite_id) si cet emplacement
   * représente un hangar (bâtiment)
   * @return #idobj ID of the created emplacement
   */
  public static function createEmplacement($emplacement)
  {
    $order = DB::table('emplacements')->max('id');
    $emplacement['remarque'] ??= '';
    $emplacement['tri'] = ($order ?? 0) + 1;

    $hangar = $emplacement['hangar'] ?? null;
    unset($emplacement['hangar']);

    $created = Emplacement::create($emplacement);

    if ($hangar !== null) {
      Hangar::create(['id' => $created->id, ...$hangar]);
    }

    return Emplacement::with(['article', 'hangar'])->find($created->id);
  }

  /**
   * Create the emplacement representing an article (ex: the inside of a vehicule)
   * @param Article $article Article represented by the new emplacement
   * @param array $emplacementData couleur_id, parent_id, est_etiquete, est_compartimentable
   * @return Emplacement
   */
  public static function createEmplacementPourArticle(Article $article, array $emplacementData): Emplacement
  {
    $order = DB::table('emplacements')->max('id');
    return Emplacement::create([
      'designation' => $article->designation,
      'remarque' => $article->remarque,
      'tri' => ($order ?? 0) + 1,
      'couleur_id' => $emplacementData['couleur_id'],
      'parent_id' => $emplacementData['parent_id'] ?? null,
      'est_etiquete' => $emplacementData['est_etiquete'] ?? false,
      'est_compartimentable' => $emplacementData['est_compartimentable'] ?? false,
      'article_id' => $article->id,
    ]);
  }

  /**
   * Edit an existing emplacement
   * @param integer $id ID of the emplacement to edit
   * @param array $data #emplacement_new Properties of the emplacement to modify
   */
  public static function editEmplacement($id, $data)
  {
    if (Emplacement::find($id)->article_id !== null) {
      throw new ArrayException([], "Cet emplacement est géré depuis la fiche du véhicule lié");
    }
    $data['remarque'] ??= '';

    // Un sous-objet hangar n'est présent que lorsque l'édition vient de la gestion des
    // hangars (ModalHangar) : son absence (édition via la gestion générique des
    // emplacements) ne doit pas effacer un hangar existant.
    $hangar = $data['hangar'] ?? null;
    unset($data['hangar']);

    Emplacement::whereId($id)
      ->update($data);

    if ($hangar !== null) {
      Hangar::updateOrCreate(['id' => $id], $hangar);
    }

    return Emplacement::with(['article', 'hangar'])->find($id);
  }

  /**
   * Delete an existing emplacement
   * @param integer $id ID of the emplacement to delete
   * @return boolean true if deleted successfully
   */
  public static function deleteEmplacement($id)
  {
    if (Article::where('emplacement_id', $id)->exists()) {
      throw new ArrayException([], "Veuillez d'abord supprimer les article de cet emplacement");
    }
    if (Emplacement::where('parent_id', $id)->exists()) {
      throw new ArrayException([], "Veuillez d'abord supprimer ou déplacer les sous-emplacements de cet emplacement");
    }
    if (Emplacement::find($id)->article_id !== null) {
      throw new ArrayException([], "Cet emplacement est lié à un véhicule, il ne peut être supprimé qu'en supprimant le véhicule");
    }
    return Emplacement::whereId($id)->delete();
  }
}
