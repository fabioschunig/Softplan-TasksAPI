import React, { useState, useEffect } from 'react';
import { API_URLS } from '../config/api';
import './Auth.css';

const UserManager = ({ token }) => {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [newUser, setNewUser] = useState({
        username: '',
        email: '',
        password: '',
        role: 'user'
    });

    useEffect(() => {
        fetchUsers();
    }, []);

    const fetchUsers = async () => {
        try {
            setLoading(true);
            const response = await fetch(API_URLS.USERS, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                setUsers(data);
                setError('');
            } else {
                const errorData = await response.json();
                setError(errorData.error || 'Falha ao buscar usuários');
            }
        } catch (err) {
            setError('Erro de rede: ' + err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateUser = async (e) => {
        e.preventDefault();
        
        if (!newUser.username || !newUser.email || !newUser.password) {
            setError('Todos os campos são obrigatórios');
            return;
        }

        try {
            const response = await fetch(API_URLS.USERS, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(newUser),
            });

            if (response.ok) {
                const data = await response.json();
                setUsers([data, ...users]);
                setNewUser({ username: '', email: '', password: '', role: 'user' });
                setShowCreateForm(false);
                setError('');
            } else {
                const errorData = await response.json();
                setError(errorData.error || 'Falha ao criar usuário');
            }
        } catch (err) {
            setError('Erro de rede: ' + err.message);
        }
    };

    const handleDeleteUser = async (userId, username) => {
        if (!window.confirm(`Você tem certeza que deseja excluir o usuário "${username}"?`)) {
            return;
        }

        try {
            const response = await fetch(`${API_URLS.USERS}/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });

            if (response.ok) {
                setUsers(users.filter(user => user.id !== userId));
                setError('');
            } else {
                const errorData = await response.json();
                setError(errorData.error || 'Falha ao excluir usuário');
            }
        } catch (err) {
            setError('Erro de rede: ' + err.message);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setNewUser(prev => ({
            ...prev,
            [name]: value
        }));
    };

    if (loading) {
        return <div className="loading">Carregando usuários...</div>;
    }

    return (
        <div className="user-manager">
            <div className="user-manager-header">
                <h2>Gerenciamento de Usuários</h2>
                <button 
                    className="create-user-btn"
                    onClick={() => setShowCreateForm(!showCreateForm)}
                >
                    {showCreateForm ? 'Cancelar' : 'Criar Novo Usuário'}
                </button>
            </div>

            {error && <div className="error-message">{error}</div>}

            {showCreateForm && (
                <div className="create-user-form">
                    <h3>Criar Novo Usuário</h3>
                    <form onSubmit={handleCreateUser}>
                        <div className="form-group">
                            <label>Nome de Usuário:</label>
                            <input
                                type="text"
                                name="username"
                                value={newUser.username}
                                onChange={handleInputChange}
                                placeholder="Digite o nome de usuário (3-31 caracteres)"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>E-mail:</label>
                            <input
                                type="email"
                                name="email"
                                value={newUser.email}
                                onChange={handleInputChange}
                                placeholder="Digite o endereço de e-mail"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>Senha:</label>
                            <input
                                type="password"
                                name="password"
                                value={newUser.password}
                                onChange={handleInputChange}
                                placeholder="Digite a senha (mín. 4 caracteres, letras + números)"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>Função:</label>
                            <select
                                name="role"
                                value={newUser.role}
                                onChange={handleInputChange}
                            >
                                <option value="user">Usuário</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div className="form-actions">
                            <button type="submit" className="submit-btn">Criar Usuário</button>
                            <button 
                                type="button" 
                                className="cancel-btn"
                                onClick={() => setShowCreateForm(false)}
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            )}

            <div className="users-list">
                <h3>Usuários Existentes ({users.length})</h3>
                {users.length === 0 ? (
                    <p>Nenhum usuário encontrado.</p>
                ) : (
                    <div className="users-grid">
                        {users.map(user => (
                            <div key={user.id} className="user-card">
                                <div className="user-info">
                                    <h4>{user.username}</h4>
                                    <p className="user-email">{user.email}</p>
                                    <p className="user-role">
                                        <span className={`role-badge ${user.role}`}>
                                            {user.role.toUpperCase()}
                                        </span>
                                    </p>
                                    <p className="user-created">
                                        Criado em: {new Date(user.created).toLocaleDateString()}
                                    </p>
                                </div>
                                <div className="user-actions">
                                    {user.role !== 'admin' && (
                                        <button
                                            className="delete-btn"
                                            onClick={() => handleDeleteUser(user.id, user.username)}
                                        >
                                            Excluir
                                        </button>
                                    )}
                                    {user.role === 'admin' && (
                                        <span className="admin-protected">Protegido</span>
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

export default UserManager;
