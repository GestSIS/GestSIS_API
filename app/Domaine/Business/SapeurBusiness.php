<?php


namespace App\Domaine\Business;

use App\Application\Typst\TypstTemplate;
use App\Application\Typst\TypstToPdfGenerator;
use App\Domaine\Exceptions\ArrayException;
use App\Domaine\Exceptions\NotReportableException;
use App\Domaine\Business\Materiel\ArticleBusiness;
use App\Models\Article;
use App\Models\ArticleAttribution;
use App\Models\ControleMedical;
use App\Models\CoursSapeur;
use App\Models\Ecriture;
use App\Models\Fonction;
use App\Models\ExerciceSapeur;
use App\Models\FonctionSapeur;
use App\Models\GradeSapeur;
use App\Models\GroupeSapeur;
use App\Models\Intervention;
use App\Models\InterventionSapeur;
use App\Models\Mutation;
use App\Models\Permis;
use App\Models\Quittance;
use App\Models\Sapeur;
use App\Models\SapeurTelephone;
use App\Models\Travail;
use App\Support\Telephone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Z38\SwissPayment\IBAN;

class SapeurBusiness
{
    /**
     * Type principal, calculé (voir typePrincipal())
     * FIXME: à supprimer une fois les apps mobiles migrées sur statut_sapeur / statut_civil
     */
    public const TYPE_SAPEUR = 0;
    public const TYPE_CIVIL = 1;
    public const TYPE_RECRUE = 2;

    /** Nature d'une mutation */
    public const NATURE_SAPEUR = 0;
    public const NATURE_CIVIL = 1;

    /** Statuts sapeur / civil d'une personne, recalculés depuis ses mutations */
    public const STATUT_JAMAIS = 0;
    public const STATUT_ACTIF = 1;
    public const STATUT_INACTIF = 2;

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
        if (array_key_exists('remarque', $data)) {
            $data['remarque'] ??= '';
        }
        return $data;
    }

    /**
     * Statuts sapeur et civil recalculés depuis les mutations : jamais (aucune mutation
     * de cette nature), actif (une mutation en cours) ou inactif. Une mutation sapeur
     * compte comme active dès 3 mois avant l'incorporation.
     *
     * @return array{statut_sapeur: int, statut_civil: int}
     */
    private static function statuts($mutations): array
    {
        $now = Carbon::now()->setTime(0, 0);
        $statut = function (int $nature, int $anticipationMonths) use ($mutations, $now) {
            $mutationsNature = $mutations->where('nature', $nature);
            if ($mutationsNature->isEmpty()) {
                return self::STATUT_JAMAIS;
            }
            $isRunning = fn($m) => ($m->sortie === null || Carbon::parse($m->sortie)->gte($now))
                && Carbon::parse($m->incorporation)->subMonths($anticipationMonths)->lte($now);
            return $mutationsNature->contains($isRunning) ? self::STATUT_ACTIF : self::STATUT_INACTIF;
        };

        return [
            'statut_sapeur' => $statut(self::NATURE_SAPEUR, 3),
            'statut_civil' => $statut(self::NATURE_CIVIL, 0),
        ];
    }

    /**
     * Type principal, pour la compatibilité des anciennes versions des apps mobiles :
     * sapeur actif > civil actif > ancien sapeur > ancien civil, recrue sinon. Une
     * personne peut être à la fois sapeur et civil : utiliser statut_sapeur / statut_civil.
     * FIXME: à supprimer une fois les apps mobiles migrées sur statut_sapeur / statut_civil
     */
    public static function typePrincipal(int $statutSapeur, int $statutCivil): int
    {
        return match (true) {
            $statutSapeur === self::STATUT_ACTIF => self::TYPE_SAPEUR,
            $statutCivil === self::STATUT_ACTIF => self::TYPE_CIVIL,
            $statutSapeur === self::STATUT_INACTIF => self::TYPE_SAPEUR,
            $statutCivil === self::STATUT_INACTIF => self::TYPE_CIVIL,
            default => self::TYPE_RECRUE,
        };
    }

    private static function anneeIncorporation($mutations)
    {
        return $mutations->where('nature', self::NATURE_SAPEUR)
            ->map(fn($m) => Carbon::parse($m->incorporation)->year)->min() ?? '';
    }

    /**
     * Recalcul quotidien des statuts sapeur et civil, qui dépendent de la date du jour
     */
    public static function recomputeSapeurStatuts()
    {
        $sapeurs = Sapeur::with('mutations')->get();
        foreach ($sapeurs as $sapeur) {
            self::majStatuts($sapeur, $sapeur->mutations);
        }
    }

    /**
     * Recalcule les statuts sapeur et civil et l'année d'incorporation à partir des
     * mutations (toutes natures confondues) de la personne.
     */
    private static function majStatuts(Sapeur $sapeur, $mutations): void
    {
        $sapeur->fill(self::statuts($mutations));
        $sapeur->annee_incorporation = self::anneeIncorporation($mutations);
        $sapeur->save();
    }

    public static function recomputeSapeurFonctionPrincipale()
    {
        $now = Carbon::now();
        $sapeurs = Sapeur::with([
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
        $sapeurs = Sapeur::where('statut_sapeur', '!=', self::STATUT_JAMAIS)->with([
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

    /**
     * Est ou a été sapeur (au moins une mutation sapeur).
     */
    private static function isSapeur($sapeurId)
    {
        return Sapeur::whereId($sapeurId)->where('statut_sapeur', '!=', self::STATUT_JAMAIS)->exists();
    }

    /**
     * Sapeur ou recrue, c'est-à-dire pas uniquement civil.
     */
    private static function isSapeurOuRecrue($sapeurId)
    {
        return Sapeur::whereId($sapeurId)
            ->where(fn($query) => $query->where('statut_sapeur', '!=', self::STATUT_JAMAIS)
                ->orWhere('statut_civil', self::STATUT_JAMAIS))
            ->exists();
    }

    /**
     * Recrue : aucune mutation, ni sapeur ni civile.
     */
    private static function isRecrue(int $sapeurId): bool
    {
        return Sapeur::whereId($sapeurId)
            ->where('statut_sapeur', self::STATUT_JAMAIS)
            ->where('statut_civil', self::STATUT_JAMAIS)
            ->exists();
    }

    public static function createSapeur($data)
    {
        //TODO: Add iban statut système validation
        if (!self::estAvsValide($data['no_avs'] ?? null)) {
            throw new NotReportableException(['no_avs' => 'Numéro AVS invalide.']);
        }
        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['porteur'] = false;
        // Statuts et année d'incorporation découlent de la mutation ajoutée ensuite
        $sapeur = Sapeur::create($data);

        self::insertMutation($sapeur->id, [
            "nature" => self::NATURE_SAPEUR,
            "localite_id" => $sapeur->localite_id,
            "incorporation" => $data['incorporation'],
            "motif" => ""
        ]);
        return $sapeur->refresh();
    }

    public static function createCivil($data)
    {
        //TODO: Add iban statut système validation
        if (!self::estAvsValide($data['no_avs'] ?? null)) {
            throw new NotReportableException(['no_avs' => 'Numéro AVS invalide.']);
        }
        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['porteur'] = false;
        $data['date_naissance'] = Carbon::yesterday();
        $sapeur = Sapeur::create($data);

        self::insertMutation($sapeur->id, [
            "nature" => self::NATURE_CIVIL,
            "localite_id" => $sapeur->localite_id,
            "incorporation" => $data['incorporation'],
            "motif" => ""
        ]);
        return $sapeur->refresh();
    }

    private static function normaliserAvs(string $noAvs): string
    {
        return preg_replace('/[^0-9]/', '', $noAvs);
    }

    /**
     * Valide la structure et la clé de contrôle d'un IBAN, avec la même bibliothèque que l'export
     * de paiement ISO 20022 (PaiementBusiness) : un IBAN accepté ici mais refusé là-bas bloquerait
     * tout le fichier de paiement du décompte. Un IBAN vide est considéré valide (champ optionnel).
     */
    public static function estIbanValide(?string $iban): bool
    {
        if (trim((string) $iban) === '') {
            return true;
        }

        try {
            new IBAN($iban);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Valide le format et la clé de contrôle d'un numéro AVS suisse : préfixe 756 + 9 chiffres
     * + 1 chiffre de contrôle, calculé selon l'algorithme EAN-13 (somme pondérée 1/3, mod 10).
     * Un numéro vide est considéré valide (le champ est optionnel pour un sapeur/civil).
     */
    public static function estAvsValide(?string $noAvs): bool
    {
        $chiffres = self::normaliserAvs((string) $noAvs);
        if ($chiffres === '') {
            return true;
        }

        if (!preg_match('/^756\d{10}$/', $chiffres)) {
            return false;
        }

        $somme = 0;
        for ($i = 0; $i < 12; $i++) {
            $somme += (int) $chiffres[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $cleControle = (10 - ($somme % 10)) % 10;

        return $cleControle === (int) $chiffres[12];
    }

    /**
     * Vérifie si un numéro AVS est déjà utilisé par un sapeur existant (tout type confondu),
     * en comparant les numéros normalisés (chiffres uniquement).
     */
    public static function avsDejaUtilise(?string $noAvs): bool
    {
        $normalise = self::normaliserAvs((string) $noAvs);
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
        // N° AVS et IBAN sont facultatifs dans le formulaire public : absents, ils sont enregistrés vides.
        $data += ['no_avs' => null, 'iban' => null];

        if (!self::estAvsValide($data['no_avs'])) {
            throw new NotReportableException(['no_avs' => 'Numéro AVS invalide.']);
        }

        if (self::avsDejaUtilise($data['no_avs'])) {
            throw new NotReportableException(['no_avs' => 'Une inscription existe déjà avec ce numéro AVS.']);
        }

        if (!self::estIbanValide($data['iban'])) {
            throw new NotReportableException(['iban' => 'IBAN invalide.'], 'IBAN invalide.');
        }

        // Sans ce contrôle, addTelephone() lèverait une erreur générique (clé `numero`, en anglais)
        // que le formulaire public ne saurait pas rattacher à la bonne ligne.
        $numeros = [];
        foreach ($data['telephones'] ?? [] as $index => $telephone) {
            $numero = Telephone::normaliser($telephone['numero']);
            if (in_array($numero, $numeros, true)) {
                throw new NotReportableException(
                    ["telephones.{$index}.numero" => 'Ce numéro est saisi plusieurs fois.'],
                );
            }
            $numeros[] = $numero;
        }

        $data = self::normalizeNullableFields($data);
        $data['iban_statut'] = 1;
        $data['porteur'] = false;

        $telephones = $data['telephones'] ?? [];
        $permis = $data['permis'] ?? [];
        unset($data['telephones'], $data['permis']);

        // Transaction : si un téléphone ou un permis en double fait échouer une des étapes,
        // on ne veut pas laisser une recrue orpheline en base.
        $recrue = DB::transaction(function () use ($data, $telephones, $permis) {
            $recrue = Sapeur::create($data);
            foreach ($telephones as $telephone) {
                self::addTelephone($recrue->id, $telephone);
            }
            foreach ($permis as $p) {
                self::addPermis($recrue->id, $p);
            }
            return $recrue;
        });

        return $recrue;
    }

    /**
     * Validation d'une recrue par un fourrier : bascule en sapeur réel et crée sa première mutation.
     */
    public static function validateRecrue(int $sapeurId, string $incorporation): Sapeur
    {
        $recrue = Sapeur::where('statut_sapeur', self::STATUT_JAMAIS)
            ->where('statut_civil', self::STATUT_JAMAIS)
            ->findOrFail($sapeurId);

        self::insertMutation($recrue->id, [
            "nature" => self::NATURE_SAPEUR,
            "localite_id" => $recrue->localite_id,
            "incorporation" => $incorporation,
            "motif" => "",
        ]);

        return $recrue->refresh();
    }

    public static function updateSapeurById(int $sapeurId, $data)
    {
        if (array_key_exists('no_avs', $data) && !self::estAvsValide($data['no_avs'])) {
            throw new NotReportableException(['no_avs' => 'Numéro AVS invalide.']);
        }

        if (array_key_exists('iban', $data) && !self::estIbanValide($data['iban'])) {
            throw new NotReportableException(['iban' => 'IBAN invalide.'], 'IBAN invalide.');
        }

        $data = self::normalizeNullableFields($data);
        $sapeur = Sapeur::findOrFail($sapeurId);
        $sapeur->update($data);
        return $sapeur;
    }

    public static function deleteSapeurById(int $sapeurId)
    {
        if (Ecriture::where('sapeur_id', $sapeurId)->exists()) {
            throw new NotReportableException([], "Impossible de supprimer un sapeur lié à une écriture comptable");
        }

        if (ArticleAttribution::where('sapeur_id', $sapeurId)->exists()) {
            throw new NotReportableException([], "Impossible de supprimer un sapeur ayant eu du matériel attribué, désactivez-le plutôt");
        }

        if (Intervention::where('sapeur_id', $sapeurId)->exists()) {
            throw new NotReportableException([], "Impossible de supprimer un sapeur ayant été chef d'intervention");
        }

        CoursSapeur::where('sapeur_id', $sapeurId)->delete();
        Permis::where('sapeur_id', $sapeurId)->delete();
        GradeSapeur::where('sapeur_id', $sapeurId)->delete();
        FonctionSapeur::where('sapeur_id', $sapeurId)->delete();
        SapeurTelephone::where('sapeur_id', $sapeurId)->delete();
        ExerciceSapeur::where('sapeur_id', $sapeurId)->delete();
        InterventionSapeur::where('sapeur_id', $sapeurId)->delete();
        Quittance::where('sapeur_id', $sapeurId)->delete();
        GroupeSapeur::where('sapeur_id', $sapeurId)->delete();
        ControleMedical::where('sapeur_id', $sapeurId)->delete();
        Mutation::where('sapeur_id', $sapeurId)->delete();
        Travail::where('sapeur_id', $sapeurId)->delete();
        Sapeur::findOrFail($sapeurId)->delete();
    }

    public static function addCours(int $sapeurId, $data)
    {
        // Transaction : un échec sur le grade / la fonction ne doit pas laisser un cours orphelin
        return DB::transaction(fn() => self::creerCours($sapeurId, $data));
    }

    private static function creerCours(int $sapeurId, $data)
    {
        if (!self::isSapeur($sapeurId)) {
            throw new NotReportableException([], "Impossible d'ajouter un cours à un civil.");
        }
        self::controlerCoursDuplique($sapeurId, $data['cours_id'], $data['date']);
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
            try {
                self::addFonction(
                    $sapeurId,
                    [
                        'fonction_id' => $data['fonction_id'],
                        'debut' => $data['date_fonction'],
                        'fin' => null,
                        'remarque' => null
                    ]
                );
            } catch (ArrayException $e) {
                $fonction = Fonction::find($data['fonction_id']);
                $message = "La fonction « {$fonction?->nom} » est déjà attribuée à ce sapeur à cette date";
                throw new NotReportableException(['fonction_id' => $message, 'date_fonction' => $message], $message);
            }
        }

        $sapeur = Sapeur::whereId($sapeurId)->first(['fonction_id', 'grade_id']);
        return ['cours' => $cours, 'main_fonction_id' => $sapeur->fonction_id, 'main_grade_id' => $sapeur->grade_id];
    }

    public static function addCoursMultiple(array $sapeurIds, array $data): array
    {
        return DB::transaction(function () use ($sapeurIds, $data) {
            // Collecte les erreurs de tous les sapeurs pour les signaler en une fois
            $cours = [];
            $erreurs = [];
            foreach ($sapeurIds as $index => $sapeurId) {
                try {
                    $cours[] = self::addCours($sapeurId, $data);
                } catch (ArrayException $e) {
                    $sapeur = Sapeur::find($sapeurId, ['nom', 'prenom']);
                    $erreurs["sapeur_ids.$index"] = "{$sapeur->nom} {$sapeur->prenom} : {$e->getMessage()}";
                }
            }

            if ($erreurs !== []) {
                throw new NotReportableException($erreurs, implode("\n", $erreurs));
            }

            return $cours;
        });
    }

    public static function updateCours(int $sapeurId, $data)
    {
        $cours = CoursSapeur::where('sapeur_id', $sapeurId)->findOrFail($data['id']);
        if (isset($data['date'])) {
            self::controlerCoursDuplique($sapeurId, $cours->cours_id, $data['date'], $cours->id);
        }
        $cours->update($data);
        return $cours;
    }

    /**
     * Contrôle que le sapeur n'a pas déjà ce cours à cette date
     */
    private static function controlerCoursDuplique(int $sapeurId, int $coursId, $date, ?int $ignoreId = null): void
    {
        $existe = CoursSapeur::where('sapeur_id', $sapeurId)
            ->where('cours_id', $coursId)
            ->whereDate('date', $date)
            ->when($ignoreId !== null, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($existe) {
            $message = "Ce cours a déjà été ajouté à ce sapeur à cette date";
            throw new NotReportableException(['cours_id' => $message, 'date' => $message], $message);
        }
    }

    public static function removeCours(int $sapeurId, int $coursSapeurId)
    {
        // Check que le cours n'est pas lié à une écriture
        if (Ecriture::where('cours_sapeur_id', $coursSapeurId)->exists()) {
            throw new NotReportableException([], 'Impossible de supprimer un cours facturé');
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
                ], "Fonction déjà attribuée durant cette période");
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
        $fonction = FonctionSapeur::where('sapeur_id', $sapeurId)->find($data['id']);
        if ($fonction === null) {
            throw new NotReportableException([], 'Fonction non trouvée', status: 404);
        }

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
        $fonction = FonctionSapeur::where('sapeur_id', $sapeurId)->find($fonctionSapeurId);
        if ($fonction === null) {
            throw new NotReportableException([], 'Fonction non trouvée', status: 404);
        }
        $fonction->delete();
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
                ], "Deux mutations en conflits");
            }
        }
    }

    /**
     * Met à jour les statuts après une modification des mutations et les retourne.
     *
     * @return array{annee_incorporation: mixed, statut_sapeur: int, statut_civil: int, type: int}
     */
    private static function majStatutsSapeur(int $sapeurId, $mutations): array
    {
        $sapeur = Sapeur::findOrFail($sapeurId);
        self::majStatuts($sapeur, $mutations);

        return [
            "annee_incorporation" => $sapeur->annee_incorporation,
            "statut_sapeur" => $sapeur->statut_sapeur,
            "statut_civil" => $sapeur->statut_civil,
            "type" => self::typePrincipal($sapeur->statut_sapeur, $sapeur->statut_civil),
        ];
    }

    /**
     * Ajout d'une mutation sapeur ou civile. Une recrue doit d'abord être validée.
     */
    public static function addMutation($sapeurId, $data)
    {
        if (self::isRecrue($sapeurId)) {
            throw new ArrayException([], "Impossible d'ajouter une mutation à une recrue, elle doit d'abord être validée.");
        }
        return self::insertMutation($sapeurId, $data);
    }

    /**
     * Les périodes ne sont contrôlées qu'entre mutations de même nature : une personne
     * peut être sapeur et civil en même temps.
     */
    private static function insertMutation(int $sapeurId, array $data): array
    {
        $data['nature'] ??= self::NATURE_SAPEUR;
        $mutations = Mutation::where('sapeur_id', $sapeurId)->get();
        self::verifyMutationPeriode($data, $mutations->where('nature', $data['nature']));

        $data['sapeur_id'] = $sapeurId;
        $data['motif'] ??= '';
        $mutation = Mutation::create($data);

        $mutations->push($mutation);
        return array_merge(["mutation" => $mutation], self::majStatutsSapeur($sapeurId, $mutations));
    }

    /**
     * Modification d'une mutation, sa nature ne change pas.
     */
    public static function updateMutation(int $sapeurId, $data)
    {
        $mutationId = $data['id'];
        $mutation = Mutation::where('sapeur_id', $sapeurId)->findOrFail($mutationId);
        $mutations = Mutation::where('sapeur_id', $sapeurId)->get()->reject(fn($m) => $m->id === $mutationId);
        self::verifyMutationPeriode($data, $mutations->where('nature', $mutation->nature));

        if (array_key_exists('motif', $data)) {
            $data['motif'] ??= '';
        }
        $mutation->update($data);

        $mutations->push($mutation);
        return array_merge(["mutation" => $mutation], self::majStatutsSapeur($sapeurId, $mutations));
    }

    /**
     * Suppression d'une mutation. Supprimer la dernière mutation annule la validation
     * de la recrue : le sapeur repasse en recrue, à condition de n'avoir encore aucun
     * historique (exercice, intervention, écriture ou travail). La dernière mutation
     * d'un civil ne se supprime pas : il faudrait supprimer la personne.
     *
     * @return array{annee_incorporation: mixed, statut_sapeur: int, statut_civil: int, type: int}
     */
    public static function removeMutation(int $sapeurId, int $mutationId): array
    {
        $mutation = Mutation::where('sapeur_id', $sapeurId)->findOrFail($mutationId);
        $otherMutations = Mutation::where('sapeur_id', $sapeurId)->where('id', '!=', $mutationId)->get();
        $isLastMutation = $otherMutations->isEmpty();

        if ($isLastMutation && $mutation->nature === self::NATURE_CIVIL) {
            throw new NotReportableException([], "Impossible de supprimer la dernière mutation d'un civil.");
        }

        if (
            $isLastMutation && (
                ExerciceSapeur::where('sapeur_id', $sapeurId)->exists()
                || InterventionSapeur::where('sapeur_id', $sapeurId)->exists()
                || Intervention::where('sapeur_id', $sapeurId)->exists()
                || Ecriture::where('sapeur_id', $sapeurId)->exists()
                || Travail::where('sapeur_id', $sapeurId)->exists()
            )
        ) {
            throw new NotReportableException([], "Impossible de supprimer la dernière mutation : le sapeur a des exercices, interventions, écritures ou travaux.");
        }

        // Sans mutation restante, les statuts repassent à « jamais » : la personne redevient recrue
        return DB::transaction(function () use ($sapeurId, $mutation, $otherMutations) {
            $mutation->delete();
            return self::majStatutsSapeur($sapeurId, $otherMutations);
        });
    }

    public static function addTelephone(int $sapeurId, $data)
    {
        $numero = Telephone::normaliser($data['numero']);
        $existeDeja = SapeurTelephone::where('sapeur_id', $sapeurId)->get()
            ->contains(fn($tel) => Telephone::normaliser($tel->numero) === $numero);
        if ($existeDeja) {
            throw new ArrayException(['numero' => 'Duplicated numero']);
        }

        $data['sapeur_id'] = $sapeurId;
        return SapeurTelephone::create($data);
    }

    public static function updateTelephone(int $sapeurId, $data)
    {
        $numero = Telephone::normaliser($data['numero']);
        $existeDeja = SapeurTelephone::where('sapeur_id', $sapeurId)->get()
            ->reject(fn($t) => $t->id === $data['id'])
            ->contains(fn($tel) => Telephone::normaliser($tel->numero) === $numero);
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

    public static function updateFonctionPrincipale($sapeurId)
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

    public static function updateMainGrade($sapeurId)
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

        $sapeurs = Sapeur::where('statut_sapeur', self::STATUT_ACTIF)
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
            'trombinoscope.pdf',
            ['Content-Type' => 'application/pdf']
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
                "materiel" => ArticleBusiness::avecDateAttribution(Article::with(['materielType', 'attributionOuverte'])->where('sapeur_id', $sapeurId)->get())
                    ->sortBy('attribution')->values(),
            ],
            $logoPath
        );
        return response()->streamDownload(
            function () use ($content) {
                echo $content;
            },
            'fiche-sapeur.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
