<?php


namespace App\Domaine\Business;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\ControleMedical;
use App\Models\CoursSapeur;
use App\Models\Ecriture;
use App\Models\ExerciceSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\GroupeSapeur;
use App\Models\HeureExercice;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Mutation;
use App\Models\Permis;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use App\Models\Travail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SapeurBusiness
{
    const TYPE_SAPEUR = 0;
    const TYPE_CIVIL = 1;

    /**
     * Normalise les champs nullable en convertissant null en string vide
     * pour éviter les erreurs SQL avec les colonnes NOT NULL de la base
     */
    private static function normalizeNullableFields($data)
    {
        $nullableFields = ['suffixe', 'remarque', 'profession', 'employeur', 'lieu_de_travail', 'iban', 'email', 'no_avs'];
        foreach ($nullableFields as $field) {
            $data[$field] ??= '';
        }
        return $data;
    }

    /**
     * Normalise le champ remarque spécifiquement pour les entités secondaires
     */
    private static function normalizeRemarque($data)
    {
        $data['remarque'] ??= '';
        return $data;
    }

    private static function isActif($mutations)
    {
        $now = Carbon::now()->setTime(0, 0);
        foreach ($mutations as $mutation) {
            if (
                (Carbon::parse($mutation->sortie)->gte($now) && Carbon::parse($mutation->incorporation)->subMonths(3)->lte($now)) ||
                ($mutation->sortie === null && Carbon::parse($mutation->incorporation)->lte($now))
            ) {
                return true;
            }
        }
        return false;
    }

    private static function anneeIncorporation($mutations)
    {
        return $mutations->map(fn($m) => Carbon::parse($m->incorporation)->year)->min() ?? '';
    }

    public static function recomputeSapeurActifStatus()
    {
        // TODO: Could be optimised via a single SQL Query
        // Sub query 1 -> check if one mutation contains the current date
        $sapeurs = Sapeur::where('type', self::TYPE_SAPEUR)->with('mutations')->get();
        foreach ($sapeurs as $sapeur) {
            $sapeur->actif = self::isActif($sapeur->mutations);
            $sapeur->save();
        }
    }

    public static function recomputeSapeurFonctionPrincipale()
    {
        $now = Carbon::now();
        $sapeurs = Sapeur::where('type', self::TYPE_SAPEUR)->with([
            'fonctions' => function ($query) use ($now) {
                $query->where('debut', '<=', $now)->where(fn($query) => $query->whereNull('fin')
                    ->orWhere('fin', '>=', $now))
                    ->join('fonctions', 'fonctions.id', '=', 'fonction_sapeur.fonction_id')
                    ->orderByDesc('fonctions.tri');
            }
        ])->get();

        foreach ($sapeurs as $sapeur) {
            $sapeur->fonction_id = $sapeur->fonctions[0]->fonction_id ?? null;
            $sapeur->save();
        }
    }

    public static function recomputeSapeurGradePrincipal()
    {
        $now = Carbon::now();
        $sapeurs = Sapeur::where('type', self::TYPE_SAPEUR)->with([
            'grades' => function ($query) use ($now) {
                $query->where('date', '<=', $now)
                    ->join('grades', 'grades.id', '=', 'grade_sapeur.grade_id')
                    ->orderByDesc('grades.tri');
            }
        ])->get();

        foreach ($sapeurs as $sapeur) {
            $sapeur->grade_id = $sapeur->grades[0]->grade_id ?? null;
            $sapeur->save();
        }
    }

    private static function isSapeur($sapeurId)
    {
        return Sapeur::whereId($sapeurId)->where('type', self::TYPE_SAPEUR)->exists();
    }

    public static function createSapeur($data)
    {
        //TODO: Add iban statut système validation
        //TODO: Add no_avs validation
        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['actif'] = 1;
        $data['annee_incorporation'] = Carbon::parse($data['incorporation'])->year;
        $data['porteur'] = 0;
        $data['type'] = self::TYPE_SAPEUR;
        $sapeur = Sapeur::create($data);

        //add new sapeur mutation
        self::addMutation($sapeur->id, array(
            "localite_id" => $sapeur->localite_id,
            "incorporation" => $data['incorporation'],
            "motif" => ""
        ));
        $sapeur->fonction_id = $sapeur->fonction_id ?? 0;
        $sapeur->grade_id = $sapeur->grade_id ?? 0;
        return $sapeur;
    }

    public static function createCivil($data)
    {
        //TODO: Add iban statut système validation
        //TODO: Add no_avs validation
        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['actif'] = 1;
        $data['porteur'] = 0;
        $data['type'] = self::TYPE_CIVIL;
        $data['date_naissance'] = Carbon::yesterday();
        $sapeur = Sapeur::create($data);

        return $sapeur;
    }

    public static function updateNonSapeurStatut($sapeurId, $data)
    {
        if (self::isSapeur($sapeurId)) {
            throw new ArrayException([], "Impossible de changer le statut d'un sapeur directement sans passer par une mutation.");
        }

        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->update($data);
        return $sapeur;
    }

    public static function updateSapeurById(int $sapeurId, $data)
    {
        $data = self::normalizeNullableFields($data);
        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->update($data);
        return $sapeur;
    }

    public static function deleteSapeurById(int $sapeurId)
    {
        if (Ecriture::where('sapeur_id', $sapeurId)->exists()) {
            throw new ArrayException([], "Impossible de supprimer un sapeur lié à une écriture comptable");
        }

        if (Article::where('sapeur_id', $sapeurId)->whereNull('retour')->exists()) {
            throw new ArrayException([], "Impossible de supprimer un sapeur possédant du matériel personnel non rendu");
        }

        if (Intervention::where('sapeur_id', $sapeurId)->exists()) {
            throw new ArrayException([], "Impossible de supprimer un sapeur ayant été chef d'intervention");
        }

        CoursSapeur::where('sapeur_id', $sapeurId)->delete();
        Permis::where('sapeur_id', $sapeurId)->delete();
        GradeSapeur::where('sapeur_id', $sapeurId)->delete();
        FonctionSapeur::where('sapeur_id', $sapeurId)->delete();
        SapeurTelephone::where('sapeur_id', $sapeurId)->delete();
        ExerciceSapeur::where('sapeur_id', $sapeurId)->delete();
        HeureExercice::where('sapeur_id', $sapeurId)->delete();
        InterventionSapeur::where('sapeur_id', $sapeurId)->delete();
        GroupeSapeur::where('sapeur_id', $sapeurId)->delete();
        ControleMedical::where('sapeur_id', $sapeurId)->delete();
        Mutation::where('sapeur_id', $sapeurId)->delete();
        Travail::where('sapeur_id', $sapeurId)->delete();
        Sapeur::findOrFail($sapeurId)->delete();
    }

    public static function addCours(int $sapeurId, $data)
    {
        if (!self::isSapeur($sapeurId)) {
            throw new ArrayException([], "Impossible d'ajouter un cours à un civil.");
        }
        $data['sapeur_id'] = $sapeurId;
        $cours = CoursSapeur::create($data);

        // Add Grade
        if (array_key_exists('grade_id', $data) && $data['grade_id'] !== null) {
            $gradeId = $data['grade_id'];

            // Add grade if not already there
            if (!GradeSapeur::where('sapeur_id', $sapeurId)->where('grade_id', $gradeId)->exists()) {
                self::addGrade($sapeurId, array(
                    'grade_id' => $data['grade_id'],
                    'date' => $data['date_grade'],
                    'remarque' => ''
                ));
            }
        }

        // Edit old fonction
        if (array_key_exists('fonction_sapeur_id', $data) && $data['fonction_sapeur_id'] !== null) {
            self::updateFonction(
                $sapeurId,
                array(
                    'id' => $data['fonction_sapeur_id'],
                    'fin' => $data['date_fonction'],
                    'remarque' => ''
                )
            );
        }

        // Add Fonction
        if (array_key_exists('fonction_id', $data) && $data['fonction_id'] !== null) {
            self::addFonction(
                $sapeurId,
                array(
                    'fonction_id' => $data['fonction_id'],
                    'debut' => $data['date_fonction'],
                    'fin' => null,
                    'remarque' => null
                )
            );
        }

        $sapeur = Sapeur::whereId($sapeurId)->first(['fonction_id', 'grade_id']);
        return ['cours' => $cours, 'main_fonction_id' => $sapeur->fonction_id, 'main_grade_id' => $sapeur->grade_id];
    }

    public static function updateCours(int $sapeurId, $data)
    {
        $cours = CoursSapeur::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        $cours->update($data);
        return $cours;
    }

    public static function removeCours(int $sapeurId, int $coursSapeurId)
    {
        // Check que le cours n'est pas lié à une écriture
        if (Ecriture::where('cours_sapeur_id', $coursSapeurId)->exists()) {
            throw new ArrayException([], 'Impossible de supprimer un cours facturé');
        }
        CoursSapeur::where('sapeur_id', $sapeurId)->findOrFail($coursSapeurId)->delete();
    }

    public static function addGrade(int $sapeurId, $data)
    {
        if (!self::isSapeur($sapeurId)) {
            throw new ArrayException([], "Impossible d'ajouter un grade à un civil.");
        }
        $data = self::normalizeRemarque($data);
        $gradeId = intval($data['grade_id']);

        //Check si déjà présent
        $nb = GradeSapeur::where('grade_id', $gradeId)->where('sapeur_id', $sapeurId)->count();

        if ($nb > 0) {
            throw new ArrayException(array('id' => "Grade déjà existant"));
        }

        $data['sapeur_id'] = $sapeurId;
        $grade = GradeSapeur::create($data);
        $mainGradeId = self::updateMainGrade($sapeurId);

        return ['grade' => $grade, 'main_grade_id' => $mainGradeId];
    }

    public static function updateGrade(int $sapeurId, $data)
    {
        $data = self::normalizeRemarque($data);
        $grade = GradeSapeur::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        $grade->update($data);
        $mainGradeId = self::updateMainGrade($sapeurId);

        return ['grade' => $grade, 'main_grade_id' => $mainGradeId];
    }

    public static function removeGrade(int $sapeurId, int $gradeSapeurId)
    {
        GradeSapeur::where('sapeur_id', $sapeurId)->findOrFail($gradeSapeurId)->delete();
        $mainGradeId = self::updateMainGrade($sapeurId);
        return ['main_grade_id' => $mainGradeId];
    }

    public static function addFonction(int $sapeurId, $data)
    {
        //Check duplicated fonction during period of time
        $fonctionId = $data['fonction_id'];

        //Check si déjà présent
        $fonctions = FonctionSapeur::where('sapeur_id', $sapeurId)
            ->get()
            ->filter(fn($fonction) => $fonction->fonction_id === $fonctionId);

        $startDate = array_key_exists('debut', $data) ? date($data['debut']) : null;
        $endDate = array_key_exists('fin', $data) ? date($data['fin']) : null;

        //Check overlaps of a fonction
        foreach ($fonctions as $fonction) {
            $start = $fonction->debut;
            $end = $fonction->fin;

            if (self::checkOverlappingPeriod($start, $end, $startDate, $endDate)) {
                throw new ArrayException([
                    "debut" => "Duplicated period",
                    "fin" => "Duplicated period",
                    "message" => "Fonction dupliquée durant une même période"
                ]);
            }
        }

        $data = self::normalizeRemarque($data);
        $data['sapeur_id'] = $sapeurId;
        $fonction = FonctionSapeur::create($data);
        $mainFonctionId = self::updateFonctionPrincipale($sapeurId);

        return ['fonction' => $fonction, 'main_fonction_id' => $mainFonctionId];
    }

    public static function updateFonction(int $sapeurId, $data)
    {
        $id = $data['id'];

        //Check si déjà présent
        $fonctions = FonctionSapeur::where('sapeur_id', $sapeurId)->get();

        //Get fonction to update
        $fonction = $fonctions->firstWhere('id', $id);
        $fonctionId = $fonction->fonction_id;

        $fonctions = $fonctions->filter(fn($f) => $f->fonction_id === $fonctionId && $f->id !== $id);

        // Check si déjà présent
        $startDate = null;
        $endDate = null;

        if (array_key_exists('debut', $data)) {
            $startDate = $data['debut'] !== null ? date($data['debut']) : null;
        } else {
            $startDate = $fonction->debut;
        }
        if (array_key_exists('fin', $data)) {
            $endDate = $data['fin'] !== null ? date($data['fin']) : null;
        } else {
            $endDate = $fonction->fin;
        }

        // Check overlaps of a fonction
        foreach ($fonctions as $fct) {
            $start = $fct->debut;
            $end = $fct->fin;

            if (self::checkOverlappingPeriod($start, $end, $startDate, $endDate)) {
                throw new ArrayException([
                    'debut' => "Duplicated period",
                    'fin' => 'Duplicated period',
                ]);
            }
        }

        // Update fonction
        $data = self::normalizeRemarque($data);
        $fonction = FonctionSapeur::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        $fonction->update($data);
        $mainFonctionId = self::updateFonctionPrincipale($sapeurId);

        return ['fonction' => $fonction, 'main_fonction_id' => $mainFonctionId];
    }

    public static function removeFonction(int $sapeurId, int $fonctionSapeurId)
    {
        FonctionSapeur::where('sapeur_id', $sapeurId)->findOrFail($fonctionSapeurId)->delete();
        $mainFonctionId = self::updateFonctionPrincipale($sapeurId);
        return ['main_fonction_id' => $mainFonctionId];
    }

    public static function finFonctions($sapeurId, $date, $fonctionsId)
    {
        $fonctions = FonctionSapeur::where('sapeur_id', $sapeurId)->get();

        // Contrôle que la date de fin ne soit pas antérieur à la date de début
        $dateFin = Carbon::parse($date);
        foreach ($fonctionsId as $id) {
            $fonction = $fonctions->firstWhere('id', intval($id));
            if ($fonction === null || Carbon::parse($fonction->debut)->gte($dateFin)) {
                throw new ArrayException([
                    'fin' => 'Date de fin invalide',
                ]);
            }
        }

        foreach ($fonctionsId as $id) {
            if ($fonctions->contains('id', intval($id))) {
                $fonction = FonctionSapeur::where('sapeur_id', $sapeurId)->findOrFail($id);
                $fonction->fin = $date;
                $fonction->save();
            }
        }

        //$fonctions
        self::updateFonctionPrincipale($sapeurId);

        return FonctionSapeur::where('sapeur_id', $sapeurId)->get();
    }

    private static function verifyMutationPeriode($editedMutation, $mutations)
    {
        //Contrôle qu'une seule mutation peut ne pas avoir de date de fin
        if (!array_key_exists('sortie', $editedMutation) || is_null($editedMutation['sortie'])) {
            foreach ($mutations as $m) {
                if (is_null($m->sortie)) {
                    throw new ArrayException([
                        "sortie" => "Une seule mutation active à la fois",
                    ]);
                }
            }
        }

        //Contrôle que deux mutations ne se chevauchent pas
        $incorporation = Carbon::parse($editedMutation['incorporation']);
        $sortie = (!array_key_exists('sortie', $editedMutation) || is_null($editedMutation['sortie'])) ? Null : Carbon::parse($editedMutation['sortie']);
        foreach ($mutations as $m) {
            //Check overlapping periodes
            $incorporationTemp = Carbon::parse($m->incorporation);
            $sortieTemp = is_null($m->sortie) ? null : Carbon::parse($m->sortie);
            if (
                is_null($sortieTemp) && $sortie->gte($incorporationTemp) ||
                is_null($sortie) && $incorporation->lte($sortieTemp) ||
                !is_null($sortieTemp) && !is_null($sortie) && ($incorporation->gte($incorporationTemp) && $incorporation->lte($sortieTemp) ||
                    $sortie->gte($incorporationTemp) && $sortie->lte($sortieTemp))
            ) {
                throw new ArrayException([
                    "sortie" => "Deux mutations en conflits",
                    "incorporation" => "Deux mutations en conflits",
                ]);
            }
        }
    }

    public static function addMutation($sapeurId, $data)
    {
        if (!self::isSapeur($sapeurId)) {
            throw new ArrayException([], "Impossible d'ajouter une mutation à un civil.");
        }
        $mutations = Mutation::where('sapeur_id', $sapeurId)->get();
        self::verifyMutationPeriode($data, $mutations);

        $data['sapeur_id'] = $sapeurId;
        $mutation = Mutation::create($data);

        // Update actif statut depending of end of all mutation
        $mutations->push($mutation);
        $actif = self::isActif($mutations) ? 1 : 0;
        $anneeIncorporation = self::anneeIncorporation($mutations);
        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->actif = $actif;
        $sapeur->annee_incorporation = $anneeIncorporation;
        $sapeur->save();
        return ["mutation" => $mutation, "actif" => $actif, "annee_incorporation" => $anneeIncorporation];
    }

    public static function updateMutation(int $sapeurId, $data)
    {
        //Update mutation
        $mutationId = $data['id'];
        $mutations = Mutation::where('sapeur_id', $sapeurId)->get()->reject(fn($m) => $m->id === $mutationId);
        self::verifyMutationPeriode($data, $mutations);

        $mutation = Mutation::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        $data['motif'] ??= '';
        $mutation->update($data);

        // Update actif statut depending of end of all mutation
        $mutations->push($mutation);
        $actif = self::isActif($mutations) ? 1 : 0;
        $anneeIncorporation = self::anneeIncorporation($mutations);
        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->actif = $actif;
        $sapeur->annee_incorporation = $anneeIncorporation;
        $sapeur->save();
        return ["mutation" => $mutation, "actif" => $actif, "annee_incorporation" => $anneeIncorporation];
    }

    /**
     * Supppression d'une mutation
     *
     * @param int $mutationId
     */
    public static function removeMutation(int $sapeurId, int $mutationId)
    {
        // Check at least one mutation
        // Attention, quand on ajoutera les civils, il faudra enlever cette limitation pour ce type de personnes
        if (Mutation::where('sapeur_id', $sapeurId)->count() === 0) {
            throw new ArrayException([
                "info" => "Au moins une mutation nécessaire",
            ]);
        }
        Mutation::where('sapeur_id', $sapeurId)->findOrFail($mutationId)->delete();

        // Update actif statut depending of end of all mutation
        $mutations = Mutation::where('sapeur_id', $sapeurId)->get();
        $actif = self::isActif($mutations) ? 1 : 0;
        $anneeIncorporation = self::anneeIncorporation($mutations);
        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->actif = $actif;
        $sapeur->annee_incorporation = $anneeIncorporation;
        $sapeur->save();
        return ["actif" => $actif, "annee_incorporation" => $anneeIncorporation];
    }

    public static function addTelephone(int $sapeurId, $data)
    {
        $telephones = SapeurTelephone::where('sapeur_id', $sapeurId)->get();
        foreach ($telephones as $tel) {
            if (
                strcmp(
                    trim(preg_replace('/\s+/', ' ', $tel->numero)),
                    trim(preg_replace('/\s+/', ' ', $data['numero']))
                ) === 0
            ) {
                throw new ArrayException(['numero' => 'Duplicated numero']);
            }
        }

        $data['sapeur_id'] = $sapeurId;
        return SapeurTelephone::create($data);
    }

    public static function updateTelephone(int $sapeurId, $data)
    {
        $telephones = SapeurTelephone::where('sapeur_id', $sapeurId)->get();

        $telephoneId = $data['id'];

        $telephones = $telephones->reject(fn($t) => $t->id === $telephoneId);

        foreach ($telephones as $tel) {
            if (
                strcmp(
                    trim(preg_replace('/\s+/', ' ', $tel->numero)),
                    trim(preg_replace('/\s+/', ' ', $data['numero']))
                ) === 0
            ) {
                throw new ArrayException(['numéro' => 'Numéro à double'], 'Numéro déjà existant');
            }
        }

        $telephone = SapeurTelephone::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        $telephone->update($data);
        return $telephone;
    }

    public static function removeTelephone(int $sapeurId, int $telephoneId)
    {
        SapeurTelephone::where('sapeur_id', $sapeurId)->findOrFail($telephoneId)->delete();
    }

    public static function addPermis(int $sapeurId, $data)
    {
        if (!self::isSapeur($sapeurId)) {
            throw new ArrayException([], "Impossible d'ajouter un permis à un civil.");
        }
        $permisId = $data['permis_type_id'];

        //Check si sapeur as déjà ce permis
        if (Permis::where('sapeur_id', $sapeurId)->where('permis_type_id', $permisId)->exists()) {
            throw new ArrayException(array('id' => "Unable to find permis"));
        }

        $data['sapeur_id'] = $sapeurId;
        $permis = Permis::create($data);

        return $permis;
    }

    public static function updatePermis(int $sapeurId, $data)
    {
        $permis = Permis::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        $permis->update($data);
        return $permis;
    }

    public static function removePermis(int $sapeurId, int $permisId)
    {
        Permis::where('sapeur_id', $sapeurId)->findOrFail($permisId)->delete();
    }

    public static function removeGroupes($sapeurId, $groupesIds)
    {
        Sapeur::findOrFail($sapeurId);
        \App\Models\GroupeSapeur::where('sapeur_id', $sapeurId)
            ->whereIn('id', $groupesIds)
            ->delete();

        return \App\Models\GroupeSapeur::where('sapeur_id', $sapeurId)->get();
    }

    /* ************************************************** *
     *                  METHODES PRIVEES                  *
     * ************************************************** */

    private static function checkOverlappingPeriod($start1, $end1, $start2, $end2)
    {
        return ($end1 === null && $end2 === null ||
            $end1 === null && $start1 <= $end2 ||
            $end2 === null && $end1 >= $start2 ||
            $end1 !== null && $end2 !== null && !($end1 < $start2 || $end2 < $start1));
    }

    private static function updateFonctionPrincipale($sapeurId)
    {
        // Recupérer avec fonctions pour le tri
        $now = Carbon::now();
        $fonctionMax = FonctionSapeur::with('fonction')
            ->where('sapeur_id', $sapeurId)
            ->get()
            ->filter(fn($fonction) => $now->gte($fonction->debut) && ($fonction->fin === null || $now->lte($fonction->fin)))
            ->sortByDesc(fn($f) => $f->fonction->tri)
            ->first();

        $maxId = $fonctionMax?->fonction->id;

        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->fonction_id = $maxId;
        $sapeur->save();
        return $maxId;
    }

    private static function updateMainGrade($sapeurId)
    {
        // Recupérer avec grades pour le tri
        $now = Carbon::now();
        $gradeMax = GradeSapeur::with('grade')
            ->where('sapeur_id', $sapeurId)
            ->get()
            ->filter(fn($grade) => $now->gte($grade->date))
            ->sortByDesc(fn($g) => $g->grade->tri)
            ->first();

        $maxId = $gradeMax?->grade->id;

        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->grade_id = $maxId;
        $sapeur->save();
        return $maxId;
    }

    private static $ALLOWED_PHOTO_EXTENSION = ['jpg', 'jpeg', 'png'];

    public static function downloadPhotoSapeur($sapeurId, $sisKey)
    {
        foreach (self::$ALLOWED_PHOTO_EXTENSION as $extension) {
            $path = "photos/$sisKey/$sapeurId.$extension";
            if (Storage::exists($path)) {
                return Storage::download($path, null, ['Response-Type' => 'arraybuffer']);
            }
        }
        return response()->json(null);
    }

    public static function getPhotoSapeurPath($sapeurId, $sisKey)
    {
        foreach (self::$ALLOWED_PHOTO_EXTENSION as $extension) {
            $path = "photos/$sisKey/$sapeurId.$extension";
            if (Storage::exists($path)) {
                return $path;
            }
        }
        return null;
    }

    public static function deletePhotoSapeur($sapeurId, $sisKey)
    {
        $files = collect(self::$ALLOWED_PHOTO_EXTENSION)
            ->map(fn($ext) => "photos/$sisKey/$sapeurId.$ext")
            ->all();
        Storage::delete($files);
    }

    public static function uploadPhotoSapeur($image, $sapeurId, $sisKey)
    {
        self::deletePhotoSapeur($sapeurId, $sisKey);
        $extension = strtolower($image->extension());
        return $image->storeAs("photos/$sisKey", "$sapeurId.$extension");
    }

    public static function trombinoscope(string $sisKey)
    {
        $imageDefault = 'icon/user.svg';

        $sapeurs = Sapeur::where('actif', 1)
            ->where('type', self::TYPE_SAPEUR)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom']);

        $sapeurs = $sapeurs
            ->map(fn($sapeur) => [
                'id' => $sapeur->id,
                'nom' => $sapeur->nom,
                'prenom' => $sapeur->prenom,
                'photo' => self::getPhotoSapeurPath($sapeur->id, $sisKey)
            ])
            ->all();
        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::Trombinoscope,
            [
                "sapeurs" => $sapeurs,
                "sisId" => $sisKey,
                "imageDefault" => $imageDefault,
            ],
            $logoPath,
            extraStorageFiles: [
                $imageDefault,
                ...array_filter(
                    array_map(fn($s) => $s['photo'], $sapeurs),
                    fn($path) => $path !== null
                )
            ]
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'trombinoscope.pdf'
        );
    }

    public static function fiche($sapeurId, string $sisKey)
    {
        $sapeur = Sapeur::with(['localite', 'civilite', 'fonction', 'grade'])->find($sapeurId);

        $logoPath = SisParamBusiness::getLogo($sisKey);
        $content = TypstToPdfGenerator::generateDocument(
            TypstTemplate::FicheSapeur,
            [
                "sapeur" => $sapeur,
                "fonctions" => FonctionSapeur::with('fonction')->where('sapeur_id', $sapeurId)->orderBy('debut')->get(),
                "grades" => GradeSapeur::with('grade')->where('sapeur_id', $sapeurId)->orderBy('date')->get(),
                "mutations" => Mutation::with('localite')->where('sapeur_id', $sapeurId)->orderBy('incorporation')->get(),
                "cours" => CoursSapeur::with(['localite', 'cours'])->where('sapeur_id', $sapeurId)->orderBy('date')->get(),
                "telephones" => SapeurTelephone::with(['telephoneType'])->where('sapeur_id', $sapeurId)->orderBy('priorite')->get(),
                "permis" => Permis::with(['permisType'])->where('sapeur_id', $sapeurId)->orderBy('date')->get(),
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'fiche-sapeur.pdf'
        );
    }
}
