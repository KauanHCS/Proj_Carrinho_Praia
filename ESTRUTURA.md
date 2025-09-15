# 📁 Estrutura Organizada do Projeto - Carrinho de Praia

## 🎯 Reorganização Concluída

A estrutura do projeto foi reorganizada seguindo padrões modernos de desenvolvimento web para melhor manutenibilidade e organização.

## 📂 Nova Estrutura de Pastas

```
Proj_Carrinho_Praia/
│
├── 📁 assets/                    # Arquivos estáticos (CSS, JS, imagens)
│   ├── css/                     # Folhas de estilo
│   │   └── style.css
│   └── js/                      # Scripts JavaScript
│       ├── main.js              # Script principal
│       ├── produtos-actions.js   # Ações de produtos
│       ├── validation.js         # Validações
│       └── filtro-simple.js     # Filtros
│
├── 📁 database/                 # Configuração e scripts de banco
│   ├── database.php            # Configuração da conexão
│   └── init_database.sql       # Script de inicialização
│
├── 📁 docs/                     # Documentação
│   ├── README.md               # Documentação principal
│   └── WARP.md                 # Documentação WARP
│
├── 📁 public/                   # Arquivos públicos (point de entrada)
│   ├── index.php               # Página principal
│   ├── login.php               # Página de login
│   └── uploads/                # Uploads de usuários
│
├── 📁 scripts/                  # Scripts de manutenção e backup
│   ├── add_products.php
│   ├── backup_system.php
│   ├── update_database.php
│   └── ...outros scripts
│
├── 📁 src/                      # Código-fonte da aplicação
│   ├── controllers/             # Controladores da aplicação
│   │   ├── actions.php         # Controlador principal
│   │   ├── backup_export.php   # Exportação e backup
│   │   └── export_data.php     # Exportação de dados
│   ├── models/                 # Modelos (futuro)
│   └── views/                  # Templates/Views
│       ├── vendas.php
│       ├── produtos.php
│       ├── estoque.php
│       ├── relatorios.php
│       ├── localizacao.php
│       └── modais.php
│
├── .htaccess                   # Configuração Apache (redirecionamento)
└── .gitattributes             # Configuração Git

```

## 🔄 Mudanças Realizadas

### ✅ Movimentações Realizadas:

1. **Assets (CSS/JS)**
   - `css/` → `assets/css/`
   - `js/` → `assets/js/`

2. **Configuração e Database**
   - `config/` → `database/`

3. **Views/Templates**
   - `templates/` → `src/views/`

4. **Controllers**
   - `actions.php` → `src/controllers/actions.php`
   - `utils/` → `src/controllers/`

5. **Arquivos Públicos**
   - `index.php` → `public/index.php`
   - `login.php` → `public/login.php`

6. **Scripts de Manutenção**
   - Arquivos de update, backup, etc → `scripts/`

7. **Documentação**
   - `*.md` → `docs/`

### 🔧 Atualizações de Referências:

- ✅ Todos os caminhos de CSS e JS atualizados
- ✅ Todas as referências PHP (includes/requires) atualizadas
- ✅ Todos os fetch() JavaScript atualizados
- ✅ Caminhos de exportação e backup corrigidos

## 🌐 Como Acessar

### Opção 1: Com .htaccess (Recomendado)
- Acesse: `http://localhost/Proj_Carrinho_Praia/`
- O `.htaccess` redirecionará automaticamente para `public/`

### Opção 2: Acesso Direto
- Acesse: `http://localhost/Proj_Carrinho_Praia/public/`

## 📋 Benefícios da Nova Estrutura

1. **🎯 Organização Clara**: Separação lógica entre arquivos públicos e código-fonte
2. **🔒 Segurança**: Arquivos sensíveis fora do diretório público
3. **🚀 Performance**: Estrutura otimizada para cache e CDN
4. **🛠 Manutenibilidade**: Código mais fácil de encontrar e modificar
5. **📈 Escalabilidade**: Preparado para crescimento do projeto
6. **🎨 Padrões**: Segue convenções modernas de desenvolvimento web

## 🔍 Arquivos Importantes

- **Ponto de Entrada**: `public/index.php`
- **Controlador Principal**: `src/controllers/actions.php`
- **Configuração DB**: `database/database.php`
- **Scripts de Manutenção**: `scripts/`
- **Assets Estáticos**: `assets/`

## 📝 Próximos Passos (Sugestões)

1. Implementar autoloading para classes PHP
2. Adicionar sistema de rotas mais robusto
3. Separar lógica de negócio em models
4. Implementar sistema de cache
5. Adicionar testes automatizados

---
**✨ Projeto reorganizado em:** `2025-09-15` por **Warp AI Assistant**