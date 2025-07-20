import React, { useState, useEffect } from 'react';
import Login from './components/Login';
import Dashboard from './components/Dashboard';
import { API_URLS } from './config/api';
import './App.css';
import './components/Auth.css';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Apply saved theme on app load - tema escuro como padrão
    const savedTheme = localStorage.getItem('theme');
    const theme = savedTheme === 'light' ? 'light' : 'dark'; // Padrão é escuro
    document.documentElement.setAttribute('data-theme', theme);
    
    checkAuthStatus();
  }, []);

  const checkAuthStatus = async () => {
    const token = localStorage.getItem('authToken');
    const savedUser = localStorage.getItem('user');

    if (token && savedUser) {
      try {
        const response = await fetch(API_URLS.VALIDATE, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          const data = await response.json();
          if (data.success) {
            setUser(data.data.user);
            setIsAuthenticated(true);
          } else {
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
          }
        } else {
          localStorage.removeItem('authToken');
          localStorage.removeItem('user');
        }
      } catch (err) {
        console.error('Auth validation error:', err);
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
      }
    }
    setLoading(false);
  };

  const handleLogin = (authData) => {
    setUser(authData.user);
    setIsAuthenticated(true);
  };

  const handleLogout = () => {
    setUser(null);
    setIsAuthenticated(false);
  };


  if (loading) {
    return (
      <div className="App">
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh' }}>
          <h2>Carregando...</h2>
        </div>
      </div>
    );
  }

  return (
    <div className="App">
      {isAuthenticated ? (
        <Dashboard user={user} onLogout={handleLogout} />
      ) : (
        <Login onLogin={handleLogin} />
      )}
    </div>
  );
}

export default App;
