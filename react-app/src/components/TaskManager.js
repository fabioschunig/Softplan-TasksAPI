import React, { useState, useEffect } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import './Auth.css'; // Reusing the same CSS for consistency

const TaskManager = ({ initialAction, user }) => {
  const [tasks, setTasks] = useState([]);
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [editingTask, setEditingTask] = useState(null);
  const [formData, setFormData] = useState({ description: '', tags: '', project_id: '', status: 0, started: null, finished: null });
  const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
    fetchTasks();
    fetchProjects();
  }, []);

  useEffect(() => {
    if (initialAction) {
      if (initialAction.action === 'new') {
        setShowCreateForm(true);
        setEditingTask(null);
        resetForm(true); // Keep form open
      } else if (initialAction.action === 'edit' && initialAction.taskId) {
        const taskToEdit = tasks.find(t => t.id === initialAction.taskId);
        if (taskToEdit) {
          startEdit(taskToEdit);
        } else {
          // If task is not in the list, fetch it individually
          fetchTaskAndEdit(initialAction.taskId);
        }
      }
    }
  }, [initialAction, tasks]);

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
        setError(errorData.error || 'Falha ao buscar tarefas');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
    } finally {
      setLoading(false);
    }
  };

    const fetchTaskAndEdit = async (taskId) => {
    try {
      const response = await fetch(`/task.api.php/${taskId}`, { headers: getAuthHeaders() });
      if (response.ok) {
        const result = await response.json();
        if (result.data) {
          startEdit(result.data);
        }
      }
    } catch (err) {
      setError('Falha ao buscar tarefa para edição.');
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
      setError('A descrição é obrigatória');
      return;
    }

    const url = editingTask ? `/task.api.php/${editingTask.id}` : '/task.api.php';
    const method = editingTask ? 'PUT' : 'POST';

    const body = {
        description: formData.description.trim(),
        tags: formData.tags.trim(),
        project_id: formData.project_id ? parseInt(formData.project_id, 10) : null,
        status: parseInt(formData.status, 10),
                started: formData.started ? formData.started.toISOString().slice(0, 10) : null,
        finished: formData.finished ? formData.finished.toISOString().slice(0, 10) : null
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
        setError(errorData.error || `Falha ao ${editingTask ? 'atualizar' : 'criar'} tarefa`);
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
    }
  };

  const handleDeleteTask = async (taskId) => {
    if (!window.confirm('Você tem certeza que deseja excluir esta tarefa?')) return;

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
        setError(errorData.error || 'Falha ao excluir tarefa');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
    }
  };

  const startEdit = (task) => {
    setEditingTask(task);
            setEditingTask(task);
    setFormData({ 
        description: task.description, 
        tags: task.tags || '', 
        project_id: task.project_id || '',
        status: task.status || 0,
        started: task.started ? new Date(task.started) : null,
        finished: task.finished ? new Date(task.finished) : null
    });
    setShowCreateForm(false); // Hide create form if it was open
    setEditingTask(task); // Ensure edit form is shown
  };

    const resetForm = (keepCreateFormOpen = false) => {
    setEditingTask(null);
    setShowCreateForm(keepCreateFormOpen);
    setFormData({ description: '', tags: '', project_id: '', status: 0, started: null, finished: null });
    setError('');
  };


  const handleSearch = (e) => {
    e.preventDefault();
    fetchTasks();
  };

    const getStatusText = (status) => {
    switch (status) {
      case 0: return 'Pendente';
      case 1: return 'Em Andamento';
      case 2: return 'Concluída';
      default: return 'Desconhecido';
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('pt-BR');
  };

  const renderForm = () => (
    <form onSubmit={handleFormSubmit} className="project-form">
      <h3>{editingTask ? 'Editar Tarefa' : 'Criar Nova Tarefa'}</h3>
      <div className="form-group">
        <label htmlFor="description">Descrição:</label>
        <textarea
          id="description"
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          placeholder="Digite a descrição da tarefa..."
          maxLength="255"
          rows="3"
          required
        />
        <small>{formData.description.length}/255 caracteres</small>
      </div>
      <div className="form-group">
        <label htmlFor="tags">Etiquetas:</label>
        <input
          type="text"
          id="tags"
          value={formData.tags}
          onChange={(e) => setFormData({ ...formData, tags: e.target.value })}
          placeholder="ex: frontend, bug, urgente"
        />
      </div>
      <div className="form-group">
        <label htmlFor="project_id">Projeto:</label>
        <select 
            id="project_id" 
            value={formData.project_id}
            onChange={(e) => setFormData({ ...formData, project_id: e.target.value })}
        >
            <option value="">Sem Projeto</option>
            {projects.map(p => (
                <option key={p.id} value={p.id}>{p.description}</option>
            ))}
        </select>
      </div>
      <div className="form-group">
        <label htmlFor="status">Situação:</label>
        <select 
            id="status" 
            value={formData.status}
            onChange={(e) => setFormData({ ...formData, status: e.target.value })}
        >
            <option value="0">Pendente</option>
            <option value="1">Em Andamento</option>
            <option value="2">Concluída</option>
        </select>
      </div>
            <div className="form-group">
        <label htmlFor="started">Data de Início:</label>
        <DatePicker
          id="started"
          selected={formData.started}
          onChange={(date) => setFormData({ ...formData, started: date })}
          dateFormat="dd/MM/yyyy"
          className="date-picker-input"
          placeholderText="Selecione a data de início"
          isClearable
        />
      </div>
      <div className="form-group">
        <label htmlFor="finished">Data de Fim:</label>
        <DatePicker
          id="finished"
          selected={formData.finished}
          onChange={(date) => setFormData({ ...formData, finished: date })}
          dateFormat="dd/MM/yyyy"
          className="date-picker-input"
          placeholderText="Selecione a data de fim"
          minDate={formData.started} // Prevent end date before start date
          isClearable
        />
      </div>
      <div className="form-actions">
        <button type="submit" className="submit-button">{editingTask ? 'Atualizar Tarefa' : 'Criar Tarefa'}</button>
        <button type="button" onClick={resetForm} className="cancel-button">Cancelar</button>
      </div>
    </form>
  );

  if (loading && tasks.length === 0) {
    return <div className="project-manager"><h2>Carregando tarefas...</h2></div>;
  }

  return (
    <div className="project-manager">
      <div className="project-header">
        <h2>Gerenciamento de Tarefas</h2>
        {user?.role === 'admin' && (
          <button onClick={() => { setShowCreateForm(!showCreateForm); setEditingTask(null); }} className="create-button">
            {showCreateForm ? 'Cancelar' : 'Nova Tarefa'}
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
        <button type="submit" className="search-button">Buscar</button>
      </form>

      {user?.role === 'admin' && showCreateForm && renderForm()}
      {user?.role === 'admin' && editingTask && renderForm()}

      <div className="projects-container">
        {tasks.length === 0 ? (
          <p className="no-projects">{searchTerm ? 'Nenhuma tarefa encontrada.' : 'Nenhuma tarefa ainda. Crie uma!'}</p>
        ) : (
          <div className="projects-grid">
            {tasks.map((task) => (
              <div key={task.id} className="project-card">
                <div className="project-content">
                  <div className="project-id">#{task.id}</div>
                  <div className="project-description">{task.description}</div>
                  {task.tags && <div className="task-tags">Etiquetas: <em>{task.tags}</em></div>}
                  <div className="project-dates">
                    <div><strong>Criado:</strong> {formatDate(task.created)}</div>
                    {task.updated && <div><strong>Atualizado:</strong> {formatDate(task.updated)}</div>}
                    {task.started && <div><strong>Iniciado:</strong> {formatDate(task.started)}</div>}
                    {task.finished && <div><strong>Finalizado:</strong> {formatDate(task.finished)}</div>}
                  </div>
                   <div className={`task-status`}><strong className={`status-text-${getStatusText(task.status).toLowerCase().replace(' ', '-')}`}>Situação:</strong> {getStatusText(task.status)}</div>
                   <div className="task-project">
                        <strong>Projeto:</strong> {projects.find(p => p.id === task.project_id)?.description || 'N/A'}
                   </div>
                </div>
                <div className="project-actions">
                  {user?.role === 'admin' ? (
                    <>
                      <button onClick={() => startEdit(task)} className="edit-button">Editar</button>
                      <button onClick={() => handleDeleteTask(task.id)} className="delete-button">Excluir</button>
                    </>
                  ) : (
                    <span className="view-only">Somente Leitura</span>
                  )}
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
