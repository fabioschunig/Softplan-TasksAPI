import React, { useState, useEffect } from 'react';
import ProjectManager from './ProjectManager';
import TaskManager from './TaskManager';
import './Auth.css';

const Dashboard = ({ user, onLogout }) => {
  const [activeTab, setActiveTab] = useState('tasks'); // Default to tasks tab

  const handleLogout = async () => {
    try {
      const token = localStorage.getItem('authToken');
      await fetch('/auth.api.php?action=logout', {
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
        <h1>Softplan Tasks</h1>
        <div className="user-info">
          <span>Welcome, {user?.username || 'User'}!</span>
          <button onClick={handleLogout} className="logout-button">
            Logout
          </button>
        </div>
      </div>

      <div className="dashboard-tabs">
        <button 
          className={`tab-button ${activeTab === 'tasks' ? 'active' : ''}`}
          onClick={() => setActiveTab('tasks')}
        >
          Tasks
        </button>
        <button 
          className={`tab-button ${activeTab === 'projects' ? 'active' : ''}`}
          onClick={() => setActiveTab('projects')}
        >
          Projects
        </button>
      </div>

      <div className="dashboard-content">
        {activeTab === 'tasks' && <TaskManager />}
        {activeTab === 'projects' && <ProjectManager />}
      </div>
    </div>
  );
};

export default Dashboard;
