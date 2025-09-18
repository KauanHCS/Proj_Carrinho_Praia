# 📁 Estrutura do Projeto - Sistema Carrinho de Praia

## 🏗️ Arquitetura Moderna Organizada

O projeto foi reestruturado seguindo as melhores práticas de desenvolvimento moderno, mantendo 100% de compatibilidade com o sistema anterior.

## 📂 Estrutura de Diretórios

```
Proj_Carrinho_Praia/
├── 📁 backup/                  # Backups automáticos do sistema
├── 📁 config/                  # Configurações de banco de dados
│   └── database.php           # Configuração MySQL
├── 📁 docs/                    # Documentação do projeto
│   └── ESTRUTURA_PROJETO.md   # Este arquivo
├── 📁 public/                  # Arquivos públicos acessíveis pelo navegador
│   ├── 📁 assets/             # Assets estáticos
│   │   ├── 📁 css/            # Arquivos de estilo
│   │   │   └── style.css      # CSS principal
│   │   └── 📁 js/             # Scripts JavaScript
│   │       ├── main.js        # Script principal
│   │       ├── produtos-actions.js  # Actions de produtos
│   │       └── validation.js  # Validações client-side
│   ├── index.php              # Página principal do sistema
│   ├── login.php              # Página de login/cadastro
│   └── add_products.php       # Adicionar produtos
├── 📁 scripts/                 # Scripts de manutenção
│   ├── 📁 database/           # Scripts de banco de dados
│   │   └── init_database.sql  # Schema inicial
│   └── 📁 maintenance/        # Scripts de manutenção
│       ├── backup_system.php  # Sistema de backup
│       ├── export_data.php    # Exportação de dados
│       └── atualizacoes.php   # Atualizações do sistema
├── 📁 src/                     # Código fonte principal
│   ├── 📁 Classes/            # Classes POO do sistema
│   │   ├── Database.php       # Conexão singleton MySQL
│   │   ├── User.php           # Autenticação e usuários
│   │   ├── Product.php        # CRUD de produtos
│   │   ├── Sale.php           # Sistema de vendas
│   │   ├── Stock.php          # Controle de estoque
│   │   └── Report.php         # Relatórios e dashboard
│   ├── 📁 Controllers/        # Controladores MVC
│   │   └── actions.php        # Controller principal (ex-actions.php)
│   └── 📁 Views/              # Templates e views
│       ├── vendas.php         # Interface de vendas
│       ├── produtos.php       # Gerenciamento de produtos
│       ├── estoque.php        # Controle de estoque
│       ├── relatorios.php     # Dashboard e relatórios
│       ├── localizacao.php    # Mapa e localização
│       └── modais.php         # Modais do sistema
├── 📁 tests/                   # Testes automatizados (futuro)
├── autoload.php               # Autoloader PSR-4 atualizado
├── bootstrap.php              # Inicializador moderno
└── README.md                  # Documentação principal
```

## 🔄 Mapeamento da Migração

### Antes (Estrutura Antiga)
```
classes/        → src/Classes/
templates/      → src/Views/
css/           → public/assets/css/
js/            → public/assets/js/
actions.php    → src/Controllers/actions.php
index.php      → public/index.php
login.php      → public/login.php
```

## 🎯 Benefícios da Nova Estrutura

### 1. **Separação de Responsabilidades**
- **public/**: Único diretório acessível pelo navegador
- **src/**: Código fonte protegido
- **config/**: Configurações centralizadas
- **scripts/**: Ferramentas de manutenção

### 2. **Segurança Aprimorada**
- Classes PHP inacessíveis diretamente
- Configurações protegidas
- Assets organizados

### 3. **Manutenibilidade**
- Código POO bem estruturado
- Autoloader PSR-4 moderno
- Bootstrap para inicialização

### 4. **Escalabilidade**
- Estrutura preparada para crescimento
- Pasta de testes reservada
- Documentação organizada

## 🚀 Como Usar a Nova Estrutura

### Acessar o Sistema
```
http://localhost/Proj_Carrinho_Praia/public/
```

### Desenvolvimento
- **Classes**: Adicione novas classes em `src/Classes/`
- **Views**: Templates em `src/Views/`
- **Assets**: CSS/JS em `public/assets/`
- **Controllers**: Lógica em `src/Controllers/`

### Caminhos de Include
```php
// Usar constantes definidas no autoloader
require_once PROJECT_ROOT . '/config/database.php';
include VIEWS_PATH . '/exemplo.php';
```

## 📋 Checklist de Migração Completa

- ✅ Estrutura de pastas criada
- ✅ Classes POO movidas para `src/Classes/`
- ✅ Views organizadas em `src/Views/`
- ✅ Assets CSS/JS em `public/assets/`
- ✅ Controllers em `src/Controllers/`
- ✅ Scripts organizados em `scripts/`
- ✅ Autoloader atualizado
- ✅ Bootstrap moderno criado
- ✅ Caminhos atualizados nos arquivos
- ✅ Compatibilidade 100% preservada

## 🔧 Configuração do Servidor Web

### Apache (.htaccess recomendado)
```apache
# Em public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 📈 Próximos Passos

1. **Implementar Testes**: Criar testes em `tests/`
2. **CI/CD**: Configurar pipeline de deploy
3. **Monitoramento**: Logs e métricas
4. **Performance**: Otimizações adicionais

---

**Migração realizada com sucesso! 🎉**  
*Sistema Carrinho de Praia - Versão POO Organizada*