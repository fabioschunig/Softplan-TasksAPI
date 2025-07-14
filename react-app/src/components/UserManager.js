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
                setError(errorData.error || 'Failed to fetch users');
            }
        } catch (err) {
            setError('Network error: ' + err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateUser = async (e) => {
        e.preventDefault();
        
        if (!newUser.username || !newUser.email || !newUser.password) {
            setError('All fields are required');
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
                setError(errorData.error || 'Failed to create user');
            }
        } catch (err) {
            setError('Network error: ' + err.message);
        }
    };

    const handleDeleteUser = async (userId, username) => {
        if (!window.confirm(`Are you sure you want to delete user "${username}"?`)) {
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
                setError(errorData.error || 'Failed to delete user');
            }
        } catch (err) {
            setError('Network error: ' + err.message);
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
        return <div className="loading">Loading users...</div>;
    }

    return (
        <div className="user-manager">
            <div className="user-manager-header">
                <h2>User Management</h2>
                <button 
                    className="create-user-btn"
                    onClick={() => setShowCreateForm(!showCreateForm)}
                >
                    {showCreateForm ? 'Cancel' : 'Create New User'}
                </button>
            </div>

            {error && <div className="error-message">{error}</div>}

            {showCreateForm && (
                <div className="create-user-form">
                    <h3>Create New User</h3>
                    <form onSubmit={handleCreateUser}>
                        <div className="form-group">
                            <label>Username:</label>
                            <input
                                type="text"
                                name="username"
                                value={newUser.username}
                                onChange={handleInputChange}
                                placeholder="Enter username (3-31 characters)"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>Email:</label>
                            <input
                                type="email"
                                name="email"
                                value={newUser.email}
                                onChange={handleInputChange}
                                placeholder="Enter email address"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>Password:</label>
                            <input
                                type="password"
                                name="password"
                                value={newUser.password}
                                onChange={handleInputChange}
                                placeholder="Enter password (min 8 chars, letters + numbers)"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>Role:</label>
                            <select
                                name="role"
                                value={newUser.role}
                                onChange={handleInputChange}
                            >
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div className="form-actions">
                            <button type="submit" className="submit-btn">Create User</button>
                            <button 
                                type="button" 
                                className="cancel-btn"
                                onClick={() => setShowCreateForm(false)}
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            )}

            <div className="users-list">
                <h3>Existing Users ({users.length})</h3>
                {users.length === 0 ? (
                    <p>No users found.</p>
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
                                        Created: {new Date(user.created).toLocaleDateString()}
                                    </p>
                                </div>
                                <div className="user-actions">
                                    {user.role !== 'admin' && (
                                        <button
                                            className="delete-btn"
                                            onClick={() => handleDeleteUser(user.id, user.username)}
                                        >
                                            Delete
                                        </button>
                                    )}
                                    {user.role === 'admin' && (
                                        <span className="admin-protected">Protected</span>
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
