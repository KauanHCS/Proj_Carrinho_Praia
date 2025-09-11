# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a TCC (undergraduate thesis) project developing a web-based sales management application for street vendors (specifically beach cart vendors). The system is built with PHP, MySQL, JavaScript, and Bootstrap, featuring modern authentication including Google Sign-In integration.

## Architecture

### Backend Structure
- **PHP MVC Pattern**: The application follows a simple MVC-like structure
  - `index.php`: Main application entry point with tab-based navigation
  - `login.php`: Authentication interface with dual login/register forms
  - `actions.php`: Central API controller handling all backend operations via AJAX
  - `config/database.php`: Database connection and configuration
  - `templates/`: View components for each module (vendas, produtos, estoque, relatorios, localizacao)

### Frontend Structure
- **Single Page Application**: Tab-based navigation system without page reloads
- **Bootstrap 5**: UI framework with custom CSS styling
- **Vanilla JavaScript**: No external JS frameworks, all interactions via `js/main.js`
- **Google Maps Integration**: Location tracking for vendors
- **Chart.js**: Sales analytics and reporting

### Database Design
- **MySQL Database**: `sistema_carrinho` 
- **Key Tables**: 
  - `usuarios`: User management with Google OAuth support
  - `produtos`: Product catalog with stock management
  - `vendas` & `itens_venda`: Sales transactions and line items
  - `movimentacoes`: Inventory movement tracking

## Development Commands

### Local Development Server
```powershell
# Using PHP built-in server
php -S localhost:8000

# Or using XAMPP/WAMP (place project in htdocs/www)
# Access via: http://localhost/Proj_Carrinho_Praia/
```

### Database Setup
```sql
# Initialize database with proper schema
mysql -u root -p < config/init_database.sql

# Or manually:
# 1. Create database: CREATE DATABASE sistema_carrinho;
# 2. Run the initialization script in config/init_database.sql
```

### Data Export and Backup
```powershell
# Export sales data (via web interface)
# Navigate to Relatórios tab -> Export section

# Or direct URL access:
# http://localhost:8000/utils/backup_export.php?action=export_sales
# http://localhost:8000/utils/backup_export.php?action=export_products  
# http://localhost:8000/utils/backup_export.php?action=backup_database
```

### File Watching/Live Reload
```powershell
# For CSS/JS changes, use browser refresh
# No build process required - direct file editing
```

## Key Development Patterns

### AJAX API Pattern
All backend interactions use the `actions.php` endpoint:
```javascript
// POST requests for mutations
fetch('actions.php', {
    method: 'POST',
    body: formData // FormData object with 'action' parameter
})

// GET requests for queries  
fetch('actions.php?action=get_produto&id=123')
```

### Authentication Flow
- **Local Auth**: Email/password stored in `usuarios` table (plain text - development only)
- **Google OAuth**: Uses Google Identity Services with client ID integration
- **Session Management**: Browser sessionStorage for user state persistence

### Frontend State Management
- Cart state managed in `js/main.js` global variables with localStorage persistence
- Cart data survives page reloads and browser sessions
- Real-time stock alerts via periodic API polling
- Enhanced notification system with loading states
- Input validation via `js/validation.js` utility class

## Common Development Tasks

### Adding New Product Categories
1. Update the select options in `templates/modais.php` (modalNovoProduto)
2. Modify the database enum if using constraints
3. Update any filtering logic in `templates/vendas.php`

### Extending API Endpoints
1. Add new case in `actions.php` switch statement
2. Create corresponding function following existing patterns
3. Use `jsonResponse()` helper for consistent API responses

### Database Schema Changes
1. Update table structure manually in MySQL
2. Modify PHP queries in `actions.php` 
3. Update form fields in corresponding template files

### Google Maps Customization
1. Replace API key in `index.php` (line 277)
2. Modify map initialization in `initMap()` function
3. Location coordinates default to São Paulo (-23.550520, -46.633308)

## Configuration

### Database Connection
Edit `config/database.php`:
```php
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "sistema_carrinho";
```

### Google OAuth Setup
Update client ID in `login.php`:
```javascript
const GOOGLE_CLIENT_ID = 'your-client-id.apps.googleusercontent.com';
```

### Google Maps API
Update API key in `index.php`:
```javascript
// Line 277
src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=visualization,marker&callback=loadGoogleMaps"
```

## Security Considerations

✅ **Security Features Implemented**:
- Password hashing with PHP `password_hash()` and `password_verify()`
- Input sanitization on both frontend and backend
- SQL injection protection via prepared statements
- XSS prevention through HTML encoding
- Form validation with comprehensive error handling

⚠️ **Still Needs for Production**:
- CSRF protection tokens
- API rate limiting
- HTTPS enforcement
- Session security headers

## Testing

### Manual Testing Workflow
1. Start local server: `php -S localhost:8000`
2. Access login page: http://localhost:8000/login.php
3. Test authentication (local and Google)
4. Navigate through tabs testing CRUD operations
5. Test sales flow: add products → checkout → payment

### Database Testing
```sql
-- Verify tables exist
SHOW TABLES;

-- Check sample data
SELECT * FROM produtos LIMIT 5;
SELECT * FROM vendas ORDER BY data DESC LIMIT 10;
```

## File Structure Navigation

```
Proj_Carrinho_Praia/
├── index.php           # Main app interface
├── login.php           # Authentication page  
├── actions.php         # API controller
├── config/
│   ├── database.php    # DB connection
│   └── init_database.sql # DB schema initialization
├── css/
│   └── style.css       # Custom styles
├── js/
│   ├── main.js         # Frontend logic
│   └── validation.js   # Input validation utilities
├── utils/
│   └── backup_export.php # Data export and backup
└── templates/          # View components
    ├── vendas.php      # Sales interface with search/filter
    ├── produtos.php    # Product management
    ├── estoque.php     # Inventory alerts
    ├── relatorios.php  # Analytics + export controls
    ├── localizacao.php # Maps integration
    └── modais.php      # Modal dialogs
```

## Browser Compatibility

- **Primary Target**: Modern Chrome/Edge (development focus)
- **Bootstrap 5 Support**: IE11+ required
- **Google APIs**: Modern browser with JavaScript enabled
- **Mobile Responsive**: Bootstrap grid system with custom breakpoints
