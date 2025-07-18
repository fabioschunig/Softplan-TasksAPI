import React, { useState, useEffect } from 'react';
import './Auth.css';

const ProjectManager = ({ user }) => {
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [editingProject, setEditingProject] = useState(null);
  const [formData, setFormData] = useState({ description: '' });
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchProjects();
  }, []);

  const fetchProjects = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('authToken');
      const url = searchTerm 
        ? `/project.api.php?search=${encodeURIComponent(searchTerm)}`
        : '/project.api.php';
        
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      if (response.ok) {
        const result = await response.json();
        setProjects(result.data || []);
        setError('');
      } else if (response.status === 401) {
        setError('Autenticação necessária. Por favor, faça login novamente.');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Falha ao buscar projetos');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
      console.error('Fetch projects error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateProject = async (e) => {
    e.preventDefault();
    
    if (!formData.description.trim()) {
      setError('A descrição é obrigatória');
      return;
    }

    try {
      const token = localStorage.getItem('authToken');
      const response = await fetch('/project.api.php', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ description: formData.description.trim() })
      });

      if (response.ok) {
        const result = await response.json();
        setProjects([result.data, ...projects]);
        setFormData({ description: '' });
        setShowCreateForm(false);
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Falha ao criar projeto');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
      console.error('Create project error:', err);
    }
  };

  const handleUpdateProject = async (e) => {
    e.preventDefault();
    
    if (!formData.description.trim()) {
      setError('A descrição é obrigatória');
      return;
    }

    try {
      const token = localStorage.getItem('authToken');
      const response = await fetch(`/project.api.php/${editingProject.id}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ description: formData.description.trim() })
      });

      if (response.ok) {
        const result = await response.json();
        setProjects(projects.map(p => p.id === editingProject.id ? result.data : p));
        setEditingProject(null);
        setFormData({ description: '' });
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Falha ao atualizar projeto');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
      console.error('Update project error:', err);
    }
  };

  const handleDeleteProject = async (projectId) => {
    if (!window.confirm('Você tem certeza que deseja excluir este projeto?')) {
      return;
    }

    try {
      const token = localStorage.getItem('authToken');
      const response = await fetch(`/project.api.php/${projectId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      if (response.ok) {
        setProjects(projects.filter(p => p.id !== projectId));
        setError('');
      } else {
        const errorData = await response.json();
        setError(errorData.error || 'Falha ao excluir projeto');
      }
    } catch (err) {
      setError('Erro de rede. Por favor, tente novamente.');
      console.error('Delete project error:', err);
    }
  };

  const startEdit = (project) => {
    setEditingProject(project);
    setFormData({ description: project.description });
    setShowCreateForm(false);
  };

  const cancelEdit = () => {
    setEditingProject(null);
    setFormData({ description: '' });
  };

  const handleSearch = (e) => {
    e.preventDefault();
    fetchProjects();
  };

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('pt-BR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  if (loading) {
    return (
      <div className="project-manager">
        <h2>Carregando projetos...</h2>
      </div>
    );
  }

  return (
    <div className="project-manager">
      <div className="project-header">
        <h2>Gerenciamento de Projetos</h2>
        {user?.role === 'admin' && (
          <button 
            onClick={() => {
              setShowCreateForm(!showCreateForm);
              setEditingProject(null);
              setFormData({ description: '' });
            }}
            className="create-button"
          >
            {showCreateForm ? 'Cancelar' : 'Novo Projeto'}
          </button>
        )}
      </div>

      {error && <div className="error-message">{error}</div>}

      {/* Search Form */}
      <form onSubmit={handleSearch} className="search-form">
        <input
          type="text"
          placeholder="Buscar projetos..."
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
          className="search-input"
        />
        <button type="submit" className="search-button">Buscar</button>
        {searchTerm && (
          <button 
            type="button" 
            onClick={() => {
              setSearchTerm('');
              fetchProjects();
            }}
            className="clear-search-button"
          >
            Limpar
          </button>
        )}
      </form>

      {/* Create Form */}
      {user?.role === 'admin' && showCreateForm && (
        <form onSubmit={handleCreateProject} className="project-form">
          <h3>Criar Novo Projeto</h3>
          <div className="form-group">
            <label htmlFor="description">Descrição:</label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) => setFormData({ description: e.target.value })}
              placeholder="Digite a descrição do projeto..."
              maxLength="255"
              rows="3"
              required
            />
            <small>{formData.description.length}/255 caracteres</small>
          </div>
          <div className="form-actions">
            <button type="submit" className="submit-button">Criar Projeto</button>
            <button 
              type="button" 
              onClick={() => setShowCreateForm(false)}
              className="cancel-button"
            >
              Cancelar
            </button>
          </div>
        </form>
      )}

      {/* Edit Form */}
      {user?.role === 'admin' && editingProject && (
        <form onSubmit={handleUpdateProject} className="project-form">
          <h3>Editar Projeto</h3>
          <div className="form-group">
            <label htmlFor="edit-description">Descrição:</label>
            <textarea
              id="edit-description"
              value={formData.description}
              onChange={(e) => setFormData({ description: e.target.value })}
              placeholder="Digite a descrição do projeto..."
              maxLength="255"
              rows="3"
              required
            />
            <small>{formData.description.length}/255 caracteres</small>
          </div>
          <div className="form-actions">
            <button type="submit" className="submit-button">Atualizar Projeto</button>
            <button 
              type="button" 
              onClick={cancelEdit}
              className="cancel-button"
            >
              Cancelar
            </button>
          </div>
        </form>
      )}

      {/* Projects List */}
      <div className="projects-container">
        {projects.length === 0 ? (
          <p className="no-projects">
            {searchTerm ? 'Nenhum projeto encontrado para sua busca.' : 'Nenhum projeto encontrado. Crie seu primeiro projeto!'}
          </p>
        ) : (
          <div className="projects-grid">
            {projects.map((project) => (
              <div key={project.id} className="project-card">
                <div className="project-content">
                  <div className="project-id">#{project.id}</div>
                  <div className="project-description">{project.description}</div>
                  <div className="project-dates">
                    <div><strong>Criado:</strong> {formatDate(project.created)}</div>
                    {project.updated && (
                      <div><strong>Atualizado:</strong> {formatDate(project.updated)}</div>
                    )}
                  </div>
                </div>
                <div className="project-actions">
                  {user?.role === 'admin' ? (
                    <>
                      <button 
                        onClick={() => startEdit(project)}
                        className="edit-button"
                        disabled={editingProject?.id === project.id}
                      >
                        Editar
                      </button>
                      <button 
                        onClick={() => handleDeleteProject(project.id)}
                        className="delete-button"
                      >
                        Excluir
                      </button>
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

export default ProjectManager;
