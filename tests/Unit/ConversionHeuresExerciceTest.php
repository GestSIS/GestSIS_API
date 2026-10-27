<?php

namespace Tests\Unit;

use App\Support\ConversionHeuresExercice;
use PHPUnit\Framework\TestCase;

class ConversionHeuresExerciceTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function heureType(): array
    {
        return [
            'id' => 4,
            'designation' => 'Préparation',
            'montant' => '25.00',
            'type' => 1,
            'compte_id' => 7,
            'ecriture_categorie_id' => 3,
            'type_unite_id' => 2,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function heure(array $valeurs = []): array
    {
        return [
            'id' => 11,
            'designation' => 'Préparation thème ARI',
            'quantite' => '1.50',
            'sapeur_id' => 45,
            'exercice_id' => 12,
            'heure_exercice_type_id' => 4,
            'created_at' => '2026-03-02 19:30:00',
            ...$valeurs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function exercice(array $valeurs = []): array
    {
        return ['id' => 12, 'date' => '2026-03-05', 'statut' => 4, 'exercice_comptable_id' => 3, ...$valeurs];
    }

    public function testTypeDepuisHeureType(): void
    {
        $conversion = ConversionHeuresExercice::typeDepuisHeureType($this->heureType(), 'Préparation');

        $this->assertSame([
            'designation' => 'Préparation',
            'actif' => true,
            'ecriture_categorie_id' => 3,
            'type_unite_id' => 2,
            'lien_exercice' => true,
            'lien_intervention' => false,
            'lien_obligatoire' => true,
        ], $conversion['type']);
        $this->assertSame(['type' => 1, 'tarif' => '25.00', 'compte_id' => 7, 'fonction_id' => null], $conversion['ligne']);
    }

    public function testDesignationLibreInchangee(): void
    {
        $this->assertSame('Préparation', ConversionHeuresExercice::designationLibre('Préparation', ['Roulage véhicule']));
    }

    public function testDesignationDejaPriseSuffixee(): void
    {
        $this->assertSame('Préparation (exercice)', ConversionHeuresExercice::designationLibre('Préparation', ['Préparation']));
    }

    public function testStatutDepuisExercice(): void
    {
        $this->assertSame(-1, ConversionHeuresExercice::statutDepuisExercice(0));
        $this->assertSame(0, ConversionHeuresExercice::statutDepuisExercice(1));
        $this->assertSame(0, ConversionHeuresExercice::statutDepuisExercice(2));
        $this->assertSame(1, ConversionHeuresExercice::statutDepuisExercice(3));
        $this->assertSame(2, ConversionHeuresExercice::statutDepuisExercice(4));
    }

    public function testTravailDepuisHeure(): void
    {
        $travail = ConversionHeuresExercice::travailDepuisHeure($this->heure(), $this->exercice(), 9);

        $this->assertSame([
            'designation' => 'Préparation thème ARI',
            'quantite' => '1.50',
            'sapeur_id' => 45,
            'auteur_id' => 45,
            'travail_type_id' => 9,
            'exercice_id' => 12,
            'exercice_comptable_id' => 3,
            'date' => '2026-03-05',
            'date_demande' => '2026-03-02',
            'justification' => '',
            'statut' => 2,
        ], $travail);
    }

    public function testTravailDepuisHeureSansCreatedAt(): void
    {
        $travail = ConversionHeuresExercice::travailDepuisHeure($this->heure(['created_at' => null]), $this->exercice(), 9);

        $this->assertSame('2026-03-05', $travail['date_demande']);
    }

    public function testEcritureCorrespondSurSuffixeEtQuantite(): void
    {
        $heure = $this->heure();
        $ecriture = ['designation' => 'Neuchâtel (Hangar) Exercice section 1 - Préparation thème ARI', 'quantite' => '1.50'];

        $this->assertTrue(ConversionHeuresExercice::ecritureCorrespond($ecriture, $heure));
        $this->assertFalse(ConversionHeuresExercice::ecritureCorrespond([...$ecriture, 'quantite' => '2.00'], $heure));
        // Écriture de présence du même exercice : pas de suffixe
        $this->assertFalse(ConversionHeuresExercice::ecritureCorrespond(['designation' => 'Neuchâtel (Hangar) Exercice section 1', 'quantite' => '1.50'], $heure));
    }

    public function testEcritureAlignee(): void
    {
        $ecriture = ['designation' => 'Neuchâtel (Hangar) Exercice section 1 - Préparation thème ARI'];

        $this->assertSame([
            'travail_id' => 31,
            'module' => 6,
            'designation' => 'Neuchâtel (Hangar) Exercice section 1',
            'complement' => 'Préparation (exercice) - Préparation thème ARI',
        ], ConversionHeuresExercice::ecritureAlignee($ecriture, $this->heure(), 'Préparation (exercice)', 31));
    }
}
