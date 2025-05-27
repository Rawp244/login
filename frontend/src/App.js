import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Login from './Login';      // Importe o novo componente Login
import Register from './Register'; // Importe o novo componente Register
import Dashboard from './Dashboard'; // Importe o novo componente Dashboard
import './App.css'; // Seu arquivo CSS para estilização

function App() {
  return (
    <Router>
      <div className="App">
        <Routes>
          {/* A rota padrão (/) vai para a tela de Login */}
          <Route path="/" element={<Login />} />
          {/* A rota /register vai para a tela de Cadastro */}
          <Route path="/register" element={<Register />} />
          {/* A rota /dashboard vai para a tela de sucesso */}
          <Route path="/dashboard" element={<Dashboard />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;