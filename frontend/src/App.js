<<<<<<< HEAD
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
=======
import React, { useState } from "react";

function App() {
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const [mensagem, setMensagem] = useState("");

  const handleChange = (e) => {
    const { name, value } = e.target;
    if (name === "email") setEmail(value);
    if (name === "senha") setSenha(value);
  };

  const handleSubmit = async () => {
    try {
      const response = await fetch("http://localhost/login/login/backend/controller/UserController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, senha }),
      });

      const data = await response.json();
      setMensagem(data.mensagem || data.erro);
    } catch (error) {
      setMensagem("Erro ao conectar com o servidor.");
    }
  };

  return (
    <div style={{ maxWidth: 300, margin: "50px auto", padding: 20, border: "1px solid #ccc", borderRadius: 10 }}>
      <h2>Cadastro de Usuário</h2>
      <input
        type="text"
        name="email"
        placeholder="E-mail"
        onChange={handleChange}
        style={{ width: "100%", padding: 8, marginBottom: 10 }}
      />
      <input
        type="password"
        name="senha"
        placeholder="Senha"
        onChange={handleChange}
        style={{ width: "100%", padding: 8, marginBottom: 10 }}
      />
      <button onClick={handleSubmit} style={{ width: "100%", padding: 10, backgroundColor: "#4CAF50", color: "#fff", border: "none" }}>
        Cadastrar
      </button>

      {mensagem && <p style={{ marginTop: 20 }}>{mensagem}</p>}
    </div>
  );
}

export default App;
>>>>>>> 6a1e99a490e7a70324a1eb194a411ddde497eaa0
