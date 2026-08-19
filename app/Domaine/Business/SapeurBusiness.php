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
    public const TYPE_SAPEUR = 0;
    public const TYPE_CIVIL = 1;
    public const TYPE_RECRUE = 2;

    private const ALLOWED_PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    private static function normalizeNullableFields($data): array
    {
        $nullableFields = ['suffixe', 'remarque', 'profession', 'employeur', 'lieu_de_travail', 'iban', 'email', 'no_avs'];
        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] ??= '';
            }
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

    private static function isSapeurOuRecrue($sapeurId)
    {
        return Sapeur::whereId($sapeurId)->whereIn('type', [self::TYPE_SAPEUR, self::TYPE_RECRUE])->exists();
    }

    public static function createSapeur($data)
    {
        //TODO: Add iban statut système validation
        //TODO: Add no_avs validation
        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['actif'] = true;
        $data['annee_incorporation'] = Carbon::parse($data['incorporation'])->year;
        $data['porteur'] = false;
        $data['type'] = self::TYPE_SAPEUR;
        $sapeur = Sapeur::create($data);

        //add new sapeur mutation
        self::addMutation($sapeur->id, [
            "localite_id" => $sapeur->localite_id,
            "incorporation" => $data['incorporation'],
            "motif" => ""
        ]);
        return $sapeur;
    }

    public static function createCivil($data)
    {
        //TODO: Add iban statut système validation
        //TODO: Add no_avs validation
        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['actif'] = true;
        $data['porteur'] = false;
        $data['type'] = self::TYPE_CIVIL;
        $data['date_naissance'] = Carbon::yesterday();
        $sapeur = Sapeur::create($data);

        return $sapeur;
    }

    private static function normaliserAvs(string $noAvs): string
    {
        return preg_replace('/[^0-9]/', '', $noAvs);
    }

    /**
     * Vérifie si un numéro AVS est déjà utilisé par un sapeur existant (tout type confondu),
     * en comparant les numéros normalisés (chiffres uniquement).
     */
    public static function avsDejaUtilise(string $noAvs): bool
    {
        $normalise = self::normaliserAvs($noAvs);
        if ($normalise === '') {
            return false;
        }

        return Sapeur::pluck('no_avs')
            ->contains(fn($avs) => self::normaliserAvs((string) $avs) === $normalise);
    }

    /**
     * Création d'une recrue via le formulaire public d'auto-inscription.
     * Statut non actif tant qu'elle n'a pas été validée par un fourrier.
     */
    public static function createRecrue($data)
    {
        if (self::avsDejaUtilise($data['no_avs'])) {
            throw new ArrayException(['no_avs' => 'Une inscription existe déjà avec ce numéro AVS.']);
        }

        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['actif'] = false;
        $data['porteur'] = false;
        $data['type'] = self::TYPE_RECRUE;

        $telephones = $data['telephones'] ?? [];
        $permis = $data['permis'] ?? [];
        unset($data['telephones'], $data['permis']);

        $recrue = Sapeur::create($data);
        foreach ($telephones as $telephone) {
            self::addTelephone($recrue->id, $telephone);
        }
        foreach ($permis as $p) {
            self::addPermis($recrue->id, $p);
        }

        return $recrue;
    }

    /**
     * Validation d'une recrue par un fourrier : bascule en sapeur réel et crée sa première mutation.
     */
    public static function validateRecrue(int $sapeurId, string $incorporation): Sapeur
    {
        $recrue = Sapeur::where('type', self::TYPE_RECRUE)->findOrFail($sapeurId);

        $recrue->type = self::TYPE_SAPEUR;
        $recrue->actif = true;
        $recrue->annee_incorporation = Carbon::parse($incorporation)->year;
        $recrue->save();

        self::addMutation($recrue->id, [
            "localite_id" => $recrue->localite_id,
            "incorporation" => $incorporation,
            "motif" => "",
        ]);

        return $recrue;
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
        if (isset($data['grade_id'])) {
            // Add grade if not already there
            if (!GradeSapeur::where('sapeur_id', $sapeurId)->where('grade_id', $data['grade_id'])->exists()) {
                self::addGrade($sapeurId, [
                    'grade_id' => $data['grade_id'],
                    'date' => $data['date_grade'],
                    'remarque' => ''
                ]);
            }
        }

        // Edit old fonction
        if (isset($data['fonction_sapeur_id'])) {
            self::updateFonction(
                $sapeurId,
                [
                    'id' => $data['fonction_sapeur_id'],
                    'fin' => $data['date_fonction'],
                    'remarque' => ''
                ]
            );
        }

        // Add Fonction
        if (isset($data['fonction_id'])) {
            self::addFonction(
                $sapeurId,
                [
                    'fonction_id' => $data['fonction_id'],
                    'debut' => $data['date_fonction'],
                    'fin' => null,
                    'remarque' => null
                ]
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

        //Check si déjà présent
        if (GradeSapeur::where('grade_id', $data['grade_id'])->where('sapeur_id', $sapeurId)->exists()) {
            throw new ArrayException(['id' => "Grade déjà existant"]);
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

    /**
     * Contrôle que la période [$debut, $fin] ne chevauche aucune des périodes existantes
     */
    private static function controlerChevauchementFonction($periodes, $debut, $fin): void
    {
        foreach ($periodes as $periode) {
            if (self::checkOverlappingPeriod($periode->debut, $periode->fin, $debut, $fin)) {
                throw new ArrayException([
                    "debut" => "Duplicated period",
                    "fin" => "Duplicated period",
                    "message" => "Fonction dupliquée durant une même période"
                ]);
            }
        }
    }

    public static function addFonction(int $sapeurId, $data)
    {
        //Check duplicated fonction during period of time
        $periodes = FonctionSapeur::where('sapeur_id', $sapeurId)
            ->where('fonction_id', $data['fonction_id'])
            ->get();
        self::controlerChevauchementFonction($periodes, $data['debut'] ?? null, $data['fin'] ?? null);

        $data = self::normalizeRemarque($data);
        $data['sapeur_id'] = $sapeurId;
        $fonction = FonctionSapeur::create($data);
        $mainFonctionId = self::updateFonctionPrincipale($sapeurId);

        return ['fonction' => $fonction, 'main_fonction_id' => $mainFonctionId];
    }

    public static function updateFonction(int $sapeurId, $data)
    {
        $fonction = FonctionSapeur::where('sapeur_id', $sapeurId)->findOrFail($data['id']);

        //Check duplicated fonction during period of time, sur les autres périodes de la même fonction
        $periodes = FonctionSapeur::where('sapeur_id', $sapeurId)
            ->where('fonction_id', $fonction->fonction_id)
            ->where('id', '!=', $fonction->id)
            ->get();
        $debut = array_key_exists('debut', $data) ? $data['debut'] : $fonction->debut;
        $fin = array_key_exists('fin', $data) ? $data['fin'] : $fonction->fin;
        self::controlerChevauchementFonction($periodes, $debut, $fin);

        $fonction->update(self::normalizeRemarque($data));
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

        FonctionSapeur::where('sapeur_id', $sapeurId)
            ->whereIn('id', $fonctionsId)
            ->update(['fin' => $date]);

        self::updateFonctionPrincipale($sapeurId);

        return FonctionSapeur::where('sapeur_id', $sapeurId)->get();
    }

    private static function verifyMutationPeriode($editedMutation, $mutations)
    {
        $sortie = isset($editedMutation['sortie']) ? Carbon::parse($editedMutation['sortie']) : null;

        //Contrôle qu'une seule mutation peut ne pas avoir de date de fin
        if ($sortie === null && $mutations->contains(fn($m) => $m->sortie === null)) {
            throw new ArrayException([
                "sortie" => "Une seule mutation active à la fois",
            ]);
        }

        //Contrôle que deux mutations ne se chevauchent pas
        $incorporation = Carbon::parse($editedMutation['incorporation']);
        foreach ($mutations as $m) {
            $sortieTemp = $m->sortie === null ? null : Carbon::parse($m->sortie);
            if (self::checkOverlappingPeriod(Carbon::parse($m->incorporation), $sortieTemp, $incorporation, $sortie)) {
                throw new ArrayException([
                    "sortie" => "Deux mutations en conflits",
                    "incorporation" => "Deux mutations en conflits",
                ]);
            }
        }
    }

    /**
     * Recalcule le statut actif et l'année d'incorporation du sapeur à partir de ses mutations
     *
     * @return array{actif: bool, annee_incorporation: mixed}
     */
    private static function majStatutActifSapeur(int $sapeurId, $mutations): array
    {
        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->actif = self::isActif($mutations);
        $sapeur->annee_incorporation = self::anneeIncorporation($mutations);
        $sapeur->save();

        return ["actif" => $sapeur->actif, "annee_incorporation" => $sapeur->annee_incorporation];
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
        return array_merge(["mutation" => $mutation], self::majStatutActifSapeur($sapeurId, $mutations));
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
        return array_merge(["mutation" => $mutation], self::majStatutActifSapeur($sapeurId, $mutations));
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
        return self::majStatutActifSapeur($sapeurId, Mutation::where('sapeur_id', $sapeurId)->get());
    }

    private static function numeroNormalise($numero): string
    {
        return trim(preg_replace('/\s+/', ' ', $numero));
    }

    public static function addTelephone(int $sapeurId, $data)
    {
        $numero = self::numeroNormalise($data['numero']);
        $existeDeja = SapeurTelephone::where('sapeur_id', $sapeurId)->get()
            ->contains(fn($tel) => self::numeroNormalise($tel->numero) === $numero);
        if ($existeDeja) {
            throw new ArrayException(['numero' => 'Duplicated numero']);
        }

        $data['sapeur_id'] = $sapeurId;
        return SapeurTelephone::create($data);
    }

    public static function updateTelephone(int $sapeurId, $data)
    {
        $numero = self::numeroNormalise($data['numero']);
        $existeDeja = SapeurTelephone::where('sapeur_id', $sapeurId)->get()
            ->reject(fn($t) => $t->id === $data['id'])
            ->contains(fn($tel) => self::numeroNormalise($tel->numero) === $numero);
        if ($existeDeja) {
            throw new ArrayException(['numéro' => 'Numéro à double'], 'Numéro déjà existant');
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
        if (!self::isSapeurOuRecrue($sapeurId)) {
            throw new ArrayException([], "Impossible d'ajouter un permis à un civil.");
        }
        $permisId = $data['permis_type_id'];

        //Check si sapeur as déjà ce permis
        if (Permis::where('sapeur_id', $sapeurId)->where('permis_type_id', $permisId)->exists()) {
            throw new ArrayException(['id' => "Unable to find permis"]);
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
        GroupeSapeur::where('sapeur_id', $sapeurId)
            ->whereIn('id', $groupesIds)
            ->delete();

        return GroupeSapeur::where('sapeur_id', $sapeurId)->get();
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

        $maxId = $fonctionMax?->fonction_id;

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

        $maxId = $gradeMax?->grade_id;

        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->grade_id = $maxId;
        $sapeur->save();
        return $maxId;
    }

    public static function downloadPhotoSapeur($sapeurId, $sisKey)
    {
        $path = self::getPhotoSapeurPath($sapeurId, $sisKey);
        if ($path !== null) {
            return Storage::download($path, null, ['Response-Type' => 'arraybuffer']);
        }
        return response()->json(null);
    }

    public static function getPhotoSapeurPath($sapeurId, $sisKey)
    {
        foreach (self::ALLOWED_PHOTO_EXTENSIONS as $extension) {
            $path = "photos/$sisKey/$sapeurId.$extension";
            if (Storage::exists($path)) {
                return $path;
            }
        }
        return null;
    }

    public static function deletePhotoSapeur($sapeurId, $sisKey)
    {
        $files = collect(self::ALLOWED_PHOTO_EXTENSIONS)
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
