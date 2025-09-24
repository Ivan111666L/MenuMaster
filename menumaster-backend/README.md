# MenuMaster Backend

A comprehensive restaurant management system backend built with PHP, providing APIs for order management, inventory control, user authentication, and business analytics.

## Features

- **User Authentication & Authorization**: JWT-based authentication with role-based access control
- **Order Management**: Complete order lifecycle management with real-time status updates
- **Inventory Control**: Ingredient tracking, stock management, and automated inventory updates
- **Menu Management**: Dynamic menu creation with combo support and daily specials
- **Table Management**: Restaurant table status and reservation system
- **Payment Processing**: Multiple payment method support with transaction tracking
- **Business Analytics**: Sales reports, profitability analysis, and performance metrics
- **Printer Integration**: Thermal printer support for order receipts
- **Advanced Analysis**: Cost analysis, profitability tracking, and business intelligence

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Authentication**: JWT (JSON Web Tokens)
- **Architecture**: MVC Pattern with RESTful APIs
- **Dependencies**: Composer for dependency management
- **Environment**: XAMPP/LAMP stack compatible

## Project Structure

```
menumaster-backend/
├── App/
│   ├── config/           # Database and application configuration
│   ├── Controllers/      # API controllers
│   ├── Middleware/       # Authentication and request middleware
│   ├── models/          # Data models and database interactions
│   ├── routes/          # API route definitions
│   └── Utils/           # Utility classes and helpers
├── database/            # Database schema and migrations
├── public/              # Public web directory
├── tests/               # Unit and integration tests
├── vendor/              # Composer dependencies
├── .env.example         # Environment variables template
├── composer.json        # PHP dependencies
└── setup_analisis_avanzado.php  # Advanced analysis setup script
```

## Installation

### Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd menumaster-backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` file with your database credentials:
   ```
   DB_HOST=localhost
   DB_NAME=menumaster
   DB_USER=your_username
   DB_PASS=your_password
   JWT_SECRET=your_jwt_secret_key
   ```

4. **Database setup**
   - Create a MySQL database named `menumaster`
   - Import the database schema from `database/` directory
   - Run the advanced analysis setup:
     ```bash
     php setup_analisis_avanzado.php
     ```

5. **Web server configuration**
   - Point your web server document root to the `public/` directory
   - Ensure URL rewriting is enabled
   - The `.htaccess` file in `public/` handles routing

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/verify` - Token verification

### Orders (Pedidos)
- `GET /api/pedidos` - List all orders
- `POST /api/pedidos` - Create new order
- `GET /api/pedidos/{id}` - Get order details
- `PUT /api/pedidos/{id}` - Update order
- `POST /api/pedidos/{id}/facturar` - Invoice order

### Products (Productos)
- `GET /api/productos` - List all products
- `POST /api/productos` - Create new product
- `GET /api/productos/{id}` - Get product details
- `PUT /api/productos/{id}` - Update product
- `DELETE /api/productos/{id}` - Delete product

### Inventory (Inventario)
- `GET /api/inventario` - List inventory items
- `POST /api/inventario` - Add inventory item
- `PUT /api/inventario/{id}` - Update inventory
- `POST /api/movimientos-inventario` - Record inventory movement

### Tables (Mesas)
- `GET /api/mesas` - List all tables
- `POST /api/mesas` - Create new table
- `GET /api/mesas/disponibles` - Get available tables
- `POST /api/mesas/reset` - Reset all tables

### Users (Usuarios)
- `GET /api/usuarios` - List users (admin only)
- `POST /api/usuarios` - Create user (admin only)
- `GET /api/usuarios/perfil` - Get user profile
- `PUT /api/usuarios/{id}` - Update user

### Analytics
- `GET /api/analisis/ventas` - Sales analysis
- `GET /api/analisis/productos` - Product performance
- `GET /api/analisis/rentabilidad` - Profitability analysis
- `GET /api/cuadre_diario` - Daily reconciliation

## Authentication

The API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your-jwt-token>
```

### User Roles

- **Administrador**: Full system access
- **Gerente**: Management operations
- **Mesero**: Order and table management
- **Cajero**: Payment processing

## Testing

Run the unit tests:

```bash
php tests/unit/DatabaseConnectionTest.php
php tests/unit/AuthTest.php
php tests/unit/PermisosTest.php
php tests/unit/ProductTest.php
```

## Security Features

- JWT-based authentication
- Role-based access control
- SQL injection prevention with prepared statements
- XSS protection headers
- CORS configuration
- Input validation and sanitization
- Secure password hashing

## Performance Features

- Database connection pooling
- Optimized SQL queries
- Response caching headers
- Gzip compression
- Efficient error handling

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is proprietary software. All rights reserved.

## Support

For support and questions, please contact the development team.

## Changelog

### Version 1.0.0
- Initial release with core functionality
- User authentication and authorization
- Order management system
- Inventory control
- Basic reporting features

### Version 1.1.0
- Advanced analytics module
- Profitability tracking
- Enhanced reporting
- Performance optimizations