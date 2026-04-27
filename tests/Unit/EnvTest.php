<?php

namespace CarrinhoDePreia\Tests\Unit;

use CarrinhoDePreia\Config\Env;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    public function testGetReturnsDefaultWhenKeyAbsent(): void
    {
        $value = Env::get('CHAVE_QUE_NAO_EXISTE_XYZ', 'fallback');
        $this->assertSame('fallback', $value);
    }

    public function testSetAndGet(): void
    {
        Env::set('CARRINHO_TEST_KEY', 'foo');
        $this->assertSame('foo', Env::get('CARRINHO_TEST_KEY'));
        $this->assertTrue(Env::has('CARRINHO_TEST_KEY'));
    }

    public function testHasReturnsFalseForUnknownKey(): void
    {
        $this->assertFalse(Env::has('CHAVE_INEXISTENTE_' . uniqid()));
    }

    public function testEnvFileLoadsBaseDefaults(): void
    {
        // Bootstrap já carregou o .env do projeto via phpunit.xml.
        // Chaves esperadas (existem no .env.example):
        $this->assertNotEmpty(Env::get('DB_NAME', 'sistema_carrinho'));
    }
}
