# 🔧 Corrigir Erro do Dashboard - Tabela vendas_itens

## ❌ Erro Encontrado

```
SQLSTATE[42S02]: Base table or view not found: 1146 
Table 'sistema_carrinho.vendas_itens' doesn't exist
```

## 📝 O que aconteceu?

O Dashboard precisa da tabela `vendas_itens` para exibir o **Top 5 Produtos Mais Vendidos**. Esta tabela armazena os detalhes dos itens de cada venda.

## ✅ Solução Rápida

### Opção 1: Executar Migração via Navegador

1. Abra seu navegador
2. Acesse: `http://localhost/Proj_Carrinho_Praia/database/migrations/run_vendas_itens_migration.php`
3. Aguarde a mensagem de sucesso
4. Recarregue o Dashboard

### Opção 2: Executar via phpMyAdmin

1. Abra o phpMyAdmin: `http://localhost/phpmyadmin`
2. Selecione o banco `sistema_carrinho`
3. Clique na aba **SQL**
4. Copie e cole o conteúdo do arquivo:  
   `database/migrations/create_vendas_itens.sql`
5. Clique em **Executar**
6. Aguarde a confirmação
7. Recarregue o Dashboard

### Opção 3: Executar via Linha de Comando

```bash
cd C:\wamp64\www\Proj_Carrinho_Praia\database\migrations
php run_vendas_itens_migration.php
```

## 📊 O que a migração faz?

1. ✅ Cria a tabela `vendas_itens` com a seguinte estrutura:
   - `id` - Chave primária
   - `venda_id` - Referência à venda
   - `produto_id` - Referência ao produto
   - `produto_nome` - Nome do produto
   - `quantidade` - Quantidade vendida
   - `preco_unitario` - Preço por unidade
   - `subtotal` - Total do item (quantidade × preço)
   - `created_at` - Data de criação

2. ✅ Cria as chaves estrangeiras (Foreign Keys)

3. ✅ Popula dados históricos (se existirem vendas antigas)

## 🔍 Verificar se funcionou

Após executar a migração:

1. Abra o Dashboard
2. Pressione `F12` (Console do navegador)
3. Clique no botão **"Atualizar"** do Dashboard
4. Verifique se não há mais o erro da tabela
5. O card **"Top 5 Produtos"** deve:
   - Mostrar produtos se houver vendas
   - Ou mostrar "Nenhuma venda registrada hoje" se não houver

## 🎯 Comportamento após correção

### Se não houver vendas hoje:
- Dashboard carrega normalmente
- Top 5 Produtos mostra: "Nenhuma venda registrada hoje"

### Se houver vendas hoje:
- Dashboard carrega normalmente
- Top 5 Produtos mostra lista vazia (vendas antigas não têm itens detalhados)

### Para popular o Top 5:
- Faça novas vendas usando **"Venda Rápida"**
- As novas vendas já incluirão itens detalhados
- O Top 5 será populado automaticamente

## ⚠️ Notas Importantes

1. **Vendas antigas**: Vendas feitas antes desta migração não terão itens detalhados. Elas aparecem como "Venda Histórica" na tabela.

2. **Novas vendas**: A partir de agora, o sistema **Venda Rápida** deve ser atualizado para salvar os itens em `vendas_itens` automaticamente.

3. **Backup**: Sempre recomendável fazer backup do banco antes de migrar:
   ```sql
   mysqldump -u root sistema_carrinho > backup_antes_migracao.sql
   ```

## 🆘 Problemas?

### Erro de Foreign Key
Se der erro de chave estrangeira:
1. Verifique se as tabelas `vendas` e `produtos` existem
2. Execute a migração novamente

### Tabela já existe
Se a tabela já existir, o script não fará nada (seguro executar múltiplas vezes)

### Sem permissões
Se der erro de permissão:
1. Verifique se o usuário MySQL tem permissão CREATE TABLE
2. Use o usuário `root` (padrão do WAMP)

## 📞 Suporte

Se o erro persistir, verifique:
- ✅ WAMP está rodando
- ✅ MySQL está ativo (ícone verde)
- ✅ Banco `sistema_carrinho` existe
- ✅ Usuário é `root` sem senha (padrão WAMP)

---

**Após corrigir este erro, o Dashboard funcionará completamente!**

✅ KPIs principais  
✅ Gráfico de vendas por hora  
✅ Meta do dia  
✅ Comparações  
✅ Formas de pagamento  
✅ **Top 5 produtos** (agora funciona!)  
✅ Horário de pico  

---

**Desenvolvido para**: Sistema de Gestão de Carrinho de Praia  
**Última atualização**: Janeiro 2025
