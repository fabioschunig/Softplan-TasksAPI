import React, { useState, useEffect } from 'react';
import ProjectManager from './ProjectManager';
import TaskManager from './TaskManager';
import TaskReport from './TaskReport';
import UserManager from './UserManager';
import { API_URLS } from '../config/api';
import './Auth.css';

const Dashboard = ({ user, onLogout }) => {
  const [activeTab, setActiveTab] = useState('tasks');
  const [initialTaskAction, setInitialTaskAction] = useState(null);
  const [isDarkTheme, setIsDarkTheme] = useState(() => {
    const savedTheme = localStorage.getItem('theme');
    // Tema escuro como padrão: retorna true se não há preferência salva ou se a preferência é 'dark'
    return savedTheme !== 'light';
  });

    const handleRequestNewTask = () => {
    setInitialTaskAction({ action: 'new' });
    setActiveTab('tasks');
  };

  const handleRequestEditTask = (taskId) => {
    setInitialTaskAction({ action: 'edit', taskId });
    setActiveTab('tasks');
  };

  // Reset the action when switching away from the tasks tab
  useEffect(() => {
    if (activeTab !== 'tasks') {
      setInitialTaskAction(null);
    }
  }, [activeTab]);

  // Apply theme to document
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', isDarkTheme ? 'dark' : 'light');
    localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
  }, [isDarkTheme]);

  const toggleTheme = () => {
    setIsDarkTheme(!isDarkTheme);
  };

  const handleLogout = async () => {
    try {
      const token = localStorage.getItem('authToken');
      await fetch(API_URLS.LOGOUT, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      onLogout();
    }
  };


  return (
    <div className="dashboard">
      <div className="dashboard-header">
        <h1>Softplan Tarefas</h1>
        <div className="user-info">
          <span>Bem-vindo(a), {user?.username || 'Usuário'}! ({user?.role || 'user'})</span>
          <button onClick={toggleTheme} className="theme-toggle" title={isDarkTheme ? 'Alternar para tema claro' : 'Alternar para tema escuro'}>
            <div className="theme-toggle-slider">
              {isDarkTheme ? '🌙' : '☀️'}
            </div>
          </button>
          <button onClick={handleLogout} className="logout-button">
            Sair
          </button>
        </div>
      </div>

      <div className="dashboard-tabs">
        <button 
          className={`tab-button ${activeTab === 'tasks' ? 'active' : ''}`}
          onClick={() => setActiveTab('tasks')}
        >
          Tarefas
        </button>
        <button 
          className={`tab-button ${activeTab === 'projects' ? 'active' : ''}`}
          onClick={() => setActiveTab('projects')}
        >
          Projetos
        </button>
        <button 
          className={`tab-button ${activeTab === 'report' ? 'active' : ''}`}
          onClick={() => setActiveTab('report')}
        >
          Relatório
        </button>
        {user?.role === 'admin' && (
          <button 
            className={`tab-button ${activeTab === 'users' ? 'active' : ''}`}
            onClick={() => setActiveTab('users')}
          >
            Usuários
          </button>
        )}
      </div>

      <div className="dashboard-content">
        {activeTab === 'tasks' && <TaskManager initialAction={initialTaskAction} user={user} />}
        {activeTab === 'projects' && <ProjectManager user={user} />}
        {activeTab === 'report' && <TaskReport onNewTask={handleRequestNewTask} onEditTask={handleRequestEditTask} user={user} />}
        {activeTab === 'users' && user?.role === 'admin' && <UserManager token={localStorage.getItem('authToken')} />}
      </div>
    </div>
  );
};

export default Dashboard;
