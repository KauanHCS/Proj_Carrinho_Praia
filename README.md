# 🏖️ Sistema de Gestão para Carrinhos de Praia

Sistema completo de gerenciamento de vendas, estoque e financeiro para carrinhos de praia, desenvolvido especialmente para Praia Grande/SP.

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias](#tecnologias)
- [Instalação](#instalação)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Como Usar](#como-usar)
- [Documentação](#documentação)
- [Tipos de Usuário](#tipos-de-usuário)

## 🎯 Sobre o Projeto

Sistema desenvolvido para otimizar a gestão de vendas em ambientes de praia, permitindo controle completo de estoque, vendas rápidas, comandas de guarda-sóis, fiado/caderneta e relatórios financeiros.

### Principais Diferenciais

- ✅ **Venda Rápida**: Interface otimizada para vendas ágeis
- ✅ **Guarda-sóis e Comandas**: Controle de consumo livre com pagamento posterior
- ✅ **Fiado/Caderneta**: Sistema completo de crédito para clientes
- ✅ **Pedidos**: Integração automática com cozinha
- ✅ **Multi-usuário**: 6 tipos diferentes de permissões
- ✅ **Responsivo**: Funciona em desktop, tablet e mobile

## ⚡ Funcionalidades

### 🛒 Venda Rápida
- Busca instantânea de produtos
- Múltiplas formas de pagamento
- Pagamento misto (dinheiro + cartão + PIX)
- Campo opcional para nome do cliente
- Integração com comandas de guarda-sóis

### ☂️ Guarda-sóis e Comandas
- Gerenciamento de guarda-sóis numerados
- Comandas abertas com consumo livre
- Fechamento de conta com múltiplos pagamentos
- Histórico completo de consumo

### 📝 Sistema de Pedidos
- Criação automática de pedidos da comanda
- Botões diretos para atualização de status
- Fluxo: Pendente → Em Preparo → Pronto → Entregue
- Integração com financeiro

### 💰 Financeiro
- Cards detalhados de vendas
- Identificação de número do guarda-sol
- Produtos vendidos visíveis
- Múltiplas formas de pagamento
- Filtros por data, status e vendedor

### 📊 Fiado/Caderneta
- Cadastro de clientes com limite de crédito
- Histórico completo de compras e pagamentos
- Controle de saldo devedor
- Alertas de limite

### 📦 Produtos e Estoque
- Cadastro completo de produtos
- Controle de estoque em tempo real
- Alertas de estoque baixo
- Categorias organizadas

### 📈 Relatórios e Dashboard
- Dashboard com estatísticas em tempo real
- Gráficos de vendas
- Relatórios detalhados
- Exportação de dados

### 👥 Gerenciamento de Funcionários
- Códigos únicos para cada administrador
- 5 funções diferentes para funcionários
- Controle granular de permissões

## 🛠️ Tecnologias

### Backend
- **PHP 8+**: Linguagem principal
- **MySQL 8+**: Banco de dados
- **PDO**: Camada de abstração de banco

### Frontend
- **HTML5/CSS3**: Estrutura e estilo
- **JavaScript ES6+**: Lógica client-side
- **Bootstrap 5**: Framework CSS
- **Bootstrap Icons**: Ícones
- **Chart.js**: Gráficos

### Servidor
- **Apache 2.4+**: Servidor web
- **WAMP/XAMPP**: Ambiente de desenvolvimento

## 📥 Instalação

### Requisitos
- WAMP Server / XAMPP
- PHP 8.0 ou superior
- MySQL 8.0 ou superior
- Navegador moderno (Chrome, Edge, Firefox)

### Passo a Passo

1. **Clone ou baixe o projeto**
   ```bash
   # Coloque na pasta www do WAMP ou htdocs do XAMPP
   C:\wamp64\www\Proj_Carrinho_Praia\
   ```

2. **Crie o banco de dados**
   ```sql
   -- Acesse http://localhost/phpmyadmin
   -- Execute o script: database/migrations/sistema_carrinho.sql
   ```

3. **Configure o banco de dados**
   ```php
   // Edite: config/database.php
   // Configure suas credenciais do MySQL
   ```

4. **Acesse o sistema**
   ```
   http://localhost/Proj_Carrinho_Praia/public/login.php
   ```

5. **Login inicial**
   - Email: `demo@carrinho.com`
   - Senha: `123456`

Para mais detalhes, consulte: [INSTALACAO.md](INSTALACAO.md)

## 📂 Estrutura do Projeto

```
Proj_Carrinho_Praia/
├── config/                  # Configurações
│   └── database.php         # Conexão com banco
├── public/                  # Arquivos públicos
│   ├── assets/             
│   │   ├── css/            # Estilos
│   │   ├── js/             # JavaScripts
│   │   └── images/         # Imagens
│   ├── index.php           # Sistema principal
│   ├── login.php           # Tela de login
│   └── health.php          # Diagnóstico do sistema
├── src/                     # Código fonte
│   ├── Controllers/        
│   │   └── actions.php     # API principal (endpoints)
│   ├── Views/              # Interfaces do sistema
│   │   ├── dashboard.php
│   │   ├── venda_rapida.php
│   │   ├── guardasois.php
│   │   ├── pedidos.php
│   │   ├── financeiro.php
│   │   ├── fiado.php
│   │   ├── produtos.php
│   │   ├── estoque.php
│   │   └── ...
│   └── Classes/            # Classes auxiliares
├── database/               # Scripts de banco
│   └── migrations/         # Migrações SQL
├── scripts/                # Scripts utilitários
├── docs/                   # Documentação adicional
├── logs/                   # Arquivos de log
├── limpar_cache.html       # Ferramenta de limpeza
├── GUIA_APRESENTACAO.md    # Guia de apresentação
├── INSTALACAO.md           # Guia de instalação
└── README.md               # Este arquivo
```

## 🚀 Como Usar

### Para Vendedores

1. **Fazer uma venda rápida**
   - Acesse: Venda Rápida
   - Busque produtos pelo nome
   - Adicione ao carrinho
   - Escolha forma de pagamento
   - Finalize a venda

2. **Gerenciar guarda-sóis**
   - Acesse: Guarda-sóis
   - Crie/edite guarda-sóis
   - Adicione items à comanda
   - Feche a conta quando solicitado

3. **Vender fiado**
   - Acesse: Fiado/Caderneta
   - Selecione o cliente
   - Registre a compra
   - Acompanhe o saldo

### Para Administradores

1. **Gerenciar produtos**
   - Cadastre novos produtos
   - Ajuste preços e estoque
   - Organize por categorias

2. **Acompanhar vendas**
   - Visualize dashboard
   - Gere relatórios
   - Exporte dados

3. **Gerenciar funcionários**
   - Crie códigos de acesso
   - Defina funções e permissões
   - Monitore atividades

## 📚 Documentação

### Documentos Principais
- [INSTALACAO.md](INSTALACAO.md) - Guia completo de instalação
- [GUIA_APRESENTACAO.md](GUIA_APRESENTACAO.md) - Preparação para apresentação
- [VERIFICACAO_SISTEMA.md](VERIFICACAO_SISTEMA.md) - Checklist de verificação

### Documentos de Referência
- [SISTEMA_COMANDAS_COMPLETO.md](SISTEMA_COMANDAS_COMPLETO.md) - Sistema de comandas
- [SISTEMA_FIADO_COMPLETO.md](SISTEMA_FIADO_COMPLETO.md) - Sistema de fiado
- [INTEGRACAO_FIADO_VENDA_RAPIDA.md](INTEGRACAO_FIADO_VENDA_RAPIDA.md) - Integração

### Histórico
- [CHANGELOG_COMANDAS_PEDIDOS.md](CHANGELOG_COMANDAS_PEDIDOS.md) - Mudanças recentes

## 👥 Tipos de Usuário

### 1. Administrador
**Acesso completo** a todas as funcionalidades do sistema.

### 2. Funcionário - Anotar Pedido
- ✅ Venda Rápida
- ✅ Fiado/Caderneta
- ✅ Guarda-sóis
- ✅ Produtos (visualização)
- ✅ Perfil

### 3. Funcionário - Fazer Pedido
- ✅ Pedidos (cozinha)
- ✅ Estoque
- ✅ Perfil

### 4. Funcionário - Financeiro
- ✅ Venda Rápida
- ✅ Fiado
- ✅ Guarda-sóis
- ✅ Pedidos
- ✅ Estoque
- ✅ Perfil

### 5. Funcionário - Financeiro + Anotar
Combina permissões de Financeiro e Anotar Pedido.

### 6. Funcionário - Ambos (Anotar + Fazer)
Combina permissões de Anotar e Fazer Pedido.

## 🔧 Manutenção

### Limpar Cache (Antes de Apresentações)
```
http://localhost/Proj_Carrinho_Praia/limpar_cache.html
```

### Diagnóstico do Sistema
```
http://localhost/Proj_Carrinho_Praia/public/health.php
```

### Backup do Banco
```sql
-- Via phpMyAdmin: Exportar > sistema_carrinho
```

## 🐛 Solução de Problemas

### Erro 500 / Tela Branca
1. Verifique se WAMP está verde (Online)
2. Confira: `logs/php_errors.log`
3. Valide: `config/database.php`

### Erro de Conexão
1. Certifique-se que MySQL está rodando
2. Verifique credenciais em `config/database.php`
3. Teste conexão via phpMyAdmin

### Cache Antigo
1. Pressione `Ctrl + Shift + Delete`
2. Ou use: `limpar_cache.html`
3. Ou abra janela anônima: `Ctrl + Shift + N`

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais e comerciais.

## 👨‍💻 Autor

Desenvolvido para gestão de carrinhos de praia em Praia Grande/SP.

## 🤝 Contribuições

Para melhorias ou sugestões, entre em contato.

---

**Sistema pronto para uso! 🎉**

Para iniciar, acesse: `http://localhost/Proj_Carrinho_Praia/public/login.php`
