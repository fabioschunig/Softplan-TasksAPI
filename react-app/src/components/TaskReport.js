import React, { useState, useEffect } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import { API_URLS } from '../config/api';
import './Auth.css';

const TaskReport = ({ onNewTask, onEditTask, user }) => {
  const [tasks, setTasks] = useState([]);
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [startDate, setStartDate] = useState(null);
  const [endDate, setEndDate] = useState(null);
  const [sortConfig, setSortConfig] = useState({ key: 'finished', direction: 'descending' });

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
      
      // Build query parameters
      const params = new URLSearchParams();
      if (searchTerm) params.append('search', searchTerm);
      if (startDate) params.append('start_date', startDate.toISOString().split('T')[0]);
      if (endDate) params.append('end_date', endDate.toISOString().split('T')[0]);
      
      const url = params.toString() 
        ? `${API_URLS.TASKS}?${params.toString()}`
        : API_URLS.TASKS;
        
      const response = await fetch(url, { headers: getAuthHeaders() });

      if (response.ok) {
        const result = await response.json();
        setTasks(result.data || []);
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Falha ao buscar tarefas');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
    } finally {
      setLoading(false);
    }
  };

  const fetchProjects = async () => {
    try {
      const response = await fetch(API_URLS.PROJECTS, { headers: getAuthHeaders() });
      if (response.ok) {
        const result = await response.json();
        setProjects(result.data || []);
      }
    } catch (err) {
      console.error('Failed to fetch projects:', err);
    }
  };

    const handleDeleteTask = async (taskId) => {
    if (!window.confirm('Você tem certeza que deseja excluir esta tarefa?')) return;

    try {
      const response = await fetch(`${API_URLS.TASKS}/${taskId}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });

      if (response.ok) {
        setTasks(tasks.filter(t => t.id !== taskId));
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Falha ao excluir tarefa');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
    }
  };

    const getProjectName = (projectId) => {
    const project = projects.find(p => p.id === projectId);
    return project ? project.description : ' ';
  };

  const getStatusText = (status) => {
    switch (status) {
      case 0: return 'Pendente';
      case 1: return 'Em Andamento';
      case 2: return 'Concluída';
      default: return 'Desconhecido';
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
        } else if (sortConfig.key === 'reference_date' || sortConfig.key === 'finished') {
            // Handle null dates based on sort direction
            if (!aValue && !bValue) return 0;
            if (!aValue) return sortConfig.direction === 'descending' ? 1 : -1;
            if (!bValue) return sortConfig.direction === 'descending' ? -1 : 1;
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
    if (!dateString) return ' ';
    return new Date(dateString).toLocaleDateString('pt-BR');
  };

  if (loading) {
    return <div className="task-report"><h2>Carregando relatório...</h2></div>;
  }

  return (
    <div className="task-report">
            <div className="project-header">
        <h2>Relatório de Tarefas</h2>
        {user?.role === 'admin' && (
          <button onClick={onNewTask} className="create-button">
            Nova Tarefa
          </button>
        )}
      </div>

      {error && <div className="error-message">{error}</div>}

      <form onSubmit={handleSearch} className="search-form">
          <input
            type="text"
            placeholder="Buscar tarefas..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="search-input"
          />
          <DatePicker
            selected={startDate}
            onChange={(date) => setStartDate(date)}
            dateFormat="dd/MM/yyyy"
            className="date-picker-input"
            placeholderText="Data de Referência"
            title="Filtrar tarefas pela data de referência inicial"
            isClearable
          />
          <DatePicker
            selected={endDate}
            onChange={(date) => setEndDate(date)}
            dateFormat="dd/MM/yyyy"
            className="date-picker-input"
            placeholderText="Data de Referência Final"
            title="Filtrar tarefas pela data de referência final"
            minDate={startDate}
            isClearable
          />
          <div className="filter-actions">
            <button type="submit" className="search-button">Filtrar</button>
            <button 
              type="button" 
              onClick={() => {
                setSearchTerm('');
                setStartDate(null);
                setEndDate(null);
                fetchTasks();
              }}
              className="clear-button"
            >
              Limpar
            </button>
          </div>
      </form>

      <div className="report-table-container">
                <table className="report-table sortable">
          <thead>
            <tr>
              <th onClick={() => requestSort('id')}>ID{getSortIndicator('id')}</th>
              <th onClick={() => requestSort('description')}>Descrição{getSortIndicator('description')}</th>
              <th onClick={() => requestSort('project_id')}>Projeto{getSortIndicator('project_id')}</th>
              <th onClick={() => requestSort('status')}>Situação{getSortIndicator('status')}</th>
              <th onClick={() => requestSort('tags')}>Etiquetas{getSortIndicator('tags')}</th>
              <th onClick={() => requestSort('reference_date')}>Data de Referência{getSortIndicator('reference_date')}</th>
              <th onClick={() => requestSort('finished')}>Data de Fim{getSortIndicator('finished')}</th>
              {user?.role === 'admin' && <th>Ações</th>}
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
                  <td>{task.tags || ' '}</td>
                  <td>{formatDate(task.reference_date)}</td>
                  <td>{formatDate(task.finished)}</td>
                  {user?.role === 'admin' && (
                    <td className="report-actions">
                      <button onClick={() => onEditTask(task.id)} className="edit-button small-button">Editar</button>
                      <button onClick={() => handleDeleteTask(task.id)} className="delete-button small-button">Excluir</button>
                    </td>
                  )}
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={user?.role === 'admin' ? "8" : "7"} className="no-results">Nenhuma tarefa encontrada.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default TaskReport;
