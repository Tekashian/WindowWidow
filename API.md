# API Documentation - Window Factory

Complete API reference for the Window Factory Production Management System.

**Base URL:** `https://api.windowfactory.com/api`  
**Version:** 1.0.0  
**Authentication:** Bearer Token (Laravel Sanctum)

## Authentication

### Login
Authenticate a user and receive an access token.

**Endpoint:** `POST /login`

**Request Body:**
```json
{
  "email": "admin@okna.pl",
  "password": "admin123"
}
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@okna.pl",
    "role": "admin",
    "is_active": true
  },
  "token": "1|abc123...xyz789"
}
```

**Error Responses:**
- `401 Unauthorized` - Invalid credentials
- `403 Forbidden` - Account inactive

### Logout
Revoke the current access token.

**Endpoint:** `POST /logout`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "message": "Successfully logged out"
}
```

### Get Current User
Retrieve information about the authenticated user.

**Endpoint:** `GET /me`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "Admin",
  "email": "admin@okna.pl",
  "role": "admin",
  "is_active": true
}
```

## Dashboard

### Get Statistics
Retrieve dashboard statistics and metrics.

**Endpoint:** `GET /dashboard`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "production_orders": {
    "total": 150,
    "nowe": 12,
    "w_trakcie": 45,
    "zakonczone": 5
  },
  "windows": {
    "total": 320,
    "projekt": 45,
    "w_produkcji": 120,
    "gotowe": 85,
    "wydane": 70
  },
  "materials": {
    "total_value": 125000.50,
    "low_stock_count": 8,
    "by_type": {
      "profil": 45000.00,
      "szklo": 60000.00,
      "okucia": 20000.50
    }
  },
  "low_stock_alerts": [
    {
      "id": 5,
      "name": "Profil PVC VEKA 70mm biały",
      "type": "profil",
      "unit": "m",
      "current_stock": 45.00,
      "min_stock": 100.00,
      "price_per_unit": 45.50
    }
  ]
}
```

### Export Materials
Export materials inventory as CSV.

**Endpoint:** `GET /dashboard/export-materials`  
**Headers:** `Authorization: Bearer {token}`

**Response:** CSV file download

## Windows Management

### List All Windows
Retrieve a list of all window products.

**Endpoint:** `GET /windows`  
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `status` (optional) - Filter by status: `projekt`, `w_produkcji`, `gotowe`, `wydane`
- `type` (optional) - Filter by type
- `per_page` (optional, default: 15) - Items per page

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Okno uchylno-rozwierane 1200x1400",
      "type": "uchylno-rozwierane",
      "width": 1200,
      "height": 1400,
      "profile_id": 1,
      "glass_id": 1,
      "color": "biały",
      "price": 890.00,
      "status": "projekt",
      "description": "Standardowe okno PVC",
      "profile": {
        "id": 1,
        "name": "VEKA Softline 70",
        "manufacturer": "VEKA"
      },
      "glass": {
        "id": 1,
        "name": "Dwuszybowe 4/16/4",
        "type": "dwuszybowe"
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

### Get Single Window
Retrieve details of a specific window.

**Endpoint:** `GET /windows/{id}`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "Okno uchylno-rozwierane 1200x1400",
  "type": "uchylno-rozwierane",
  "width": 1200,
  "height": 1400,
  "profile_id": 1,
  "glass_id": 1,
  "color": "biały",
  "price": 890.00,
  "status": "projekt",
  "description": "Standardowe okno PVC",
  "created_at": "2026-01-02T10:00:00Z",
  "updated_at": "2026-01-02T10:00:00Z"
}
```

### Create Window
Create a new window product.

**Endpoint:** `POST /windows`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Okno balkonowe 900x2400",
  "type": "balkonowe",
  "width": 900,
  "height": 2400,
  "profile_id": 1,
  "glass_id": 2,
  "color": "biały",
  "price": 1250.00,
  "status": "projekt",
  "description": "Okno balkonowe z niskim progiem"
}
```

**Response (201 Created):**
```json
{
  "id": 25,
  "name": "Okno balkonowe 900x2400",
  ...
}
```

**Validation Errors (422 Unprocessable Entity):**
```json
{
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "width": ["The width must be a number."]
  }
}
```

### Update Window
Update an existing window.

**Endpoint:** `PUT /windows/{id}`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:** (All fields optional)
```json
{
  "price": 950.00,
  "status": "w_produkcji"
}
```

**Response (200 OK):** Updated window object

### Delete Window
Delete a window product.

**Endpoint:** `DELETE /windows/{id}`  
**Headers:** `Authorization: Bearer {token}`

**Response (204 No Content)**

## Profiles Management

Similar CRUD operations as Windows. Endpoints follow same pattern:
- `GET /profiles` - List all
- `GET /profiles/{id}` - Get single
- `POST /profiles` - Create
- `PUT /profiles/{id}` - Update
- `DELETE /profiles/{id}` - Delete

**Profile Object:**
```json
{
  "id": 1,
  "name": "VEKA Softline 70",
  "manufacturer": "VEKA",
  "type": "PVC",
  "material": "PVC",
  "color": "Biały",
  "price_per_meter": 45.50,
  "created_at": "2026-01-02T10:00:00Z",
  "updated_at": "2026-01-02T10:00:00Z"
}
```

## Glass Types Management

Similar CRUD operations. Endpoints:
- `GET /glasses`
- `GET /glasses/{id}`
- `POST /glasses`
- `PUT /glasses/{id}`
- `DELETE /glasses/{id}`

**Glass Object:**
```json
{
  "id": 1,
  "name": "Dwuszybowe 4/16/4",
  "type": "dwuszybowe",
  "thickness": 24,
  "u_value": 1.1,
  "price_per_sqm": 85.00,
  "description": "Standardowe szkło energooszczędne",
  "created_at": "2026-01-02T10:00:00Z",
  "updated_at": "2026-01-02T10:00:00Z"
}
```

## Materials & Inventory

### List Materials
Get all materials in warehouse.

**Endpoint:** `GET /materials`  
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `type` (optional) - Filter by type: `profil`, `szklo`, `okucia`, `uszczelki`, `inne`
- `low_stock` (optional) - Show only low stock items: `true`/`false`

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Profil PVC VEKA 70mm biały",
      "type": "profil",
      "unit": "m",
      "current_stock": 45.00,
      "min_stock": 100.00,
      "price_per_unit": 45.50,
      "supplier": "VEKA Polska",
      "is_low_stock": true,
      "total_value": 2047.50
    }
  ]
}
```

### Add Stock
Add inventory to a material.

**Endpoint:** `POST /materials/{id}/add-stock`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "quantity": 100.00,
  "reason": "Dostawa od dostawcy VEKA"
}
```

**Response (200 OK):**
```json
{
  "message": "Stock added successfully",
  "material": {
    "id": 1,
    "current_stock": 145.00
  }
}
```

### Remove Stock
Remove inventory from a material.

**Endpoint:** `POST /materials/{id}/remove-stock`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "quantity": 25.50,
  "reason": "Użyto w produkcji zlecenia #123"
}
```

**Response (200 OK):**
```json
{
  "message": "Stock removed successfully",
  "material": {
    "id": 1,
    "current_stock": 119.50
  }
}
```

### Get Stock Movements
View transaction history for a material.

**Endpoint:** `GET /materials/{id}/movements`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 45,
      "material_id": 1,
      "type": "in",
      "quantity": 100.00,
      "reason": "Dostawa od dostawcy VEKA",
      "user_id": 1,
      "user": {
        "name": "Admin"
      },
      "created_at": "2026-01-02T10:00:00Z"
    },
    {
      "id": 46,
      "material_id": 1,
      "type": "out",
      "quantity": 25.50,
      "reason": "Użyto w produkcji zlecenia #123",
      "user_id": 3,
      "user": {
        "name": "Piotr Nowak"
      },
      "created_at": "2026-01-02T14:30:00Z"
    }
  ]
}
```

### Get Low Stock Alerts
Retrieve all materials with stock below minimum.

**Endpoint:** `GET /low-stock`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 5,
      "name": "Profil PVC VEKA 70mm biały",
      "current_stock": 45.00,
      "min_stock": 100.00,
      "deficit": 55.00
    }
  ]
}
```

## Orders Management

### List Orders
Get all customer orders.

**Endpoint:** `GET /orders`  
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `status` (optional) - Filter by status
- `customer` (optional) - Filter by customer name

### Create Order
Create a new customer order.

**Endpoint:** `POST /orders`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "customer_name": "Jan Kowalski",
  "customer_email": "jan@example.com",
  "customer_phone": "+48 123 456 789",
  "delivery_address": "ul. Kwiatowa 15, 00-001 Warszawa",
  "status": "nowe",
  "items": [
    {
      "window_id": 1,
      "quantity": 3,
      "price": 890.00
    },
    {
      "window_id": 2,
      "quantity": 2,
      "price": 650.00
    }
  ]
}
```

**Response (201 Created):**
```json
{
  "id": 45,
  "customer_name": "Jan Kowalski",
  "total_amount": 3970.00,
  "status": "nowe",
  "items": [...]
}
```

### Update Order Status
Change order status.

**Endpoint:** `POST /orders/{id}/update-status`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "w_realizacji"
}
```

**Available Statuses:**
- `nowe` - New order
- `w_realizacji` - In progress
- `gotowe` - Ready
- `wydane` - Delivered
- `anulowane` - Cancelled

## Production Orders

### List Production Orders
Get all production work orders.

**Endpoint:** `GET /production-orders`  
**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `status` (optional) - Filter by status
- `assigned_to` (optional) - Filter by assigned user ID

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "order_id": 45,
      "assigned_to": 3,
      "status": "w_trakcie",
      "total_cost": 3500.00,
      "started_at": "2026-01-02T08:00:00Z",
      "completed_at": null,
      "items": [
        {
          "id": 1,
          "window_id": 1,
          "quantity": 3
        }
      ],
      "assigned_user": {
        "id": 3,
        "name": "Piotr Nowak"
      }
    }
  ]
}
```

### Create Production Order
Create a new production work order.

**Endpoint:** `POST /production-orders`  
**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "order_id": 45,
  "assigned_to": 3,
  "status": "nowe",
  "items": [
    {
      "window_id": 1,
      "quantity": 3
    }
  ]
}
```

### Start Production Order
Mark production order as started.

**Endpoint:** `POST /production-orders/{id}/start`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "id": 1,
  "status": "w_trakcie",
  "started_at": "2026-01-02T08:00:00Z"
}
```

### Complete Production Order
Mark production order as completed.

**Endpoint:** `POST /production-orders/{id}/complete`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "id": 1,
  "status": "zakonczone",
  "completed_at": "2026-01-02T16:00:00Z"
}
```

### Cancel Production Order
Cancel a production order.

**Endpoint:** `POST /production-orders/{id}/cancel`  
**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
  "id": 1,
  "status": "anulowane"
}
```

## Error Responses

All endpoints may return the following error responses:

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
  "message": "Resource not found."
}
```

### 422 Unprocessable Entity
```json
{
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### 500 Internal Server Error
```json
{
  "message": "Server error",
  "error": "Detailed error message (in development only)"
}
```

## Rate Limiting

- **Rate Limit:** 60 requests per minute per IP
- **Headers:**
  - `X-RateLimit-Limit`: Maximum requests per minute
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Unix timestamp when limit resets

**429 Too Many Requests:**
```json
{
  "message": "Too many requests. Please try again later."
}
```

## Pagination

All list endpoints support pagination:

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

**Response Structure:**
```json
{
  "data": [...],
  "links": {
    "first": "https://api.windowfactory.com/api/windows?page=1",
    "last": "https://api.windowfactory.com/api/windows?page=5",
    "prev": null,
    "next": "https://api.windowfactory.com/api/windows?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

## Webhooks (Coming Soon)

Future feature to enable real-time notifications for:
- Order status changes
- Low stock alerts
- Production order completions

---

**API Version:** 1.0.0  
**Last Updated:** January 2026  
**Support:** api-support@windowfactory.com
