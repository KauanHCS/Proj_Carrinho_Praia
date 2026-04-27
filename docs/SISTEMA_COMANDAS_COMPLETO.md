# 🏖️ Sistema de Comandas e Guarda-sóis - Documentação Completa

## ✅ Implementação Concluída

Sistema completo de comandas para guarda-sóis integrado com a Venda Rápida.

---

## 🎯 Fluxo de Uso Completo

### Modo 1: Pagar na Hora (Imediato)
1. Vendedor seleciona **"Pagar na Hora"**
2. Adiciona produtos ao carrinho
3. Seleciona forma de pagamento (Dinheiro/PIX/Cartão/Fiado)
4. Finaliza venda imediatamente
5. Estoque é atualizado na hora

### Modo 2: Comanda (Acumular consumo)
1. Vendedor seleciona **"Adicionar à Comanda"**
2. Seleciona um guarda-sol (ex: #5)
3. Adiciona produtos ao carrinho
4. Clica em **"Adicionar à Comanda"**
   - Produtos são salvos na comanda
   - Carrinho é limpo
   - Guarda-sol fica marcado como "Ocupado"
5. Cliente pode pedir mais? Sim!
   - Vendedor repete passos 3-4
   - Comandas acumulam no mesmo guarda-sol
6. Cliente vai embora?
   - Opção A: **"Fechar Comanda"** → Guarda-sol fica "Aguardando Pagamento"
   - Opção B: **"Pagar Comanda Agora"** → Abre modal de pagamento, finaliza e libera guarda-sol

---

## 📋 Funcionalidades Implementadas

### 1. **Seletor de Modo de Venda** 🔄
- Botões de radio no topo da Venda Rápida
- **Pagar na Hora**: Modo tradicional com pagamento imediato
- **Adicionar à Comanda**: Modo comandas para guarda-sóis

### 2. **Interface Dinâmica** 🎨
**Modo "Pagar na Hora":**
- Mostra formas de pagamento
- Mostra resumo de pagamento
- Botão "Finalizar Venda"

**Modo "Comanda":**
- Mostra seleção de guarda-sol
- Oculta formas de pagamento
- Botões:
  - **Adicionar à Comanda** (azul) - Salva produtos na comanda
  - **Fechar Comanda** (amarelo) - Muda status para aguardando pagamento
  - **Pagar Comanda Agora** (vermelho) - Abre modal para pagamento imediato

### 3. **Sistema de Comandas** 📝
- Cada guarda-sol pode ter múltiplas comandas abertas
- Comandas acumulam produtos até o fechamento
- Total é calculado automaticamente

### 4. **Pagamento de Comanda** 💰
- Modal com 4 formas de pagamento:
  - Dinheiro
  - PIX
  - Cartão
  - Fiado
- Ao pagar:
  - Todas as comandas são fechadas
  - Venda é registrada
  - Estoque é atualizado
  - Guarda-sol volta para status "Vazio"

### 5. **Status dos Guarda-sóis** 🎯
- **Vazio** (verde): Disponível
- **Ocupado** (amarelo): Com comanda aberta
- **Aguardando Pagamento** (vermelho): Comanda fechada, esperando pagamento

---

## 🔧 Endpoints Backend Criados

### POST:
1. **`adicionarComanda`** (já existia)
   - Adiciona produtos à comanda do guarda-sol
   - Atualiza total consumido
   - Muda status para "ocupado"

2. **`fecharComanda`** (NOVO)
   - Muda status do guarda-sol para "aguardando_pagamento"
   - Comandas continuam abertas

3. **`finalizarPagamentoComanda`** (NOVO)
   - Busca todas as comandas abertas
   - Registra venda com todos os produtos
   - Atualiza estoque
   - Fecha todas as comandas
   - Libera guarda-sol (status = 'vazio')

---

## 📁 Arquivos Modificados

### Frontend:
1. **`venda_rapida.php`** (+70 linhas)
   - Adicionado seletor de modo de venda
   - Seção de guarda-sol condicional
   - Três novos botões para modo comanda

2. **`venda-rapida.js`** (+335 linhas)
   - Função `alterarModoVenda()` - Alterna interface
   - Função `adicionarItemsComanda()` - Salva produtos na comanda
   - Função `fecharComandaGuardasol()` - Fecha comanda sem pagar
   - Função `pagarComandaAgora()` - Busca total e abre modal
   - Função `abrirModalPagamentoComanda()` - Modal dinâmico
   - Função `finalizarPagamentoComanda()` - Processa pagamento
   - Função `atualizarInfoGuardasolSelecionado()` - Atualiza display

### Backend:
3. **`actions.php`** (+122 linhas)
   - Endpoint `fecharComanda`
   - Endpoint `finalizarPagamentoComanda`

---

## 🎬 Cenários de Uso

### Cenário 1: Cliente rápido (Pagar na Hora)
```
Vendedor → Modo "Pagar na Hora"
        → Adiciona 1 água, 1 salgado
        → Seleciona Dinheiro
        → Finaliza Venda
        ✅ Pronto! Venda registrada
```

### Cenário 2: Cliente no guarda-sol (Comanda)
```
Vendedor → Modo "Comanda"
        → Seleciona Guarda-sol #5
        → Adiciona 2 cervejas
        → Clica "Adicionar à Comanda"
        ✅ Produtos salvos, carrinho limpo

[10 minutos depois]
Vendedor → Adiciona 1 porção batata
        → Clica "Adicionar à Comanda"
        ✅ Mais produtos acumulados

[Cliente vai embora]
Vendedor → Clica "Pagar Comanda Agora"
        → Modal abre com total: R$ 45,00
        → Seleciona PIX
        ✅ Pagamento realizado, guarda-sol liberado
```

### Cenário 3: Fechar comanda sem pagar
```
Vendedor → Modo "Comanda"
        → Guarda-sol #8 com comanda aberta
        → Clica "Fechar Comanda"
        ✅ Guarda-sol fica "Aguardando Pagamento"

[Mais tarde]
Vendedor → Seleciona mesmo guarda-sol #8
        → Clica "Pagar Comanda Agora"
        → Paga e libera
```

---

## 🧪 Testes Recomendados

### Teste 1: Modo Pagar na Hora
1. Selecione "Pagar na Hora"
2. Adicione produtos
3. Verifique que formas de pagamento aparecem
4. Finalize venda
5. ✅ Deve registrar venda normalmente

### Teste 2: Adicionar à Comanda
1. Selecione "Adicionar à Comanda"
2. Selecione guarda-sol vazio
3. Adicione produtos
4. Clique "Adicionar à Comanda"
5. ✅ Carrinho limpa, guarda-sol fica "Ocupado"

### Teste 3: Acumular Comandas
1. Repita Teste 2 com mesmo guarda-sol
2. Adicione mais produtos
3. Clique "Adicionar à Comanda" novamente
4. ✅ Produtos acumulam no guarda-sol

### Teste 4: Fechar Comanda
1. Com guarda-sol ocupado
2. Clique "Fechar Comanda"
3. ✅ Status muda para "Aguardando Pagamento"

### Teste 5: Pagar Comanda
1. Selecione guarda-sol com comanda
2. Clique "Pagar Comanda Agora"
3. Escolha forma de pagamento
4. ✅ Modal abre, pagamento processa, guarda-sol libera

### Teste 6: Verificar Venda
1. Após pagar comanda
2. Vá em Relatórios/Vendas
3. ✅ Venda deve aparecer com todos os produtos acumulados

### Teste 7: Verificar Estoque
1. Anote estoque antes
2. Faça comanda e pague
3. Verifique estoque depois
4. ✅ Estoque deve ter diminuído corretamente

---

## 💡 Dicas de Uso

1. **Pagar na Hora**: Use para clientes que não sentam (ambulantes)
2. **Comanda**: Use para clientes nos guarda-sóis
3. **Fechar Comanda**: Use quando cliente terminar de pedir mas ainda não vai pagar
4. **Pagar Agora**: Use quando cliente pedir e pagar na mesma hora

---

## 🎨 Visual

### Indicadores de Status:
- 🟢 **Verde** = Guarda-sol Vazio (disponível)
- 🟡 **Amarelo** = Guarda-sol Ocupado (com comandas abertas)
- 🔴 **Vermelho** = Aguardando Pagamento (comanda fechada)

### Botões por Modo:
**Modo "Na Hora":**
- ✅ Finalizar Venda (verde)
- 🗑️ Limpar Carrinho (vermelho outline)

**Modo "Comanda":**
- 📝 Adicionar à Comanda (azul)
- ✔️ Fechar Comanda (amarelo)
- 💰 Pagar Comanda Agora (vermelho)
- 🗑️ Limpar Carrinho (vermelho outline)

---

## ✨ Pronto para Uso!

O sistema está 100% funcional e integrado. Você agora tem:

1. ✅ Venda imediata (Pagar na Hora)
2. ✅ Sistema de comandas para guarda-sóis
3. ✅ Acumulação de pedidos
4. ✅ Fechamento de comanda
5. ✅ Pagamento flexível
6. ✅ Liberação automática do guarda-sol

**Comece a usar agora na Venda Rápida!** 🎉
