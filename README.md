# Softplan-TasksAPI

## Description
Simple API to list Softplan tasks with user authentication system

## Features
- **User Authentication**: Login/Register with username and password
- **Session Management**: Secure token-based authentication
- **Protected API Routes**: Tasks API requires authentication
- **React Frontend**: Modern UI with login/register forms
- **Clean Architecture**: Domain-driven design with proper separation of concerns

## Technologies
- **Backend**: PHP 8+ with Clean Architecture
- **Frontend**: React 19.0.0
- **Database**: MySQL with PDO
- **Security**: Argon2ID password hashing, session tokens

## API Endpoints

### Authentication
- `POST /auth.api.php?action=login` - User login
- `POST /auth.api.php?action=register` - User registration  
- `POST /auth.api.php?action=logout` - User logout
- `GET /auth.api.php?action=validate` - Validate session token

### Tasks (Protected)
- `GET /task.api.php` - Get all tasks (requires authentication)

## Setup

### Database
1. Run the SQL script in `database/mysql.sql` to create tables
2. Configure database connection in `.env` file

### Backend
1. Install dependencies: `composer install`
2. Configure environment variables in `config/.env`
3. Serve from `public/` directory

### Frontend
1. Navigate to `react-app/` directory
2. Install dependencies: `npm install`
3. Start development server: `npm start`

## Authentication Flow

1. **Register/Login**: User provides credentials
2. **Token Generation**: Server creates secure session token
3. **Token Storage**: Client stores token in localStorage
4. **API Requests**: Include token in Authorization header
5. **Token Validation**: Server validates token on each request

## Security Features

- **Password Hashing**: Argon2ID algorithm
- **Session Tokens**: 64-character random tokens
- **Token Expiration**: 24-hour session lifetime
- **Password Validation**: Minimum 8 characters with letters and numbers
- **SQL Injection Protection**: Prepared statements
- **CORS Headers**: Proper cross-origin configuration

## Project Structure

```
src/
├── Domain/
│   ├── Model/          # User, Task, UserSession entities
│   └── Repository/     # Repository interfaces
├── Application/
│   └── Service/        # AuthService business logic
└── Infrastructure/
    ├── Repository/     # PDO implementations
    ├── Middleware/     # Authentication middleware
    └── Persistence/    # Database connection
```

## Usage Examples

### Login Request
```bash
curl -X POST http://localhost/auth.api.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"user","password":"password123"}'
```

### Access Protected Route
```bash
curl -X GET http://localhost/task.api.php \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```
