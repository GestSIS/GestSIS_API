<?php

namespace Tests\Unit;

use App\Domaine\Business\AlarmesBusiness;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AlarmesBusinessTest extends TestCase
{
    private function sapeur(int $id, string $nom, string $prenom, array $numeros = []): object
    {
        return (object) [
            'id' => $id,
            'nom' => $nom,
            'prenom' => $prenom,
            'telephones' => array_map(fn($numero) => (object) ['numero' => $numero], $numeros),
        ];
    }

    /**
     * A firefighter entry missing "phone" and "fullname" (malformed payload
     * from the alarm micro-service) must be treated as unresolved instead of
     * throwing, so the whole request doesn't fail for one bad entry.
     */
    public function testResoudreAlarmesHandlesFirefighterMissingPhoneAndFullname(): void
    {
        $sapeurs = [$this->sapeur(1, 'Dupont', 'Jean', ['+41791234567'])];

        $alarme = (object) [
            'firefighters' => [
                (object) [],
            ],
        ];

        $result = AlarmesBusiness::resoudreAlarmes([$alarme], $sapeurs);

        $this->assertCount(1, $result);
        $this->assertSame([], $result[0]->firefighters);
        $this->assertCount(1, $result[0]->unresolved);
    }

    /**
     * An alarme entry missing the "firefighters" property entirely (malformed
     * payload) must not throw and resolves to no firefighters.
     */
    public function testResoudreAlarmesHandlesAlarmeMissingFirefighters(): void
    {
        $sapeurs = [$this->sapeur(1, 'Dupont', 'Jean')];

        $alarme = (object) [];

        $result = AlarmesBusiness::resoudreAlarmes([$alarme], $sapeurs);

        $this->assertCount(1, $result);
        $this->assertSame([], $result[0]->firefighters);
        $this->assertSame([], $result[0]->unresolved);
    }

    public function testResoudreAlarmesResolvesFirefighterByPhone(): void
    {
        $sapeurs = [$this->sapeur(1, 'Dupont', 'Jean', ['0791234567'])];

        $alarme = (object) [
            'firefighters' => [
                (object) ['phone' => '+41791234567', 'fullname' => 'Jean Dupont'],
            ],
        ];

        $result = AlarmesBusiness::resoudreAlarmes([$alarme], $sapeurs);

        $this->assertCount(1, $result[0]->firefighters);
        $this->assertSame(1, $result[0]->firefighters[0]->id);
        $this->assertSame([], $result[0]->unresolved);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function formatsTelephoneEnregistres(): array
    {
        return [
            'national' => ['079 123 45 67'],
            'national compact' => ['0791234567'],
            'préfixe 00' => ['0041 79 123 45 67'],
            'international' => ['+41 79 123 45 67'],
            'avec (0) après l\'indicatif' => ['+41 (0)79 123 45 67'],
            'avec tirets' => ['079-123-45-67'],
            'avec points et barre' => ['079/123.45.67'],
        ];
    }

    /**
     * Quel que soit le format enregistré pour le sapeur, il doit être reconnu par son numéro
     * (le nom de l'alarme ne correspond volontairement pas, pour exclure la méthode par nom).
     */
    #[DataProvider('formatsTelephoneEnregistres')]
    public function testResoudreAlarmesReconnaitLeNumeroQuelQueSoitSonFormat(string $numeroEnregistre): void
    {
        $sapeurs = [$this->sapeur(1, 'Dupont', 'Jean', [$numeroEnregistre])];

        $alarme = (object) [
            'firefighters' => [
                (object) ['phone' => '+41791234567', 'fullname' => 'Nom Inconnu'],
            ],
        ];

        $result = AlarmesBusiness::resoudreAlarmes([$alarme], $sapeurs);

        $this->assertCount(1, $result[0]->firefighters);
        $this->assertSame(1, $result[0]->firefighters[0]->id);
    }

    public function testResoudreAlarmesNeConfondPasUnNumeroEtranger(): void
    {
        $sapeurs = [$this->sapeur(1, 'Dupont', 'Jean', ['0033 6 12 34 56 78'])];

        $alarme = (object) [
            'firefighters' => [
                (object) ['phone' => '+41612345678', 'fullname' => 'Nom Inconnu'],
            ],
        ];

        $result = AlarmesBusiness::resoudreAlarmes([$alarme], $sapeurs);

        $this->assertSame([], $result[0]->firefighters);
        $this->assertCount(1, $result[0]->unresolved);
    }
}
