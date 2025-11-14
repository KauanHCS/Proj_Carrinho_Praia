<?php

namespace CarrinhoDePreia\Validators;

use CarrinhoDePreia\Exceptions\ValidationException;

/**
 * ProductValidator - Validação de dados de produtos
 */
class ProductValidator
{
    private $errors = [];
    private $validCategories = ['bebida', 'comida', 'acessorio', 'outros'];
    
    public function validate($data, $isUpdate = false)
    {
        $this->errors = [];
        
        // Nome
        if (isset($data['nome'])) {
            $this->validateName($data['nome']);
        } elseif (!$isUpdate) {
            $this->errors['nome'] = 'Nome é obrigatório';
        }
        
        // Categoria
        if (isset($data['categoria'])) {
            $this->validateCategory($data['categoria']);
        } elseif (!$isUpdate) {
            $this->errors['categoria'] = 'Categoria é obrigatória';
        }
        
        // Preços
        if (isset($data['preco_compra'])) {
            $this->validatePrice($data['preco_compra'], 'Preço de compra');
        }
        
        if (isset($data['preco_venda'])) {
            $this->validatePrice($data['preco_venda'], 'Preço de venda');
        }
        
        // Validar relação entre preços
        if (isset($data['preco_compra']) && isset($data['preco_venda'])) {
            if ($data['preco_venda'] <= $data['preco_compra']) {
                $this->errors['preco_venda'] = 'Preço de venda deve ser maior que o de compra';
            }
        }
        
        // Quantidade
        if (isset($data['quantidade'])) {
            $this->validateQuantity($data['quantidade']);
        } elseif (!$isUpdate) {
            $this->errors['quantidade'] = 'Quantidade é obrigatória';
        }
        
        // Limite mínimo
        if (isset($data['limite_minimo'])) {
            $this->validateQuantity($data['limite_minimo'], 'Limite mínimo');
        }
        
        // Validade (opcional)
        if (isset($data['validade']) && !empty($data['validade'])) {
            $this->validateDate($data['validade']);
        }
        
        return empty($this->errors);
    }
    
    private function validateName($nome)
    {
        if (empty($nome)) {
            $this->errors['nome'] = 'Nome não pode ser vazio';
            return;
        }
        
        if (strlen($nome) < 2) {
            $this->errors['nome'] = 'Nome deve ter no mínimo 2 caracteres';
        }
        
        if (strlen($nome) > 150) {
            $this->errors['nome'] = 'Nome deve ter no máximo 150 caracteres';
        }
    }
    
    private function validateCategory($categoria)
    {
        if (!in_array($categoria, $this->validCategories)) {
            $this->errors['categoria'] = 'Categoria inválida. Opções: ' . implode(', ', $this->validCategories);
        }
    }
    
    private function validatePrice($price, $field = 'Preço')
    {
        if (!is_numeric($price)) {
            $this->errors[strtolower(str_replace(' ', '_', $field))] = "$field deve ser numérico";
            return;
        }
        
        if ($price <= 0) {
            $this->errors[strtolower(str_replace(' ', '_', $field))] = "$field deve ser maior que zero";
        }
    }
    
    private function validateQuantity($quantity, $field = 'Quantidade')
    {
        if (!is_numeric($quantity)) {
            $this->errors[strtolower(str_replace(' ', '_', $field))] = "$field deve ser numérica";
            return;
        }
        
        if (intval($quantity) != $quantity) {
            $this->errors[strtolower(str_replace(' ', '_', $field))] = "$field deve ser um número inteiro";
            return;
        }
        
        if ($quantity < 0) {
            $this->errors[strtolower(str_replace(' ', '_', $field))] = "$field não pode ser negativa";
        }
    }
    
    private function validateDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$d || $d->format('Y-m-d') !== $date) {
            $this->errors['validade'] = 'Data de validade inválida (formato: YYYY-MM-DD)';
            return;
        }
        
        if ($d < new \DateTime()) {
            $this->errors['validade'] = 'Data de validade não pode ser no passado';
        }
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function throwIfInvalid()
    {
        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }
}
