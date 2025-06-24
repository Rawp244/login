import React, { useState, useEffect, useCallback, useRef } from 'react'; // Importado useRef
import { toast } from 'react-toastify';
import './UserManagement.css';

function UserManagement() {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(true);
  const hasToastShown = useRef(false); // Adicionado useRef para controlar o toast

  const getAuthToken = () => {
    return localStorage.getItem('jwt');
  };

  const getDisplayNameForRole = (role) => {
    switch (role) {
      case 'admin':
        return 'Administrador';
      case 'user':
        return 'Usuário Comum';
      default:
        return role;
    }
  };

  const fetchUsuarios = useCallback(async () => {
    setLoading(true);
    try {
      const token = getAuthToken();
      if (!token) {
        toast.error('Token de autenticação não encontrado. Por favor, faça login novamente.');
        setLoading(false);
        return;
      }

      const response = await fetch('http://localhost/loginmvc/backend/controller/UserController.php/users', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
      });
      const data = await response.json();

      if (response.ok) {
        setUsuarios(data);
        // Lógica para mostrar o toast de sucesso apenas uma vez
        if (!hasToastShown.current) {
          toast.success('Usuários carregados com sucesso!');
          hasToastShown.current = true; // Marca que o toast já foi exibido
        }
      } else {
        toast.error(data.erro || `Erro ao carregar usuários: ${response.statusText}`);
      }
    } catch (error) {
      console.error('Erro de rede ou servidor ao carregar usuários:', error);
      toast.error('Erro ao conectar com o servidor para carregar usuários.');
    } finally {
      setLoading(false);
    }
  }, []); // Dependências vazias, pois getAuthToken e hasToastShown.current são estáveis

  useEffect(() => {
    fetchUsuarios();
  }, [fetchUsuarios]);

  const handleUpdateRole = async (userId, newRole) => {
    if (!window.confirm(`Tem certeza que deseja mudar o perfil do usuário ID ${userId} para "${getDisplayNameForRole(newRole)}"?`)) {
      return;
    }

    try {
      const token = getAuthToken();
      if (!token) {
        toast.error('Token de autenticação não encontrado. Por favor, faça login novamente.');
        return;
      }

      const response = await fetch(`http://localhost/loginmvc/backend/controller/UserController.php/users/${userId}/role`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ role: newRole }),
      });
      const data = await response.json();

      if (response.ok) {
        toast.success(`Perfil atualizado com sucesso para o usuário ID ${userId}!`);
        fetchUsuarios(); // Recarrega a lista de usuários após a atualização
      } else {
        toast.error(data.erro || `Erro ao atualizar perfil: ${response.statusText}`);
      }
    } catch (error) {
      console.error('Erro de rede ou servidor ao atualizar perfil:', error);
      toast.error('Erro ao conectar com o servidor para atualizar perfil.');
    }
  };

  return (
    <div className="user-management-container">
      <h2>Gerenciamento de Perfis de Usuários</h2>
      {loading ? (
        <p>Carregando usuários...</p>
      ) : (
        <table className="user-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>USUARIO</th>
              <th>PERFIL ATUAL</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            {usuarios.length > 0 ? (
              usuarios.map((usuario) => (
                <tr key={usuario.id}>
                  <td>{usuario.id}</td>
                  <td>{usuario.username}</td>
                  <td>{getDisplayNameForRole(usuario.role)}</td>
                  <td>
                    <select
                      value={usuario.role}
                      onChange={(e) => handleUpdateRole(usuario.id, e.target.value)}
                      className="role-select"
                    >
                      <option value="user">Usuário Comum</option>
                      <option value="admin">Administrador</option>
                    </select>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="4">Nenhum usuário encontrado ou você não tem permissão para visualizá-los.</td>
              </tr>
            )}
          </tbody>
        </table>
      )}
    </div>
  );
}

export default UserManagement;