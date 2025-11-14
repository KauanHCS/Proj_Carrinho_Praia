<?php

namespace CarrinhoDePreia\Exceptions;

/**
 * AuthenticationException - Exceção para erros de autenticação
 */
class AuthenticationException extends \Exception
{
    public function __construct($message = "Erro de autenticação", $code = 401)
    {
        parent::__construct($message, $code);
    }
}
