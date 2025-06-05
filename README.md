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

## 📋 Prerequisites
- PHP 8+ 
- MySQL
- Node.js and npm
- Composer

## 🚀 Quick Start

### 1. 🗄️ Database Setup
```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE softplan_task_api;"

# Run SQL script (creates tables: project, task, users, user_sessions)
mysql -u root -p softplan_task_api < database/mysql.sql
```

### 2. ⚙️ Backend Setup
```bash
# Install PHP dependencies
composer install

# Create configuration file
cp config/.env.example config/.env

# Edit config/.env with your MySQL credentials
# Example:
# DB_HOST=localhost
# DB_USER=root  
# DB_PASS=your_mysql_password
# DB_NAME=softplan_task_api

# Give permission and start PHP server (port 8000)
chmod +x start-server.sh
./start-server.sh
```

### 3. 🎨 Frontend Setup
```bash
# Navigate to React app directory
cd react-app

# Install dependencies
npm install

# Start React development server (port 3000)
npm start
```

### 4. 🌐 Access Application
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000

The React app is already configured with proxy for the PHP API.

## 🔐 Authentication System

The project includes a complete authentication system with:

- **User Registration/Login** with secure credentials
- **Session Tokens** (64-character secure tokens)
- **Password Encryption** using Argon2ID
- **Session Expiration** (24-hour lifetime)
- **Protected APIs** requiring authentication

### 🎯 Usage Flow

1. Access http://localhost:3000
2. Register a new user or login with existing credentials
3. Access the dashboard with tasks
4. Token is automatically managed by the frontend

## 📡 API Endpoints

### Authentication
- `POST /auth.api.php?action=register` - User registration  
- `POST /auth.api.php?action=login` - User login
- `POST /auth.api.php?action=logout` - User logout
- `GET /auth.api.php?action=validate` - Validate session token

### Tasks (Protected)
- `GET /task.api.php` - Get all tasks (requires authentication)

## 🔄 Authentication Flow

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

react-app/
├── src/
│   ├── components/     # Login, Register, Dashboard
│   ├── App.js         # Main application logic
│   └── App.css        # Styles
└── public/            # Static files
```

## Troubleshooting

### Common Issues

1. **API Connection Error**: Make sure PHP server is running on port 8000
2. **Database Connection**: Check config/.env file credentials
3. **CORS Issues**: APIs include proper CORS headers
4. **Port Conflicts**: Change ports in start-server.sh or package.json if needed

### 🔧 Useful Commands

```bash
# Start PHP server manually
cd public && php -S localhost:8000

# Check PHP version (requires 8+)
php --version

# Install Composer dependencies
composer install

# React development server
cd react-app && npm start

# Reinstall React dependencies
cd react-app && npm install
```

## Usage Examples

### Login Request
```bash
curl -X POST http://localhost:8000/auth.api.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"user","password":"password123"}'
```

### Access Protected Route
```bash
curl -X GET http://localhost:8000/task.api.php \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
