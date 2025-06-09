# Client Finance Manager

A custom PHP MVC application for managing clients and their financial movements (expenses/earnings), with secure admin authentication and a professional REST API.

---

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
│   ├── Core/
│   ├── Models/
│   └── Views/
├── config/
│   └── app.php
├── database/
│   └── schema.sql
├── public/
│   ├── api.php
│   ├── index.php
│   └── .htaccess
├── tests/
│   ├── Controllers/
│   │   └── API/
│   │       └── AuthAPIControllerTest.php
│   └── Models/
│       ├── ClientTest.php
│       └── MovementTest.php
├── .gitignore
├── composer.json
├── composer.lock
├── phpunit.xml
└── README.md
```

---

## Features

- **Admin Authentication**: Secure login/logout for administrators
- **Client Management**: Full CRUD for clients
- **Financial Movements**: Track expenses and earnings per client
- **Dashboard**: Financial summaries and quick stats
- **Reports**: Filter movements by date range
- **REST API**: Token-based authentication for external integrations
- **Testing**: PHPUnit tests for API and models

---

## Setup

### Local Development

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd client-finance-manager
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   - Copy `.env.example` to `.env` and set your DB credentials.

4. **Create the database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

5. **Run the development server**
   ```bash
   php -S localhost:8000 -t public
   ```

6. **Access the app**
   - Web: http://localhost:8000
   - API: http://localhost:8000/api

---

## Database Schema

- `administrators` (id, username, password, email, created_at, updated_at)
- `clients` (id, name, email, phone, address, created_at, updated_at)
- `movements` (id, client_id, type, amount, description, date, created_by, created_at)
- `api_tokens` (id, administrator_id, token_hash, expires_at, created_at, ...)

---

## Web Interface

- **Login:** `/login` (default: `admin` / `admin123`)
- **Clients:** List, create, edit, delete clients
- **Movements:** Add/view earnings and expenses per client
- **Dashboard:** Financial overview
- **Reports:** Filter movements by date

---

## API Usage

### Authentication

```bash
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

### Get Movements

```bash
curl "http://localhost:8000/api/movements?client_id=1" \
  -H "Authorization: Bearer <token>"
```

### Endpoints

- `POST /api/auth/login` - Login, get token
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Current admin info
- `GET /api/movements?client_id=X` - Movements for client
- `GET /api` - API documentation

---

## Testing

- Run all tests:
  ```bash
  ./vendor/bin/phpunit
  ```

---

## Security

- Passwords hashed (bcrypt)
- SQL injection protection (prepared statements)
- CSRF protection (web)
- Token-based API authentication

---


