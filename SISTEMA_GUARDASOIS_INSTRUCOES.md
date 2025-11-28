# 🏖️ Sistema de Guarda-sóis - Instruções de Implementação

## ✅ O que já foi implementado

### 1. **Banco de Dados** ✓
- **Tabelas criadas:**
  - `guardasois`: Armazena os guarda-sóis com número, status, cliente, total
  - `comandas`: Armazena os pedidos acumulados de cada guarda-sol
  - Coluna `guardasol_id` adicionada na tabela `vendas`
- **View criada:**
  - `view_resumo_guardasois`: Agrega dados dos guarda-sóis
- **Status possíveis:**
  - `vazio`: Guarda-sol disponível
  - `ocupado`: Cliente ocupando o guarda-sol
  - `aguardando_pagamento`: Pedidos feitos, aguardando pagamento

### 2. **Backend (API)** ✓
- **Endpoints POST** (`actions.php`):
  - `cadastrarGuardasol`: Cadastra novo guarda-sol com número
  - `ocuparGuardasol`: Marca guarda-sol como ocupado com nome do cliente
  - `adicionarComanda`: Adiciona pedido ao guarda-sol
  - `finalizarGuardasol`: Fecha todas comandas e reseta guarda-sol para "vazio"
- **Endpoints GET** (`actions.php`):
  - `listarGuardasois`: Lista todos os guarda-sóis ordenados por status
  - `obterComandasGuardasol`: Busca comandas abertas de um guarda-sol

### 3. **Frontend** ✓
- **Interface criada em `venda_rapida.php`:**
  - Card de seleção de guarda-sol no header
  - Modal com grid visual de guarda-sóis
  - Filtros por status (Todos, Vazios, Ocupados, Aguardando Pagamento)
  - Cards coloridos com indicadores visuais (verde=vazio, amarelo=ocupado, vermelho=aguardando pag.)
  
- **Estilos CSS** (`venda-rapida.css`):
  - Grid responsivo de guarda-sóis
  - Cards com bordas e animações diferentes por status
  - Ícones visuais (☀️ vazio, ⌛ ocupado, 💵 aguardando pag.)
  
- **JavaScript** (`venda-rapida.js`):
  - `abrirModalGuardasol()`: Abre modal de seleção
  - `carregarGuardasois()`: Carrega lista do servidor
  - `renderizarGuardasois()`: Renderiza grid visual
  - `selecionarGuardasol()`: Seleciona e armazena guarda-sol
  - `filtrarGuardasolStatus()`: Filtra por status

### 4. **Menu** ✓
- Aba "Vendas" removida do sistema
- Apenas "Venda Rápida" permanece

---

## 🔧 Próximos Passos para Completar

### Passo 1: Executar Migração do Banco de Dados
**IMPORTANTE:** Execute primeiro!

1. Acesse no navegador:
   ```
   http://localhost/Proj_Carrinho_Praia/public/executar_migration_guardasol.php
   ```

2. Verifique se as tabelas foram criadas com sucesso:
   - `guardasois` ✓
   - `comandas` ✓
   - `view_resumo_guardasois` ✓

### Passo 2: Cadastrar Guarda-sóis Iniciais
Você precisa cadastrar os guarda-sóis do seu negócio. Há 2 opções:

#### Opção A: Via interface (RECOMENDADO - ainda precisa ser criada)
Criar uma tela administrativa para cadastrar guarda-sóis.

#### Opção B: Via SQL direto
Execute no banco de dados (ajuste os números conforme sua necessidade):

```sql
INSERT INTO guardasois (numero, usuario_id, status) VALUES
(1, SEU_USUARIO_ID, 'vazio'),
(2, SEU_USUARIO_ID, 'vazio'),
(3, SEU_USUARIO_ID, 'vazio'),
(4, SEU_USUARIO_ID, 'vazio'),
(5, SEU_USUARIO_ID, 'vazio'),
(6, SEU_USUARIO_ID, 'vazio'),
(7, SEU_USUARIO_ID, 'vazio'),
(8, SEU_USUARIO_ID, 'vazio'),
(9, SEU_USUARIO_ID, 'vazio'),
(10, SEU_USUARIO_ID, 'vazio');
```

Para descobrir seu `usuario_id`:
```sql
SELECT id, nome, email FROM usuarios;
```

### Passo 3: Integrar Fluxo de Venda com Guarda-sol
Agora é necessário modificar o fluxo de venda para:

#### 3.1. Adicionar validação no `finalizarVendaMista()`
No arquivo `venda-rapida.js`, modificar a função `finalizarVendaMista()` para incluir o guarda-sol:

```javascript
// Dentro da função finalizarVendaMista(), após validações:

// Adicionar validação de guarda-sol (opcional ou obrigatório)
if (!guardasolSelecionado) {
    mostrarAlerta('Selecione um guarda-sol antes de finalizar', 'warning');
    return;
}

// Adicionar ao formData:
formData.append('guardasol_id', guardasolSelecionado.id);
```

#### 3.2. Modificar backend para salvar guarda-sol
No `actions.php`, caso `finalizar_venda`, adicionar após inserção da venda:

```php
// Se houver guardasol_id, adicionar comanda
if (isset($_POST['guardasol_id']) && !empty($_POST['guardasol_id'])) {
    $guardasol_id = intval($_POST['guardasol_id']);
    
    // Preparar produtos para JSON
    $produtos = [];
    foreach ($itens as $item) {
        $produtos[] = [
            'produto_id' => $item['produto_id'],
            'nome' => $item['nome'],
            'quantidade' => $item['quantidade'],
            'preco_unitario' => $item['preco'],
            'subtotal' => $item['preco'] * $item['quantidade']
        ];
    }
    
    // Inserir comanda
    $sql = "INSERT INTO comandas (guardasol_id, usuario_id, produtos, subtotal, status) 
            VALUES (?, ?, ?, ?, 'aberto')";
    $stmt = $conn->prepare($sql);
    $produtos_json = json_encode($produtos);
    $stmt->bind_param("iisd", $guardasol_id, $usuario_id, $produtos_json, $total);
    $stmt->execute();
    
    // Atualizar status do guarda-sol
    $sql = "UPDATE guardasois 
            SET status = 'ocupado', 
                total_consumido = total_consumido + ?,
                horario_ocupacao = NOW()
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $total, $guardasol_id);
    $stmt->execute();
}
```

### Passo 4: Implementar Finalização de Guarda-sol
Criar botão na interface para finalizar o guarda-sol quando o cliente pagar:

#### 4.1. Adicionar botão "Fechar Conta do Guarda-sol"
Na `venda_rapida.php`, no card de seleção de guarda-sol:

```html
<button class="btn btn-danger btn-sm" onclick="finalizarContaGuardasol()">
    <i class="bi bi-cash-coin"></i>
    Fechar Conta
</button>
```

#### 4.2. Adicionar função JavaScript:
```javascript
async function finalizarContaGuardasol() {
    if (!guardasolSelecionado) {
        mostrarAlerta('Nenhum guarda-sol selecionado', 'warning');
        return;
    }
    
    if (!confirm(`Deseja fechar a conta do Guarda-sol #${guardasolSelecionado.numero}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'finalizarGuardasol');
    formData.append('guardasol_id', guardasolSelecionado.id);
    
    try {
        const response = await fetch('../src/Controllers/actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta(`Guarda-sol #${guardasolSelecionado.numero} fechado com sucesso!`, 'success');
            guardasolSelecionado = null;
            document.getElementById('guardasolInfoDisplay').textContent = 'Clique para selecionar um guarda-sol';
            document.getElementById('guardasolInfoDisplay').classList.remove('text-primary');
            document.getElementById('guardasolInfoDisplay').classList.add('text-muted');
        } else {
            mostrarAlerta('Erro ao fechar guarda-sol: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarAlerta('Erro de conexão', 'danger');
    }
}

// Exportar
window.finalizarContaGuardasol = finalizarContaGuardasol;
```

---

## 🎯 Fluxo Completo de Uso

### Cenário 1: Cliente chega em um guarda-sol vazio
1. Vendedor seleciona o guarda-sol (ex: #5)
2. Adiciona produtos ao carrinho
3. Finaliza venda
4. Sistema:
   - Cria comanda vinculada ao guarda-sol
   - Muda status para "ocupado"
   - Incrementa total consumido

### Cenário 2: Cliente pede mais produtos (mesmo guarda-sol)
1. Vendedor seleciona mesmo guarda-sol (#5)
2. Adiciona novos produtos
3. Finaliza venda
4. Sistema:
   - Adiciona nova comanda ao guarda-sol
   - Acumula no total consumido
   - Status continua "ocupado"

### Cenário 3: Cliente vai pagar e sair
1. Vendedor clica em "Fechar Conta do Guarda-sol #5"
2. Sistema:
   - Fecha todas as comandas abertas
   - Muda status para "vazio"
   - Zera total consumido
   - Limpa cliente_nome
3. Guarda-sol fica disponível para próximo cliente

---

## 📋 Checklist de Implementação

- [✅] Banco de dados criado
- [✅] Endpoints backend criados
- [✅] Interface visual criada
- [✅] Modal de seleção funcionando
- [ ] **Migração executada no banco**
- [ ] **Guarda-sóis cadastrados**
- [ ] **Integração com fluxo de venda**
- [ ] **Botão de fechar conta implementado**
- [ ] **Testes completos**

---

## 🧪 Testes Recomendados

1. **Teste de seleção:**
   - Abrir modal de guarda-sóis
   - Visualizar grid com status coloridos
   - Selecionar um guarda-sol
   - Verificar exibição no header

2. **Teste de criação de comanda:**
   - Selecionar guarda-sol vazio
   - Adicionar produtos
   - Finalizar venda
   - Verificar no banco se comanda foi criada
   - Verificar se status mudou para "ocupado"

3. **Teste de acumulação:**
   - Selecionar mesmo guarda-sol
   - Adicionar mais produtos
   - Finalizar venda
   - Verificar se total acumulou

4. **Teste de fechamento:**
   - Clicar em "Fechar Conta"
   - Verificar se status voltou para "vazio"
   - Verificar se total zerou
   - Verificar se comandas foram fechadas

---

## 🎨 Melhorias Futuras (Opcional)

1. **Tela administrativa de guarda-sóis:**
   - Cadastrar/editar/excluir guarda-sóis
   - Ver histórico de cada guarda-sol
   - Relatórios de ocupação

2. **Visualização em tempo real:**
   - Dashboard com grid de todos os guarda-sóis
   - Atualização automática de status
   - Tempo de ocupação em tempo real

3. **Relatórios:**
   - Guarda-sóis mais rentáveis
   - Tempo médio de ocupação
   - Produtos mais vendidos por guarda-sol

4. **Notificações:**
   - Alerta quando guarda-sol fica muito tempo ocupado
   - Lembrete para fechar conta

---

## 📞 Suporte

Se encontrar problemas:
1. Verifique o console do navegador (F12)
2. Verifique os logs do PHP
3. Teste os endpoints diretamente via Postman
4. Verifique se as tabelas foram criadas no banco

**Arquivos modificados:**
- `index.php` (menu)
- `venda_rapida.php` (interface)
- `venda-rapida.css` (estilos)
- `venda-rapida.js` (lógica)
- `actions.php` (backend)
- `create_guardasois.sql` (migração)
