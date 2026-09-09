<?php

namespace Tests\Unit;

use App\Application\Auth\TokenTools;
use PHPUnit\Framework\TestCase;

class TokenToolsTest extends TestCase
{
    public function testValidateTokenThrowsWhenTokenIsNull(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        TokenTools::validateToken(null);
    }

    public function testValidateTokenThrowsWhenTokenIsEmptyString(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        TokenTools::validateToken('');
    }
}
