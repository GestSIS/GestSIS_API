<?php

namespace App\Domaine\Business\Materiel;

use App\Domaine\Exceptions\ArrayException;
use App\Models\Article;
use App\Models\Inventaire;
use App\Models\InventaireEmplacement;
use App\Models\InventaireLigne;
use App\Models\MaterielType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Saisie d'un inventaire en cours : vérification des pièces, comptage des lots et
 * matériel trouvé en plus. Rien n'est modifié dans le stock : les écarts sont traités
 * à la clôture.
 *
 * - Une pièce numérotée se vérifie individuellement (présente, absente, défectueuse).
 * - Un lot regroupe les pièces non numérotées d'un même nœud, de même type, même
 *   désignation, même compartiment, même taille et même péremption ; on y saisit la
 *   quantité constatée, dont les pièces défectueuses, et une précision commune.
 * - Une ligne EN_PLUS est toujours présente : ajoutée par erreur, elle se retire.
 *
 * Chaque opération verrouille l'inventaire et le refuse s'il n'est plus en cours.
 */
class InventaireSaisieBusiness
{
    private const STATUTS = ['PRESENT', 'ABSENT', 'DEFECTUEUX'];

    /**
     * Statut (null = à vérifier) d'une pièce. Enregistre qui l'a vérifiée et quand.
     *
     * @throws ArrayException si l'inventaire n'est plus en cours ou si une pièce en plus
     *                        n'est pas présente
     */
    public static function modifierStatut(int $inventaireId, int $ligneId, ?string $statut, ?int $auteurSapeurId): void
    {
        DB::transaction(function () use ($inventaireId, $ligneId, $statut, $auteurSapeurId) {
            self::inventaireEnCours($inventaireId);
            $ligne = self::ligneDe($inventaireId, $ligneId);

            if ($ligne->origine === 'EN_PLUS' && $statut !== 'PRESENT') {
                throw new ArrayException([], "Du matériel trouvé en plus est présent : retirez la ligne s'il a été ajouté par erreur");
            }
            if ($statut !== null && !in_array($statut, self::STATUTS, true)) {
                throw new ArrayException([], "Statut inconnu : {$statut}");
            }

            $ligne->update(self::verification($statut, $auteurSapeurId));
        });
    }

    /**
     * Précision du sapeur sur une pièce, ou sur toutes les pièces d'un lot, sans changer
     * leur statut.
     *
     * @throws ArrayException si l'inventaire n'est plus en cours
     */
    public static function modifierRemarque(int $inventaireId, int $ligneId, ?string $remarque): void
    {
        DB::transaction(function () use ($inventaireId, $ligneId, $remarque) {
            self::inventaireEnCours($inventaireId);
            $ligne = self::ligneDe($inventaireId, $ligneId);
            $values = ['remarque_sapeur' => $remarque === null || trim($remarque) === '' ? null : trim($remarque)];

            if ($ligne->numero !== null) {
                $ligne->update($values);

                return;
            }
            InventaireLigne::duLot($ligne->valeursLot())->update($values);
        });
    }

    /**
     * Quantité constatée du lot de la ligne donnée (null = remettre le lot à vérifier), dont
     * `$defectueux` pièces défectueuses (null = garder le nombre déjà saisi, dans la limite
     * des pièces prévues constatées).
     *
     * - Moins que prévu : les pièces manquantes sont les plus anciennes ; les autres sont présentes.
     * - Plus que prévu : toutes les pièces prévues sont présentes et le surplus devient
     *   des lignes EN_PLUS du même lot.
     * - Défectueuses : parmi les pièces prévues constatées. Une pièce en plus n'est pas
     *   encore en stock : défectueuse, elle s'ignore au traitement de l'écart.
     *
     * @throws ArrayException si l'inventaire n'est plus en cours, si la pièce est numérotée
     *                        ou s'il y a plus de défectueuses que de pièces prévues constatées
     */
    public static function compterLot(int $inventaireId, int $ligneId, ?int $quantite, ?int $defectueux, ?int $auteurSapeurId): void
    {
        DB::transaction(function () use ($inventaireId, $ligneId, $quantite, $defectueux, $auteurSapeurId) {
            self::inventaireEnCours($inventaireId);
            $ligne = self::ligneDe($inventaireId, $ligneId);
            if ($ligne->numero !== null) {
                throw new ArrayException([], 'Une pièce numérotée se vérifie individuellement, pas par quantité');
            }

            self::appliquerQuantite($ligne, $quantite, $defectueux, $auteurSapeurId);
        });
    }

    /**
     * Matériel trouvé en plus dans un nœud de l'inventaire.
     *
     * - Avec numéro : une ligne. Un article existant portant ce numéro y est rattaché
     *   (son type et sa désignation sont repris) ; sinon le type est obligatoire.
     * - Sans numéro : `quantite` pièces du type donné. Si le nœud a déjà un lot prévu de
     *   ce type, de même compartiment, taille et péremption, sa quantité constatée est
     *   augmentée (un lot à vérifier compte comme complet) et la précision s'ajoute à la
     *   sienne ; sinon les pièces forment leur propre lot.
     *
     * Le type est un type existant ou, à défaut, le nom d'un type à créer au traitement
     * (`materiel_type_id` null). `$details` porte ce que le sapeur relève sur l'objet
     * (compartiment, taille, péremption, étiquette, précision), repris au traitement de l'écart.
     *
     * @param array{compartiment?: ?string, taille?: ?string, date_peremption?: ?string, est_etiquete?: ?bool, remarque_sapeur?: ?string} $details
     *
     * @throws ArrayException si l'inventaire n'est plus en cours, si le numéro figure déjà
     *                        dans l'inventaire ou si le type manque
     */
    public static function ajouterEnPlus(
        int $inventaireId,
        int $noeudId,
        ?string $numero,
        ?int $materielTypeId,
        ?string $nouveauType,
        int $quantite,
        array $details,
        ?int $auteurSapeurId,
    ): void {
        DB::transaction(function () use ($inventaireId, $noeudId, $numero, $materielTypeId, $nouveauType, $quantite, $details, $auteurSapeurId) {
            $inventaire = self::inventaireEnCours($inventaireId);
            $noeud = InventaireEmplacement::where('inventaire_id', $inventaire->id)->findOrFail($noeudId);
            $numero = $numero === null || trim($numero) === '' ? null : trim($numero);
            if ($materielTypeId !== null && MaterielType::findOrFail($materielTypeId)->est_emplacement) {
                throw new ArrayException([], "Un véhicule ne s'ajoute pas comme matériel trouvé en plus");
            }
            $releve = self::releve($details);
            // Le compartiment n'a de sens que dans un emplacement compartimenté
            if (!($noeud->emplacement?->est_compartimentable ?? false)) {
                $releve['compartiment'] = '';
            }

            if ($numero !== null) {
                self::ajouterPieceNumerotee($noeud, $numero, $materielTypeId, $nouveauType, $releve, $auteurSapeurId);

                return;
            }

            $values = [
                'inventaire_emplacement_id' => $noeud->id,
                'materiel_type_id' => $materielTypeId,
                'numero' => null,
                'designation' => self::designationDuType($materielTypeId, $nouveauType),
                'compartiment' => $releve['compartiment'],
                'taille' => $releve['taille'],
                'date_peremption' => $releve['date_peremption'],
            ];

            // La précision d'un lot est commune à ses pièces : celle du sapeur s'ajoute à la sienne
            $existante = InventaireLigne::duLot($values)->orderByRaw("origine = 'ATTENDU' DESC")->orderBy('id')->first();
            if ($existante !== null && $releve['remarque_sapeur'] !== null) {
                $releve['remarque_sapeur'] = implode(' · ', array_filter([$existante->remarque_sapeur, $releve['remarque_sapeur']]));
                InventaireLigne::duLot($values)->update(['remarque_sapeur' => $releve['remarque_sapeur']]);
                $existante->remarque_sapeur = $releve['remarque_sapeur'];
            } elseif ($existante !== null) {
                $releve['remarque_sapeur'] = $existante->remarque_sapeur;
            }

            $lotPrevu = $existante?->origine === 'ATTENDU' ? $existante : null;
            if ($lotPrevu !== null) {
                $constate = InventaireLigne::duLot($values)->where(fn (Builder $query) => $query
                    ->whereNull('statut')
                    ->orWhere('statut', '!=', 'ABSENT'))
                    ->count();
                self::appliquerQuantite($lotPrevu, $constate + $quantite, null, $auteurSapeurId);

                return;
            }

            for ($piece = 0; $piece < $quantite; $piece++) {
                self::creerEnPlus([...$values, ...$releve], $auteurSapeurId);
            }
        });
    }

    /**
     * Détails relevés sur l'objet, normalisés pour la ligne.
     *
     * @param array{compartiment?: ?string, taille?: ?string, date_peremption?: ?string, est_etiquete?: ?bool, remarque_sapeur?: ?string} $details
     * @return array{compartiment: string, taille: ?string, date_peremption: ?string, est_etiquete: bool, remarque_sapeur: ?string}
     */
    private static function releve(array $details): array
    {
        $text = fn (?string $value): ?string => $value === null || trim($value) === '' ? null : trim($value);

        return [
            'compartiment' => $text($details['compartiment'] ?? null) ?? '',
            'taille' => $text($details['taille'] ?? null),
            'date_peremption' => ($details['date_peremption'] ?? null) === null ? null : Carbon::parse($details['date_peremption'])->toDateString(),
            'est_etiquete' => (bool) ($details['est_etiquete'] ?? false),
            'remarque_sapeur' => $text($details['remarque_sapeur'] ?? null),
        ];
    }

    /**
     * Retire une ligne de matériel en plus ajoutée par erreur.
     *
     * @throws ArrayException si l'inventaire n'est plus en cours ou si la pièce était prévue
     */
    public static function retirer(int $inventaireId, int $ligneId): void
    {
        DB::transaction(function () use ($inventaireId, $ligneId) {
            self::inventaireEnCours($inventaireId);
            $ligne = self::ligneDe($inventaireId, $ligneId);
            if ($ligne->origine !== 'EN_PLUS') {
                throw new ArrayException([], "Une pièce prévue ne peut pas être retirée : marquez-la manquante si elle n'est pas là");
            }

            $ligne->delete();
        });
    }

    /**
     * « Reste conforme » : les pièces encore à vérifier du nœud (sans ses sous-nœuds)
     * deviennent présentes.
     *
     * @throws ArrayException si l'inventaire n'est plus en cours
     */
    public static function marquerConformes(int $inventaireId, int $noeudId, ?int $auteurSapeurId): int
    {
        return DB::transaction(function () use ($inventaireId, $noeudId, $auteurSapeurId) {
            $inventaire = self::inventaireEnCours($inventaireId);
            $noeud = InventaireEmplacement::where('inventaire_id', $inventaire->id)->findOrFail($noeudId);

            return InventaireLigne::where('inventaire_emplacement_id', $noeud->id)
                ->whereNull('statut')
                ->update(self::verification('PRESENT', $auteurSapeurId));
        });
    }

    /**
     * @throws ArrayException si l'inventaire n'est plus en cours
     */
    private static function inventaireEnCours(int $inventaireId): Inventaire
    {
        $inventaire = Inventaire::lockForUpdate()->findOrFail($inventaireId);
        if ($inventaire->statut !== 'EN_COURS') {
            throw new ArrayException([], "L'inventaire « {$inventaire->designation} » est validé, il ne peut plus être modifié");
        }

        return $inventaire;
    }

    private static function ligneDe(int $inventaireId, int $ligneId): InventaireLigne
    {
        return InventaireLigne::whereHas('inventaireEmplacement', fn (Builder $query) => $query->where('inventaire_id', $inventaireId))
            ->findOrFail($ligneId);
    }

    /**
     * @return array{statut: ?string, verifie_par_sapeur_id: ?int, verifie_le: ?Carbon}
     */
    private static function verification(?string $statut, ?int $auteurSapeurId): array
    {
        return [
            'statut' => $statut,
            'verifie_par_sapeur_id' => $statut === null ? null : $auteurSapeurId,
            'verifie_le' => $statut === null ? null : Carbon::now(),
        ];
    }

    /**
     * Valeurs du lot d'une ligne non numérotée, avec ce que reprend une pièce ajoutée au lot.
     *
     * @return array<string, mixed>
     */
    private static function cleDuLot(InventaireLigne $ligne): array
    {
        return [...$ligne->valeursLot(), ...$ligne->only(['est_etiquete', 'remarque_sapeur'])];
    }


    /**
     * @throws ArrayException s'il y a plus de défectueuses que de pièces prévues constatées
     */
    private static function appliquerQuantite(InventaireLigne $ligne, ?int $quantite, ?int $defectueux, ?int $auteurSapeurId): void
    {
        $values = self::cleDuLot($ligne);

        /** @var Collection<int, InventaireLigne> $prevues les plus anciennes d'abord */
        $prevues = InventaireLigne::duLot($values)->where('origine', 'ATTENDU')->orderBy('id')->lockForUpdate()->get();
        $enPlus = InventaireLigne::duLot($values)->where('origine', 'EN_PLUS')->orderBy('id')->lockForUpdate()->get();

        if ($quantite === null) {
            $prevues->each->update(self::verification(null, $auteurSapeurId));
            $enPlus->each->delete();

            return;
        }

        $manquantes = max(0, $prevues->count() - $quantite);
        $constatees = $prevues->count() - $manquantes;
        if ($defectueux === null) {
            $defectueux = min($constatees, $prevues->where('statut', 'DEFECTUEUX')->count());
        } elseif ($defectueux > $constatees) {
            throw new ArrayException([], "Seules les pièces prévues constatées peuvent être défectueuses ({$constatees} au plus)");
        }

        foreach ($prevues->values() as $index => $prevue) {
            $statut = match (true) {
                $index < $manquantes => 'ABSENT',
                $index < $manquantes + $defectueux => 'DEFECTUEUX',
                default => 'PRESENT',
            };
            $prevue->update(self::verification($statut, $auteurSapeurId));
        }

        $surplus = max(0, $quantite - $prevues->count());
        $enPlus->slice($surplus)->each->delete();
        for ($piece = $enPlus->count(); $piece < $surplus; $piece++) {
            self::creerEnPlus($values, $auteurSapeurId);
        }
    }

    /**
     * @param array<string, mixed> $releve
     * @throws ArrayException si le numéro figure déjà dans l'inventaire ou si le type manque
     */
    private static function ajouterPieceNumerotee(InventaireEmplacement $noeud, string $numero, ?int $materielTypeId, ?string $nouveauType, array $releve, ?int $auteurSapeurId): void
    {
        $existante = InventaireLigne::where('numero', $numero)
            ->whereHas('inventaireEmplacement', fn (Builder $query) => $query->where('inventaire_id', $noeud->inventaire_id))
            ->with('inventaireEmplacement')
            ->first();
        if ($existante !== null) {
            $ou = $existante->inventaireEmplacement->designation;
            throw new ArrayException([], $existante->origine === 'ATTENDU'
                ? "Le n° {$numero} est prévu dans « {$ou} » : marquez-le présent"
                : "Le n° {$numero} a déjà été ajouté dans « {$ou} »");
        }

        $article = Article::nonSupprimes()->where('numero', $numero)->with('materielType')->first();
        if ($article !== null) {
            self::creerEnPlus([
                'inventaire_emplacement_id' => $noeud->id,
                'article_id' => $article->id,
                'materiel_type_id' => $article->materiel_type_id,
                'numero' => $numero,
                'designation' => self::designationArticle($article),
                ...$releve,
            ], $auteurSapeurId);

            return;
        }

        self::creerEnPlus([
            'inventaire_emplacement_id' => $noeud->id,
            'materiel_type_id' => $materielTypeId,
            'numero' => $numero,
            'designation' => self::designationDuType($materielTypeId, $nouveauType),
            ...$releve,
        ], $auteurSapeurId);
    }

    /**
     * Désignation d'un article dans l'inventaire : la sienne, à défaut celle de son type.
     */
    public static function designationArticle(Article $article): string
    {
        return trim($article->designation) === '' ? $article->materielType->designation : $article->designation;
    }

    /**
     * @throws ArrayException si ni type existant ni nom de nouveau type n'est donné
     */
    private static function designationDuType(?int $materielTypeId, ?string $nouveauType): string
    {
        if ($materielTypeId !== null) {
            return MaterielType::findOrFail($materielTypeId)->designation;
        }
        if ($nouveauType === null || trim($nouveauType) === '') {
            throw new ArrayException([], 'Choisissez le type du matériel trouvé, ou donnez le nom du nouveau type');
        }

        return trim($nouveauType);
    }

    /**
     * @param array<string, mixed> $values
     */
    private static function creerEnPlus(array $values, ?int $auteurSapeurId): void
    {
        InventaireLigne::create([
            ...$values,
            'origine' => 'EN_PLUS',
            ...self::verification('PRESENT', $auteurSapeurId),
        ]);
    }
}
