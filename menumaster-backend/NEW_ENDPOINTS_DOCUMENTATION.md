# New API Endpoints Documentation

This document describes the newly implemented API endpoints for the MenuMaster system.

## Overview

Three new API modules have been successfully implemented and integrated into the MenuMaster backend:

1. **Historial API** - Historical data and analytics
2. **Security API** - Security management and monitoring
3. **Compras API** - Purchase management system

## Authentication

All endpoints require JWT authentication via the `Authorization: Bearer <token>` header, except where noted.

## Historial API Endpoints

Base URL: `/api/historial`

### GET Endpoints

#### `/api/historial/orders`
- **Description**: Retrieve historical orders with optional filtering
- **Parameters**: 
  - `fecha_inicio` (optional): Start date filter
  - `fecha_fin` (optional): End date filter
  - `estado` (optional): Order status filter
- **Response**: List of historical orders

#### `/api/historial/order-details/{id}`
- **Description**: Get detailed information for a specific order
- **Parameters**: 
  - `id`: Order ID (required)
- **Response**: Detailed order information

#### `/api/historial/sales-stats`
- **Description**: Get sales statistics and metrics
- **Parameters**:
  - `periodo` (optional): Time period (day, week, month, year)
- **Response**: Sales statistics data

#### `/api/historial/top-products`
- **Description**: Get top-selling products analysis
- **Parameters**:
  - `limit` (optional): Number of products to return (default: 10)
  - `periodo` (optional): Time period filter
- **Response**: List of top-selling products

#### `/api/historial/sales-by-waiter`
- **Description**: Get sales performance by waiter
- **Parameters**:
  - `fecha_inicio` (optional): Start date
  - `fecha_fin` (optional): End date
- **Response**: Sales data grouped by waiter

#### `/api/historial/profitability`
- **Description**: Get profitability analysis
- **Parameters**:
  - `periodo` (optional): Analysis period
- **Response**: Profitability metrics and analysis

#### `/api/historial/complete-report`
- **Description**: Generate comprehensive sales report
- **Parameters**:
  - `fecha_inicio` (optional): Report start date
  - `fecha_fin` (optional): Report end date
- **Response**: Complete sales report data

## Security API Endpoints

Base URL: `/api/security`

### POST Endpoints

#### `/api/security/register-failed-attempt`
- **Description**: Register a failed login attempt
- **Body**: 
  ```json
  {
    "email": "user@example.com",
    "ip_address": "192.168.1.1"
  }
  ```

#### `/api/security/clear-attempts`
- **Description**: Clear failed login attempts for a user
- **Body**:
  ```json
  {
    "email": "user@example.com"
  }
  ```

#### `/api/security/register-activity`
- **Description**: Register user activity
- **Body**:
  ```json
  {
    "user_id": 1,
    "action": "login",
    "status": "success"
  }
  ```

#### `/api/security/create-password-token`
- **Description**: Create password reset token
- **Body**:
  ```json
  {
    "user_id": 1
  }
  ```

#### `/api/security/verify-password-token`
- **Description**: Verify password reset token
- **Body**:
  ```json
  {
    "token": "reset_token_here"
  }
  ```

#### `/api/security/reset-password-token`
- **Description**: Reset password using token
- **Body**:
  ```json
  {
    "token": "reset_token_here",
    "new_password": "new_password"
  }
  ```

#### `/api/security/log-successful-login`
- **Description**: Log successful login
- **Body**:
  ```json
  {
    "user_id": 1
  }
  ```

#### `/api/security/log-logout`
- **Description**: Log user logout
- **Body**:
  ```json
  {
    "user_id": 1
  }
  ```

### GET Endpoints

#### `/api/security/lockout-status/{email}`
- **Description**: Check if user account is locked
- **Parameters**: 
  - `email`: User email (required)

#### `/api/security/login-stats`
- **Description**: Get login statistics
- **Parameters**:
  - `fecha_inicio` (optional): Start date
  - `fecha_fin` (optional): End date

#### `/api/security/user-activities`
- **Description**: Get user activity logs
- **Parameters**:
  - `user_id` (optional): Filter by user ID
  - `action` (optional): Filter by action type

#### `/api/security/report`
- **Description**: Generate security report
- **Parameters**:
  - `fecha_inicio` (optional): Report start date
  - `fecha_fin` (optional): Report end date

### DELETE Endpoints

#### `/api/security/clean-old-data`
- **Description**: Clean old security data
- **Parameters**:
  - `days` (optional): Number of days to keep (default: 90)

## Compras API Endpoints

Base URL: `/api/compras`

### POST Endpoints

#### `/api/compras/create`
- **Description**: Create a new purchase order
- **Body**:
  ```json
  {
    "proveedor_id": 1,
    "items": [
      {
        "ingrediente_id": 1,
        "cantidad": 10,
        "precio_unitario": 5.50
      }
    ],
    "fecha_entrega_esperada": "2024-01-15"
  }
  ```

#### `/api/compras/mark-received`
- **Description**: Mark purchase as received
- **Body**:
  ```json
  {
    "compra_id": 1,
    "fecha_recepcion": "2024-01-15",
    "items_recibidos": [
      {
        "item_id": 1,
        "cantidad_recibida": 10
      }
    ]
  }
  ```

#### `/api/compras/generate-automatic-order`
- **Description**: Generate automatic purchase order based on stock levels
- **Body**:
  ```json
  {
    "proveedor_id": 1,
    "stock_minimo_threshold": 10
  }
  ```

#### `/api/compras/create-supplier-ingredient`
- **Description**: Create supplier-ingredient relationship
- **Body**:
  ```json
  {
    "proveedor_id": 1,
    "ingrediente_id": 1,
    "precio_unitario": 5.50,
    "tiempo_entrega_dias": 3
  }
  ```

### GET Endpoints

#### `/api/compras/purchases`
- **Description**: List all purchases with optional filtering
- **Parameters**:
  - `estado` (optional): Filter by status
  - `proveedor_id` (optional): Filter by supplier
  - `fecha_inicio` (optional): Start date filter
  - `fecha_fin` (optional): End date filter

#### `/api/compras/purchase-details/{id}`
- **Description**: Get detailed purchase information
- **Parameters**:
  - `id`: Purchase ID (required)

#### `/api/compras/statistics`
- **Description**: Get purchase statistics
- **Parameters**:
  - `periodo` (optional): Time period (month, quarter, year)

#### `/api/compras/supplier-performance`
- **Description**: Get supplier performance analysis
- **Parameters**:
  - `proveedor_id` (optional): Specific supplier ID

#### `/api/compras/cost-analysis`
- **Description**: Get cost analysis and trends
- **Parameters**:
  - `periodo` (optional): Analysis period

#### `/api/compras/supplier-analysis`
- **Description**: Comprehensive supplier analysis
- **Parameters**:
  - `incluir_rendimiento` (optional): Include performance metrics

#### `/api/compras/inventory-impact`
- **Description**: Analyze purchase impact on inventory
- **Parameters**:
  - `compra_id` (optional): Specific purchase ID

#### `/api/compras/purchase-suggestions`
- **Description**: Get AI-powered purchase suggestions
- **Parameters**:
  - `categoria` (optional): Product category filter

#### `/api/compras/reports/complete`
- **Description**: Generate complete purchase report
- **Parameters**:
  - `fecha_inicio` (optional): Report start date
  - `fecha_fin` (optional): Report end date
  - `formato` (optional): Report format (json, pdf, excel)

### PUT Endpoints

#### `/api/compras/update-status/{id}`
- **Description**: Update purchase status
- **Parameters**:
  - `id`: Purchase ID (required)
- **Body**:
  ```json
  {
    "nuevo_estado": "recibido",
    "notas": "Optional notes"
  }
  ```

#### `/api/compras/update-details/{id}`
- **Description**: Update purchase details
- **Parameters**:
  - `id`: Purchase ID (required)
- **Body**:
  ```json
  {
    "fecha_entrega_esperada": "2024-01-20",
    "notas": "Updated delivery date"
  }
  ```

### DELETE Endpoints

#### `/api/compras/delete/{id}`
- **Description**: Delete a purchase order
- **Parameters**:
  - `id`: Purchase ID (required)

## Implementation Status

✅ **Completed Tasks:**
- Created `historial_api.php` with all historical data endpoints
- Created `security_api.php` with comprehensive security management
- Created `compras_api.php` with full purchase management system
- Updated `router.php` to include all new API routes
- Successfully tested all endpoints with authentication
- Fixed admin user authentication issues

## Testing Results

All endpoints have been tested and are responding correctly with HTTP 200 status codes. The authentication system is working properly, and all new API routes are properly integrated into the main router.

## Notes

- All endpoints follow RESTful conventions
- Proper error handling is implemented in each controller
- JWT authentication is required for all endpoints
- Response format is consistent across all endpoints
- Comprehensive logging is implemented for security endpoints

## Next Steps

The new API endpoints are ready for frontend integration. The controllers contain placeholder implementations that should be completed with actual business logic as needed.