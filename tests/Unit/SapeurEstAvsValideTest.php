<?php

namespace Tests\Unit;

use App\Domaine\Business\SapeurBusiness;
use PHPUnit\Framework\TestCase;

class SapeurEstAvsValideTest extends TestCase
{
    public function testAccepteUnNumeroAvsValideFormate(): void
    {
        $this->assertTrue(SapeurBusiness::estAvsValide('756.1234.5678.97'));
    }

    public function testAccepteUnNumeroAvsValideSansPonctuation(): void
    {
        $this->assertTrue(SapeurBusiness::estAvsValide('7561234567897'));
    }

    public function testRefuseUneCleDeControleIncorrecte(): void
    {
        $this->assertFalse(SapeurBusiness::estAvsValide('756.1234.5678.98'));
    }

    public function testRefuseUnPrefixeAutreQue756(): void
    {
        $this->assertFalse(SapeurBusiness::estAvsValide('755.1234.5678.97'));
    }

    public function testRefuseUneLongueurIncorrecte(): void
    {
        $this->assertFalse(SapeurBusiness::estAvsValide('756.1234.5678'));
    }

    public function testAccepteUneValeurVideOuNulleCarLeChampEstOptionnel(): void
    {
        $this->assertTrue(SapeurBusiness::estAvsValide(''));
        $this->assertTrue(SapeurBusiness::estAvsValide(null));
    }
}
