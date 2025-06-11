import React, { useState, useEffect } from 'react';
import './Auth.css'; // Reusing the same CSS for consistency

const TaskManager = () => {
  const [tasks, setTasks] = useState([]);
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [formData, setFormData] = useState({ description: '', tags: '', project_id: '', status: 0 });
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
      console.error('Failed to fetch projects for dropdown:', err);
    }
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    if (!formData.description.trim()) {
      setError('Description is required');
      return;
    }

    const url = editingTask ? `/task.api.php/${editingTask.id}` : '/task.api.php';
    const method = editingTask ? 'PUT' : 'POST';

    const body = {
        description: formData.description.trim(),
        tags: formData.tags.trim(),
        project_id: formData.project_id ? parseInt(formData.project_id, 10) : null,
        status: parseInt(formData.status, 10)
    };

    try {
      const response = await fetch(url, {
        method,
        headers: getAuthHeaders(),
        body: JSON.stringify(body)
      });

      if (response.ok) {
        fetchTasks(); // Refetch all tasks to see the changes
        resetForm();
      } else {
        const errorData = await response.json();
        setError(errorData.error || `Failed to ${editingTask ? 'update' : 'create'} task`);
      }
    } catch (err) {
      setError('Network error. Please try again.');
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

  const startEdit = (task) => {
    setEditingTask(task);
    setFormData({ 
        description: task.description, 
        tags: task.tags || '', 
        project_id: task.project_id || '',
        status: task.status || 0
    });
    setShowCreateForm(false);
  };

  const resetForm = () => {
    setEditingTask(null);
    setShowCreateForm(false);
    setFormData({ description: '', tags: '', project_id: '', status: 0 });
    setError('');
  };

  const handleSearch = (e) => {
    e.preventDefault();
    fetchTasks();
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
    return new Date(dateString).toLocaleString('pt-BR');
  };

  const renderForm = () => (
    <form onSubmit={handleFormSubmit} className="project-form">
      <h3>{editingTask ? 'Edit Task' : 'Create New Task'}</h3>
      <div className="form-group">
        <label htmlFor="description">Description:</label>
        <textarea
          id="description"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          placeholder="Enter task description..."
          maxLength="255"
          rows="3"
          required
        />
        <small>{formData.description.length}/255</small>
      </div>
      <div className="form-group">
        <label htmlFor="tags">Tags:</label>
        <input
          type="text"
          id="tags"
          value={formData.tags}
          onChange={(e) => setFormData({ ...formData, tags: e.target.value })}
          placeholder="e.g., frontend, bug, urgent"
        />
      </div>
      <div className="form-group">
        <label htmlFor="project_id">Project:</label>
        <select 
            id="project_id" 
            value={formData.project_id}
            onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
        >
            <option value="">No Project</option>
            {projects.map(p => (
                <option key={p.id} value={p.id}>{p.description}</option>
            ))}
        </select>
      </div>
      <div className="form-group">
        <label htmlFor="status">Status:</label>
        <select 
            id="status" 
            value={formData.status}
            onChange={(e) => setFormData({ ...formData, status: e.target.value })}
        >
            <option value="0">Pending</option>
            <option value="1">In Progress</option>
            <option value="2">Completed</option>
        </select>
      </div>
      <div className="form-actions">
        <button type="submit" className="submit-button">{editingTask ? 'Update Task' : 'Create Task'}</button>
        <button type="button" onClick={resetForm} className="cancel-button">Cancel</button>
      </div>
    </form>
  );

  if (loading && tasks.length === 0) {
    return <div className="project-manager"><h2>Loading tasks...</h2></div>;
  }

  return (
    <div className="project-manager">
      <div className="project-header">
        <h2>Task Management</h2>
        <button onClick={() => { setShowCreateForm(!showCreateForm); setEditingTask(null); }} className="create-button">
          {showCreateForm ? 'Cancel' : 'New Task'}
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

      {showCreateForm && renderForm()}
      {editingTask && renderForm()}

      <div className="projects-container">
        {tasks.length === 0 ? (
          <p className="no-projects">{searchTerm ? 'No tasks found.' : 'No tasks yet. Create one!'}</p>
        ) : (
          <div className="projects-grid">
            {tasks.map((task) => (
              <div key={task.id} className="project-card">
                <div className="project-content">
                  <div className="project-id">#{task.id}</div>
                  <div className="project-description">{task.description}</div>
                  {task.tags && <div className="task-tags">Tags: <em>{task.tags}</em></div>}
                  <div className="project-dates">
                    <div><strong>Created:</strong> {formatDate(task.created)}</div>
                    {task.updated && <div><strong>Updated:</strong> {formatDate(task.updated)}</div>}
                  </div>
                   <div className={`task-status`}><strong className={`status-text-${getStatusText(task.status).toLowerCase().replace(' ', '-')}`}>Status:</strong> {getStatusText(task.status)}</div>
                   <div className="task-project">
                        <strong>Project:</strong> {projects.find(p => p.id === task.project_id)?.description || 'N/A'}
                   </div>
                </div>
                <div className="project-actions">
                  <button onClick={() => startEdit(task)} className="edit-button">Edit</button>
                  <button onClick={() => handleDeleteTask(task.id)} className="delete-button">Delete</button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default TaskManager;
