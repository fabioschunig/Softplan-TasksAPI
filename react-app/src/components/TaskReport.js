import React, { useState, useEffect } from 'react';
import './Auth.css';

const TaskReport = ({ onNewTask, onEditTask }) => {
  const [tasks, setTasks] = useState([]);
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [sortConfig, setSortConfig] = useState({ key: 'description', direction: 'ascending' });

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

  const sortedTasks = React.useMemo(() => {
    let sortableItems = [...tasks];
    if (sortConfig !== null) {
      sortableItems.sort((a, b) => {
        let aValue = a[sortConfig.key];
        let bValue = b[sortConfig.key];

        // Handle special cases like project name
        if (sortConfig.key === 'project_id') {
          aValue = getProjectName(a.project_id);
          bValue = getProjectName(b.project_id);
        }
        if (sortConfig.key === 'status') {
            aValue = getStatusText(a.status);
            bValue = getStatusText(b.status);
        } else if (sortConfig.key === 'started' || sortConfig.key === 'finished') {
            // Treat null or invalid dates as being "greater" so they sort to the end.
            if (!aValue) return 1;
            if (!bValue) return -1;
            aValue = new Date(aValue);
            bValue = new Date(bValue);
        }

        if (aValue < bValue) {
          return sortConfig.direction === 'ascending' ? -1 : 1;
        }
        if (aValue > bValue) {
          return sortConfig.direction === 'ascending' ? 1 : -1;
        }
        return 0;
      });
    }
    return sortableItems;
  }, [tasks, sortConfig, projects]);

  const requestSort = (key) => {
    let direction = 'ascending';
    if (sortConfig.key === key && sortConfig.direction === 'ascending') {
      direction = 'descending';
    }
    setSortConfig({ key, direction });
  };

  const getSortIndicator = (columnKey) => {
    if (sortConfig.key === columnKey) {
      return sortConfig.direction === 'ascending' ? ' ▲' : ' ▼';
    }
    return null;
  };

  const handleSearch = (e) => {
    e.preventDefault();
    fetchTasks();
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
                <table className="report-table sortable">
          <thead>
            <tr>
              <th onClick={() => requestSort('id')}>ID{getSortIndicator('id')}</th>
              <th onClick={() => requestSort('description')}>Description{getSortIndicator('description')}</th>
              <th onClick={() => requestSort('project_id')}>Project{getSortIndicator('project_id')}</th>
              <th onClick={() => requestSort('status')}>Status{getSortIndicator('status')}</th>
              <th onClick={() => requestSort('tags')}>Tags{getSortIndicator('tags')}</th>
              <th onClick={() => requestSort('started')}>Start Date{getSortIndicator('started')}</th>
              <th onClick={() => requestSort('finished')}>End Date{getSortIndicator('finished')}</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {tasks.length > 0 ? (
              sortedTasks.map(task => (
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
