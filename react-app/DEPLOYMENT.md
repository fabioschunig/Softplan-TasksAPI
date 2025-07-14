# Deployment Configuration

## Environment Variables

The application uses environment variables to configure API endpoints for different environments.

### Development

Copy `.env.example` to `.env` and configure:

```bash
# For local development
REACT_APP_API_BASE_URL=http://localhost:8000
REACT_APP_ENV=development
```

### Production

For production deployment, set the following environment variables:

```bash
# For production (same domain)
REACT_APP_API_BASE_URL=
REACT_APP_ENV=production

# For production (different domain)
REACT_APP_API_BASE_URL=https://api.yourdomain.com
REACT_APP_ENV=production
```

## Configuration Files

- **`src/config/api.js`**: Central API configuration
- **`.env.example`**: Template for environment variables
- **`.env`**: Local environment variables (not committed to git)

## How it Works

1. **Development**: Uses `http://localhost:8000` for API calls
2. **Production**: Uses relative URLs (empty BASE_URL) or custom domain
3. **Fallback**: If no environment variable is set, uses relative URLs

## API Endpoints

All API endpoints are centrally managed in `src/config/api.js`:

- `API_URLS.LOGIN`: Authentication login
- `API_URLS.LOGOUT`: Authentication logout  
- `API_URLS.VALIDATE`: Token validation
- `API_URLS.USERS`: User management
- `API_URLS.PROJECTS`: Project management
- `API_URLS.TASKS`: Task management

## Deployment Steps

1. **Build the application**:
   ```bash
   npm run build
   ```

2. **Set production environment variables**:
   ```bash
   export REACT_APP_API_BASE_URL=""
   export REACT_APP_ENV=production
   ```

3. **Deploy the build folder** to your web server

4. **Configure your web server** to serve the React app and proxy API calls to the PHP backend
