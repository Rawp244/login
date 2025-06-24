// frontend/src/components/SideMenu.js
import React from 'react';
import { Link, useNavigate } from 'react-router-dom'; // Importe useNavigate
import '../styles/SideMenu.css'; // Novo CSS para o menu lateral

function SideMenu({ userProfile }) {
  const navigate = useNavigate(); // Inicialize useNavigate

  const handleLogout = () => {
    // Remove o token JWT do localStorage
    localStorage.removeItem('jwt');
    // Opcional: Remover outros itens relacionados ao usuário (melhora a limpeza da sessão)
    localStorage.removeItem('user_id');
    localStorage.removeItem('user_profile');
    localStorage.removeItem('username'); 
    localStorage.removeItem('user_email'); // Se ainda existir

    // Redireciona o usuário para a página de login
    navigate('/login'); 
    // Poderia adicionar um toast aqui, mas é melhor que o login mostre algo como "Desconectado" ou o header suma
    console.log("Usuário deslogado. Token JWT removido.");
  };

  return (
    <div className="side-menu">
      <div className="menu-header">
        <img src="/logo.png" alt="Logo" className="menu-logo" /> {/* Adicione seu logo aqui, ou use um placeholder */}
        <h3>Gestão Online</h3>
      </div>
      <ul className="menu-items">
        <li>
          <Link to="/dashboard" className="menu-item-link">
            <i className="fas fa-home"></i> <span>Painel</span>
          </Link>
        </li>
        {/* Itens do menu para ADMIN */}
        {userProfile === 'admin' && (
          <>
            <li className="menu-category">ERP</li>
            <li>
              <Link to="/erp/clientes" className="menu-item-link">
                <i className="fas fa-users"></i> <span>Clientes</span>
              </Link>
            </li>
            <li>
              <Link to="/erp/produtos" className="menu-item-link">
                <i className="fas fa-box"></i> <span>Produtos</span>
              </Link>
            </li>
            <li>
              <Link to="/erp/fornecedores" className="menu-item-link">
                <i className="fas fa-truck"></i> <span>Fornecedores</span>
              </Link>
            </li>
            <li>
              <Link to="/erp/estoque" className="menu-item-link">
                <i className="fas fa-warehouse"></i> <span>Estoque</span>
              </Link>
            </li>
            <li>
              <Link to="/erp/pedidos" className="menu-item-link">
                <i className="fas fa-shopping-cart"></i> <span>Pedidos</span>
              </Link>
            </li>
            <li>
              <Link to="/erp/oportunidades" className="menu-item-link">
                <i className="fas fa-bullhorn"></i> <span>Oportunidades & Promoções</span>
              </Link>
            </li>
            <li className="menu-category">Administração</li>
            <li>
              <Link to="/users" className="menu-item-link">
                <i className="fas fa-user-cog"></i> <span>Gerenciar Usuários</span>
              </Link>
            </li>
            {/* Adicione outras categorias de administração aqui */}
          </>
        )}
        {/* Itens do menu para USUÁRIO/CLIENTE */}
        {userProfile === 'user' && ( // Ou 'client'
          <>
            <li className="menu-category">Minha Conta</li>
            <li>
              <Link to="/meu-cadastro" className="menu-item-link">
                <i className="fas fa-user-circle"></i> <span>Meu Cadastro</span>
              </Link>
            </li>
            <li>
              <Link to="/minhas-promocoes" className="menu-item-link">
                <i className="fas fa-tag"></i> <span>Minhas Promoções</span>
              </Link>
            </li>
            {/* Adicione outras categorias para o cliente aqui */}
          </>
        )}
        {/* Botão de Logout - Adicionado aqui */}
        <li>
          <button onClick={handleLogout} className="menu-item-link logout-button"> {/* Adicionei uma classe 'logout-button' para estilização */}
            <i className="fas fa-sign-out-alt"></i> <span>Sair</span>
          </button>
        </li>
      </ul>
    </div>
  );
}

export default SideMenu;