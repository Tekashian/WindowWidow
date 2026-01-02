# Window Factory - Production Management System

A professional, enterprise-grade production management system for window manufacturing companies. Built with Laravel 10 (backend API) and Vue.js 3 (frontend).

## System Overview

Window Factory is a comprehensive internal management system designed to streamline production operations, inventory management, and order processing for window manufacturing facilities.

### Key Features

- **Production Dashboard** - Real-time monitoring of production metrics and analytics
- **Production Orders Management** - Complete lifecycle management of manufacturing orders
- **Inventory Control** - Advanced warehouse management with automated low-stock alerts
- **Product Catalog** - Comprehensive database of window types, profiles, and glass specifications
- **Order Management** - Customer order tracking and status management
- **User Roles & Permissions** - Role-based access control (Admin, Warehouse, Production)

## Technology Stack

### Backend (API)
- **Framework**: Laravel 10.x
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (Token-based)
- **Architecture**: RESTful API with service layer pattern

### Frontend
- **Framework**: Vue.js 3 with Composition API
- **Build Tool**: Vite
- **State Management**: Pinia
- **HTTP Client**: Axios
- **Routing**: Vue Router
- **Styling**: Custom CSS with modern design system

### Design Philosophy
- **Minimalist & Futuristic** - Clean, professional interface without distracting elements
- **Performance First** - Optimized for speed and efficiency
- **Accessibility** - WCAG compliant design patterns
- **Responsive** - Mobile-first approach

## Project Structure

```
window-factory/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/    # RESTful API controllers
│   │   │   ├── Middleware/         # Custom middleware
│   │   │   └── Requests/           # Form request validation
│   │   ├── Models/                 # Eloquent models
│   │   ├── Services/               # Business logic layer
│   │   ├── Policies/               # Authorization policies
│   │   ├── Events/                 # System events
│   │   └── Listeners/              # Event listeners
│   ├── config/                     # Configuration files
│   ├── database/
│   │   ├── migrations/             # Database schema
│   │   └── seeders/                # Sample data
│   └── routes/
│       └── api.php                 # API routes
│
└── frontend/                   # Vue.js application
    ├── src/
    │   ├── assets/                 # Global styles & assets
    │   ├── components/             # Reusable Vue components
    │   ├── views/                  # Page components
    │   ├── services/               # API service layer
    │   ├── stores/                 # Pinia state management
    │   └── router/                 # Vue Router configuration
    └── public/                     # Static assets
```

## Database Schema

### Core Tables

- **users** - System users with role-based access
- **windows** - Window products catalog
- **profiles** - Window frame profiles (PVC, Aluminum)
- **glasses** - Glass types and specifications
- **materials** - Warehouse inventory items
- **stock_movements** - Material transaction history
- **orders** - Customer orders
- **order_items** - Order line items
- **production_orders** - Manufacturing work orders
- **production_order_items** - Work order details

## API Endpoints

### Authentication
```
POST   /api/login              - User authentication
POST   /api/logout             - User logout
GET    /api/me                 - Get current user
```

### Dashboard
```
GET    /api/dashboard          - Get statistics
GET    /api/dashboard/export-materials - Export inventory CSV
```

### Resources (CRUD operations)
```
/api/windows              - Window products
/api/profiles             - Frame profiles
/api/glasses              - Glass types
/api/orders               - Customer orders
/api/materials            - Inventory items
/api/production-orders    - Production orders
```

### Special Operations
```
POST   /api/materials/{id}/add-stock     - Add inventory
POST   /api/materials/{id}/remove-stock  - Remove inventory
GET    /api/materials/{id}/movements     - Stock history
GET    /api/low-stock                    - Low stock alerts
POST   /api/production-orders/{id}/start    - Start production
POST   /api/production-orders/{id}/complete - Complete order
POST   /api/production-orders/{id}/cancel   - Cancel order
```

## Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+
- Git

### Backend Setup

1. **Clone and navigate to backend directory**
```bash
cd backend
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Configure environment**
```bash
cp .env.example .env
```

Edit `.env` file with your database credentials:
```env
DB_DATABASE=okna_produkcja
DB_USERNAME=root
DB_PASSWORD=your_password

FRONTEND_URL=http://localhost:5173
```

4. **Generate application key**
```bash
php artisan key:generate
```

5. **Run database migrations and seeders**
```bash
php artisan migrate:fresh --seed
```

6. **Start development server**
```bash
php artisan serve
```

Backend API will be available at `http://localhost:8000`

### Frontend Setup

1. **Navigate to frontend directory**
```bash
cd frontend
```

2. **Install dependencies**
```bash
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
```

Edit `.env` file:
```env
VITE_API_URL=http://localhost:8000/api
```

4. **Start development server**
```bash
npm run dev
```

Frontend application will be available at `http://localhost:5173`

## Default User Accounts

The system comes with pre-configured test accounts:

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| Admin | admin@okna.pl | admin123 | Full system access |
| Warehouse | magazyn@okna.pl | magazyn123 | Inventory management |
| Production | produkcja@okna.pl | produkcja123 | Production orders |

## Configuration

### CORS Settings
Backend CORS is configured in `config/cors.php`:
```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
'supports_credentials' => true,
```

### API Rate Limiting
Default rate limit: 60 requests per minute per IP

### Token Expiration
Authentication tokens expire after 30 days of inactivity

## Development

### Code Standards
- **PHP**: PSR-12 coding standard
- **JavaScript**: ESLint + Prettier configuration
- **Vue**: Composition API with `<script setup>`
- **CSS**: BEM methodology for component styles

### Testing
```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test
```

### Building for Production

**Backend:**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Frontend:**
```bash
npm run build
```

Production build will be in `frontend/dist/`

## Architecture Decisions

### Authentication Flow
1. User submits credentials to `/api/login`
2. Backend validates and returns JWT token via Laravel Sanctum
3. Token stored in localStorage and included in subsequent requests
4. Middleware validates token on protected routes

### State Management
- Pinia stores for global state (auth, dashboard, materials)
- Composition API for component-local state
- Axios interceptors for automatic token injection

### Error Handling
- Global error interceptor in Axios
- Automatic redirect to login on 401 responses
- User-friendly error messages
- Backend validation errors properly displayed

## Security Features

- CSRF protection on all state-changing operations
- Password hashing with bcrypt
- SQL injection prevention via Eloquent ORM
- XSS protection through Vue's template escaping
- Rate limiting on API endpoints
- Token-based authentication with automatic expiration

## Performance Optimizations

- Lazy loading of Vue components
- Database query optimization with eager loading
- API response caching where appropriate
- Asset minification and bundling
- Gzip compression enabled

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Troubleshooting

### Common Issues

**CORS errors:**
- Ensure `FRONTEND_URL` is set correctly in backend `.env`
- Verify Laravel is running on correct port

**Authentication fails:**
- Clear browser localStorage
- Check database has seeded users
- Verify Sanctum configuration

**Database connection error:**
- Check MySQL service is running
- Verify database credentials in `.env`
- Ensure database exists

**Frontend won't start:**
- Delete `node_modules` and run `npm install` again
- Check Node.js version is 18+

## Future Enhancements

- [ ] Customer-facing order portal
- [ ] Real-time notifications via WebSockets
- [ ] Advanced reporting and analytics
- [ ] Mobile application (React Native)
- [ ] Integration with ERP systems
- [ ] Automated email notifications
- [ ] Multi-language support
- [ ] Advanced user permissions

## License

Proprietary - All rights reserved

## Support

For technical support or questions, contact the development team.

---

**Version:** 1.0.0  
**Last Updated:** January 2026  
**Developed by:** Internal Development Team
