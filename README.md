# Client Finance Manager

A custom MVC CRUD system in vanilla PHP for managing clients and their financial movements (expenses/earnings), with administrator authentication and REST API.

## Project Structure

```
client-finance-manager/
│
├── app/
│   ├── Controllers/
│   │   ├── API/
│   │   │   ├── AuthAPIController.php
│   │   │   ├── BaseAPIController.php
│   │   │   └── MovementAPIController.php
│   │   ├── AuthController.php
│   │   ├── ClientController.php
│   │   ├── HomeController.php
│   │   └── MovementController.php
│   │
│   ├── Core/
│   │   ├── BaseController.php
│   │   ├── BaseModel.php
│   │   └── Database.php
│   │
│   ├── Models/
│   │   ├── Administrator.php
│   │   ├── Client.php
│   │   └── Movement.php
│   │
│   └── Views/
│       ├── auth/
│       ├── clients/
│       ├── home/
│       └── movements/
│
├── config/
│   └── app.php               # Unified configuration
│
├── database/
│   └── schema.sql            # Database schema
│
├── public/
│   ├── api.php              # API entry point
│   ├── index.php            # Main application entry
│   └── .htaccess            # URL rewriting
│
├── .env.example             # Environment template
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

## Features

- **Administrator Authentication** - Secure login/logout system
- **Client Management** - Full CRUD operations for clients
- **Financial Movements** - Track expenses and earnings per client
- **Dashboard** - Overview with financial summaries
- **Time Range Reports** - Filter movements by date
- **Professional REST API** - Token-based authentication for external access

## Setup

### Local Development

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd client-finance-manager
   ```

2. **Setup environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database settings
   ```

3. **Create database**
   ```bash
   # Import the schema
   mysql -u root -p < database/schema.sql
   ```

4. **Configure environment variables**
   ```env
   # .env
   DB_HOST=localhost
   DB_NAME=client_finance_manager
   DB_USER=root
   DB_PASS=your_password
   ```

5. **Start development server**
   ```bash
   php -S localhost:8000 -t public
   ```

6. **Access application**
    - Web: http://localhost:8000
    - API: http://localhost:8000/api

### Production (Heroku)

1. **Add JawsDB addon**
   ```bash
   heroku addons:create jawsdb:kitefin
   ```

2. **Deploy**
   ```bash
   git push heroku main
   ```

3. **Setup database**
    - Run `database/schema.sql` on production database

## Database Schema

```sql
administrators (id, username, password, email, created_at, updated_at)
clients (id, name, email, phone, address, created_at, updated_at)
movements (id, client_id, type, amount, description, date, created_by, created_at)
api_tokens (id, administrator_id, token_hash, expires_at, created_at, ...)
```

## Web Interface

**Default Login:** `admin` / `admin123`

- Client management (create, read, update, delete)
- Movement tracking for each client
- Financial dashboard with summaries
- Date range filtering and reports

## API Usage

### Authentication

```bash
# Login
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Response
{
  "success": true,
  "data": {
    "access_token": "your_token_here",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

### Get User Movements

```bash
# Get movements for specific client
curl "http://localhost:8000/api/movements?client_id=1" \
  -H "Authorization: Bearer your_token_here"

# With filters
curl "http://localhost:8000/api/movements?client_id=1&type=income&start_date=2025-01-01" \
  -H "Authorization: Bearer your_token_here"
```

### API Endpoints

- `POST /api/auth/login` - Get access token
- `POST /api/auth/logout` - Revoke token
- `GET /api/auth/me` - Get current user info
- `GET /api/movements?client_id=X` - Get user movements
- `GET /api` - API documentation

## Technology Stack

- **PHP 8.0+** - Custom MVC framework
- **MySQL** - Database with foreign key relationships
- **Vanilla JavaScript** - Frontend interactions
- **No external frameworks** - Pure PHP implementation

## Security Features

- Session-based authentication (web)
- Token-based authentication (API)
- Password hashing (bcrypt)
- SQL injection protection
- CSRF protection
- XSS prevention

