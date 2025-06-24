import React, { useState, useEffect } from 'react';
import './Auth.css';

const TaskReport = ({ onNewTask, onEditTask }) => {
  const [tasks, setTasks] = useState([]);
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchTasks();
    fetchProjects();
  }, []);

  const getAuthHeaders = () => ({
    'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
    'Content-Type': 'application/json'
  });

  const fetchTasks = async () => {
    try {
      setLoading(true);
      const url = searchTerm 
        ? `/task.api.php?search=${encodeURIComponent(searchTerm)}`
        : '/task.api.php';
        
      const response = await fetch(url, { headers: getAuthHeaders() });

      if (response.ok) {
        const result = await response.json();
        setTasks(result.data || []);
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Failed to fetch tasks');
      }
    } catch (err) {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const fetchProjects = async () => {
    try {
      const response = await fetch('/project.api.php', { headers: getAuthHeaders() });
      if (response.ok) {
        const result = await response.json();
        setProjects(result.data || []);
      }
    } catch (err) {
      console.error('Failed to fetch projects:', err);
    }
  };

    const handleDeleteTask = async (taskId) => {
    if (!window.confirm('Are you sure you want to delete this task?')) return;

    try {
      const response = await fetch(`/task.api.php/${taskId}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });

      if (response.ok) {
        setTasks(tasks.filter(t => t.id !== taskId));
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Failed to delete task');
      }
    } catch (err) {
      setError('Network error. Please try again.');
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    fetchTasks();
  };

  const getProjectName = (projectId) => {
    const project = projects.find(p => p.id === projectId);
    return project ? project.description : 'N/A';
  };

  const getStatusText = (status) => {
    switch (status) {
      case 0: return 'Pending';
      case 1: return 'In Progress';
      case 2: return 'Completed';
      default: return 'Unknown';
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('pt-BR');
  };

  if (loading) {
    return <div className="task-report"><h2>Loading report...</h2></div>;
  }

  return (
    <div className="task-report">
            <div className="project-header">
        <h2>Tasks Report</h2>
        <button onClick={onNewTask} className="create-button">
          New Task
        </button>
      </div>

      {error && <div className="error-message">{error}</div>}

      <form onSubmit={handleSearch} className="search-form">
        <input
          type="text"
          placeholder="Search tasks..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="search-input"
        />
        <button type="submit" className="search-button">Search</button>
      </form>

      <div className="report-table-container">
        <table className="report-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Description</th>
              <th>Project</th>
              <th>Status</th>
              <th>Tags</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {tasks.length > 0 ? (
              tasks.map(task => (
                <tr key={task.id}>
                  <td>{task.id}</td>
                  <td>{task.description}</td>
                  <td>{getProjectName(task.project_id)}</td>
                  <td>{getStatusText(task.status)}</td>
                  <td>{task.tags || 'N/A'}</td>
                  <td>{formatDate(task.started)}</td>
                                    <td>{formatDate(task.finished)}</td>
                  <td className="report-actions">
                    <button onClick={() => onEditTask(task.id)} className="edit-button small-button">Edit</button>
                    <button onClick={() => handleDeleteTask(task.id)} className="delete-button small-button">Delete</button>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="8" className="no-results">No tasks found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default TaskReport;
