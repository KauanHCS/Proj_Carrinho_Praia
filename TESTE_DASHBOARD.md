# Guia de Testes - Dashboard Melhorado

Este documento contém os testes para validar todas as funcionalidades do Dashboard.

## ✅ Checklist de Testes

### 1. Acesso ao Dashboard

- [ ] Fazer login como **Administrador**
- [ ] Verificar se o Dashboard é carregado automaticamente (primeira aba)
- [ ] Verificar se o ícone 🏎️ (velocímetro) aparece no menu sidebar
- [ ] Verificar se o header mostra "Dashboard"

### 2. KPIs Principais (4 cards no topo)

#### Card 1: Faturamento Hoje
- [ ] Exibe valor em R$ formatado (ex: R$ 150,00)
- [ ] Mostra comparação com ontem em % (verde ⬆️ ou vermelho ⬇️)
- [ ] Ícone: 💰 (dinheiro)
- [ ] Background: Gradiente roxo

#### Card 2: Ticket Médio
- [ ] Exibe valor em R$ formatado
- [ ] Mostra comparação com ontem em %
- [ ] Ícone: 🧾 (recibo)
- [ ] Background: Gradiente verde

#### Card 3: Atendimentos Hoje
- [ ] Exibe número de vendas realizadas
- [ ] Mostra diferença numérica com ontem (+X ou -X)
- [ ] Ícone: 👥 (pessoas)
- [ ] Background: Gradiente azul

#### Card 4: Clima - Praia Grande
- [ ] Exibe temperatura em °C
- [ ] Mostra descrição do clima (Ensolarado, Nublado, etc.)
- [ ] Ícone muda conforme clima (☀️ ⛅ 🌧️)
- [ ] Background: Gradiente laranja/rosa
- [ ] **Nota**: Requer configuração da API Key (ver CONFIGURAR_API_CLIMA.md)

### 3. Gráfico de Vendas por Hora

- [ ] Gráfico de linha com 24 horas (0h a 23h)
- [ ] Exibe valores em R$ no eixo Y
- [ ] Valores corretos para cada hora
- [ ] Horas sem venda mostram R$ 0,00
- [ ] Tooltip mostra valor ao passar mouse
- [ ] Cor: Azul claro (#0dcaf0)
- [ ] Suavização da linha (tension: 0.4)

### 4. Meta do Dia

- [ ] Exibe meta configurada (padrão: R$ 500,00)
- [ ] Exibe valor atual alcançado
- [ ] Barra de progresso animada
- [ ] Percentual calculado corretamente
- [ ] Restante calculado corretamente
- [ ] Cor da barra muda conforme progresso:
  - [ ] Vermelho: < 30%
  - [ ] Amarelo: 30% - 70%
  - [ ] Verde: > 70%
- [ ] Botão "Editar Meta" abre modal
- [ ] Modal permite alterar o valor da meta
- [ ] Meta salva no localStorage (persiste entre sessões)

### 5. Top 5 Produtos - Hoje

- [ ] Lista até 5 produtos mais vendidos
- [ ] Ordenação: Maior quantidade vendida primeiro
- [ ] Medalhas: 🥇 1º, 🥈 2º, 🥉 3º, 4º, 5º
- [ ] Exibe quantidade de unidades vendidas
- [ ] Exibe valor total faturado (R$)
- [ ] Barra de progresso proporcional ao líder
- [ ] Se não houver vendas, mostra mensagem "Nenhuma venda registrada hoje"

### 6. Gráfico Formas de Pagamento

- [ ] Gráfico tipo Donut (rosquinha)
- [ ] Cores corretas:
  - [ ] Dinheiro: Verde (#198754)
  - [ ] PIX: Azul claro (#0dcaf0)
  - [ ] Cartão: Azul (#0d6efd)
  - [ ] Fiado: Amarelo (#ffc107)
- [ ] Considera pagamentos mistos (soma todas as formas)
- [ ] Tooltip mostra valor em R$ e percentual
- [ ] Legenda abaixo do gráfico

### 7. Comparação com Ontem

- [ ] Faturamento de ontem em R$
- [ ] Diferença percentual (badge verde/vermelho)
- [ ] Atendimentos de ontem (número)
- [ ] Diferença numérica (badge verde/vermelho)
- [ ] Ticket médio de ontem em R$
- [ ] Diferença percentual (badge verde/vermelho)

### 8. Comparação com Semana Passada

- [ ] Mesmo formato da comparação com ontem
- [ ] Compara com a mesma data, 7 dias atrás
- [ ] Faturamento, atendimentos e ticket médio
- [ ] Badges de diferença funcionando

### 9. Horário de Pico

- [ ] Exibe hora com maior número de vendas (formato: 14h)
- [ ] Exibe quantidade de vendas naquela hora
- [ ] Ícone: 🔥 (fogo) com animação de pulso
- [ ] Se não houver vendas, mostra "--:--"

### 10. Atualização em Tempo Real

- [ ] Indicador "Ao vivo" com LED piscando (verde)
- [ ] Botão "Atualizar" funciona manualmente
- [ ] Atualização automática a cada 30 segundos
- [ ] Indicador pisca ao atualizar
- [ ] Console.log mostra "Dashboard inicializado"

### 11. Responsividade Mobile

#### Testar em largura < 768px

- [ ] Header do dashboard empilha verticalmente
- [ ] 4 KPIs principais em 1 coluna
- [ ] Gráficos se ajustam à largura
- [ ] Top produtos empilha itens verticalmente
- [ ] Comparações centralizam conteúdo
- [ ] Meta do dia mantém legibilidade
- [ ] Botões e controles são touch-friendly

### 12. Animações e Transições

- [ ] KPIs aparecem com fadeIn
- [ ] Cards têm efeito hover (levitação)
- [ ] Barra de progresso anima suavemente
- [ ] Gráficos carregam com animação
- [ ] Ícone de clima anima ao trocar
- [ ] Badge "Ao vivo" pisca continuamente

### 13. Modal de Editar Meta

- [ ] Abre ao clicar em "Editar Meta"
- [ ] Input pré-preenchido com valor atual
- [ ] Aceita apenas números positivos
- [ ] Validação: não aceita valores ≤ 0
- [ ] Botão "Salvar Meta" atualiza valor
- [ ] Valor salvo no localStorage
- [ ] Progress bar recalcula imediatamente
- [ ] Efeito visual ao salvar (scale animation)
- [ ] Modal fecha após salvar

### 14. Integração com Banco de Dados

#### Testar com dados reais

- [ ] Fazer uma venda usando "Venda Rápida"
- [ ] Voltar ao Dashboard e clicar em "Atualizar"
- [ ] Verificar se faturamento aumentou
- [ ] Verificar se número de atendimentos aumentou
- [ ] Verificar se ticket médio foi recalculado
- [ ] Verificar se produto aparece no Top 5
- [ ] Verificar se forma de pagamento aparece no gráfico
- [ ] Verificar se vendas por hora foi atualizada
- [ ] Verificar se horário de pico mudou (se aplicável)

#### Testar sem dados (banco vazio)

- [ ] Dashboard não quebra
- [ ] Todos os valores mostram R$ 0,00 ou 0
- [ ] Gráficos mostram estrutura vazia
- [ ] Top produtos mostra mensagem apropriada
- [ ] Nenhum erro no console

### 15. Performance

- [ ] Dashboard carrega em menos de 3 segundos
- [ ] Atualização automática não trava a interface
- [ ] Gráficos renderizam sem lag
- [ ] Transições são suaves (60fps)
- [ ] Sem memory leaks (verificar no DevTools)

### 16. Console do Navegador (F12)

#### Mensagens esperadas:
- [ ] "Dashboard inicializado"
- [ ] "Atualizando dashboard manualmente..." (ao clicar em Atualizar)
- [ ] Sem erros relacionados ao Dashboard

#### Erros aceitáveis:
- [ ] Erro de clima se API key não configurada
- [ ] "Configure API key" no card de clima

## 🧪 Cenários de Teste Específicos

### Cenário 1: Primeiro Uso (Dados Zerados)
1. Limpar localStorage: `localStorage.clear()` no console
2. Recarregar página
3. Verificar se meta padrão é R$ 500,00
4. Verificar se todos os KPIs mostram 0

### Cenário 2: Dia Com Muitas Vendas
1. Fazer 10+ vendas com valores variados
2. Usar formas de pagamento diferentes
3. Fazer vendas em horários diferentes
4. Atualizar dashboard
5. Verificar se todos os dados batem

### Cenário 3: Mudança de Dia
1. Simular dados de "ontem" (alterar data no banco)
2. Verificar se comparações funcionam
3. Verificar se "hoje" mostra dados corretos

### Cenário 4: Meta Atingida
1. Configurar meta baixa (ex: R$ 50,00)
2. Fazer vendas até ultrapassar
3. Verificar se barra fica verde
4. Verificar se percentual ultrapassa 100%
5. Observar celebração no console (🎉)

### Cenário 5: Pagamento Misto
1. Fazer venda com 2 ou 3 formas de pagamento
2. Atualizar dashboard
3. Verificar se gráfico de formas soma todas as partes

## 📝 Registro de Bugs

Use este formato para reportar problemas encontrados:

```
BUG #X
Descrição: [Descreva o problema]
Passo a passo: [Como reproduzir]
Esperado: [O que deveria acontecer]
Obtido: [O que realmente aconteceu]
Screenshot: [Se aplicável]
Console: [Erros no console]
Prioridade: [Alta/Média/Baixa]
```

## ✅ Teste Final: Checklist Rápido

Antes de considerar o Dashboard aprovado, verifique:

- [ ] Todos os 4 KPIs principais funcionam
- [ ] Gráfico de vendas por hora renderiza
- [ ] Top 5 produtos lista corretamente
- [ ] Gráfico de formas de pagamento funciona
- [ ] Comparações com ontem/semana mostram valores
- [ ] Meta do dia calcula e atualiza
- [ ] Horário de pico identifica corretamente
- [ ] Atualização automática funciona (30s)
- [ ] Botão manual de atualizar funciona
- [ ] Modal de editar meta salva valores
- [ ] Responsivo em mobile (< 768px)
- [ ] Nenhum erro crítico no console
- [ ] Performance aceitável (< 3s carregamento)

---

## 🎯 Critérios de Aprovação

O Dashboard é considerado **aprovado** se:

1. ✅ Todos os itens do "Checklist Rápido" estão funcionando
2. ✅ Não há erros críticos no console
3. ✅ Performance é aceitável
4. ✅ Responsividade funciona em mobile
5. ✅ Atualização em tempo real opera corretamente

## 📊 Resultado dos Testes

**Data do Teste**: ___/___/_____  
**Testado por**: _________________  
**Aprovado**: [ ] Sim [ ] Não  
**Observações**:  
_______________________________________  
_______________________________________  
_______________________________________  

---

**Desenvolvido para**: Sistema de Gestão de Carrinho de Praia  
**Última atualização**: Janeiro 2025
