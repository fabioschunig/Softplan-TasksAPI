// API Configuration
const API_CONFIG = {
  // Base URL - uses environment variable or falls back to relative URLs for production
  BASE_URL: process.env.REACT_APP_API_BASE_URL || '',
  
  // API Endpoints
  ENDPOINTS: {
    LOGIN: '/auth.api.php?action=login',
    LOGOUT: '/auth.api.php?action=logout', 
    VALIDATE: '/auth.api.php?action=validate',
    USERS: '/user.api.php',
    PROJECTS: '/project.api.php',
    TASKS: '/task.api.php'
  }
};

// Helper function to build full API URLs
export const getApiUrl = (endpoint) => {
  return `${API_CONFIG.BASE_URL}${endpoint}`;
};

// Specific API URLs
export const API_URLS = {
  LOGIN: getApiUrl(API_CONFIG.ENDPOINTS.LOGIN),
  LOGOUT: getApiUrl(API_CONFIG.ENDPOINTS.LOGOUT),
  VALIDATE: getApiUrl(API_CONFIG.ENDPOINTS.VALIDATE),
  USERS: getApiUrl(API_CONFIG.ENDPOINTS.USERS),
  PROJECTS: getApiUrl(API_CONFIG.ENDPOINTS.PROJECTS),
  TASKS: getApiUrl(API_CONFIG.ENDPOINTS.TASKS)
};

export default API_CONFIG;
