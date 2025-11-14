<?php

namespace CarrinhoDePreia\Exceptions;

/**
 * ValidationException - Exceção para erros de validação
 */
class ValidationException extends \Exception
{
    private $errors;
    
    public function __construct($errors, $message = "Erro de validação")
    {
        parent::__construct($message);
        $this->errors = is_array($errors) ? $errors : [$errors];
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
