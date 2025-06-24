// frontend/src/App.js
import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

// Importe seus componentes de página
import Login from './Login';
import Register from './Register';
import DashboardPage from './pages/DashboardPage'; // Importado como DashboardPage
import UserManagement from './UserManagement'; // Verifique se este componente existe e está no caminho correto

// ROTAS DO MÓDULO ERP/CRM (verifique os caminhos, ajustei para pages/erp)
import ProdutosPage from './pages/erp/ProdutosPage';
import FornecedoresPage from './pages/erp/FornecedoresPage';
import EstoquePage from './pages/erp/EstoquePage';
import PedidosPage from './pages/erp/PedidosPage';
import ClientesPage from './pages/erp/ClientesPage';
import OportunidadesPage from './pages/erp/OportunidadesPage'; // Nova rota para OportunidadesPage

import './App.css'; // Importa o CSS global da aplicação

// Componente para proteger rotas (apenas para usuários logados)
const PrivateRoute = ({ children }) => {
  const isAuthenticated = localStorage.getItem('jwt');
  return isAuthenticated ? children : <Navigate to="/login" />;
};

// Componente para proteger rotas de Admin
const AdminRoute = ({ children }) => {
    const isAuthenticated = localStorage.getItem('jwt');
    const userProfile = localStorage.getItem('user_profile');
    // Permite acesso se autenticado E o perfil for 'admin'
    return isAuthenticated && userProfile === 'admin' ? children : <Navigate to="/login" />;
};

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          {/* Rota padrão: Se já logado, vai para dashboard, senão para login */}
          <Route path="/" element={
            localStorage.getItem('jwt') ? <Navigate to="/dashboard" /> : <Navigate to="/login" />
          } />
          
          {/* Rotas de Autenticação */}
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />

          {/* Rota do Dashboard (protegida para acesso apenas por usuários logados) */}
          <Route path="/dashboard" element={
            <PrivateRoute>
              <DashboardPage />
            </PrivateRoute>
          } />

          {/* Rota para Gerenciamento de Usuários (protegida para acesso apenas por administradores) */}
          {/* **ATENÇÃO**: Verifique se UserManagement existe e é o componente correto para esta rota */}
          <Route path="/users" element={
            <AdminRoute>
              <UserManagement />
            </AdminRoute>
          } />

          {/* ROTAS DO MÓDULO ERP/CRM (protegidas para acesso apenas por administradores) */}
          {/* IMPORTANTE: Todos os caminhos de importação para 'pages/erp' foram confirmados */}
          <Route path="/erp/produtos" element={
            <AdminRoute>
              <ProdutosPage />
            </AdminRoute>
          } />
          <Route path="/erp/fornecedores" element={
            <AdminRoute>
              <FornecedoresPage />
            </AdminRoute>
          } />
          <Route path="/erp/estoque" element={
            <AdminRoute>
              <EstoquePage />
            </AdminRoute>
          } />
          <Route path="/erp/pedidos" element={
            <AdminRoute>
              <PedidosPage />
            </AdminRoute>
          } />
          <Route path="/erp/clientes" element={
            <AdminRoute>
              <ClientesPage />
            </AdminRoute>
          } />
          <Route path="/erp/oportunidades" element={
            <AdminRoute>
              <OportunidadesPage />
            </AdminRoute>
          } />

          {/* Rotas específicas para a área do cliente (você precisará criar esses componentes de página) */}
          <Route path="/meu-cadastro" element={
            <PrivateRoute>
              {/* Você pode criar um componente MyProfilePage.js em 'src/pages/client/' */}
              <div>Página Meu Cadastro (Cliente - Componente a ser criado)</div>
            </PrivateRoute>
          } />
          <Route path="/minhas-promocoes" element={
            <PrivateRoute>
              {/* Você pode criar um componente ClientPromotionsPage.js em 'src/pages/client/' */}
              <div>Página Minhas Promoções (Cliente - Componente a ser criado)</div>
            </PrivateRoute>
          } />

          {/* Rota para lidar com caminhos não encontrados (404) */}
          <Route path="*" element={<div>404 - Página Não Encontrada</div>} />

        </Routes>
      </div>
      {/* Adicione o ToastContainer aqui, fora do <Routes> mas dentro do <Router> */}
      <ToastContainer
        position="top-right"
        autoClose={3000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
      />
    </Router>
  );
}

export default App;