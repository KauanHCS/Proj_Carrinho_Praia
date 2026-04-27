<?php

namespace CarrinhoDePreia\Tests\Unit;

use CarrinhoDePreia\Security;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        // Garante sessão limpa para cada teste.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
        }
    }

    public function testCsrfTokenIsGeneratedAndValidated(): void
    {
        $token = Security::generateCSRFToken();
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token), 'Token deve ter 64 chars (32 bytes hex)');
        $this->assertTrue(Security::validateCSRFToken($token));
    }

    public function testInvalidCsrfTokenIsRejected(): void
    {
        Security::generateCSRFToken();
        $this->assertFalse(Security::validateCSRFToken('token-invalido'));
    }

    public function testPasswordStrengthRejectsWeakPasswords(): void
    {
        $result = Security::validatePasswordStrength('abc');
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testPasswordStrengthAcceptsStrongPasswords(): void
    {
        $result = Security::validatePasswordStrength('AbcDef12');
        $this->assertTrue($result['valid'], implode(' / ', $result['errors']));
    }

    public function testValidateEmail(): void
    {
        $this->assertTrue(Security::validateEmail('user@example.com'));
        $this->assertFalse(Security::validateEmail('not-an-email'));
    }

    public function testHashAndVerifyPassword(): void
    {
        $hash = Security::hashPassword('s3cret-PASS');
        $this->assertNotSame('s3cret-PASS', $hash);
        $this->assertTrue(Security::verifyPassword('s3cret-PASS', $hash));
        $this->assertFalse(Security::verifyPassword('outra', $hash));
    }

    public function testSanitizeInputRemovesTags(): void
    {
        $result = Security::sanitizeInput('<script>alert(1)</script>texto', 'string');
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testValidateParamTypes(): void
    {
        $rules = ['idade' => 'int', 'email' => 'email'];
        $params = ['idade' => '25', 'email' => 'a@b.com'];
        $result = Security::validateParamTypes($params, $rules);
        $this->assertTrue($result['valid']);

        $params = ['idade' => 'abc', 'email' => 'nao-email'];
        $result = Security::validateParamTypes($params, $rules);
        $this->assertFalse($result['valid']);
    }
}
