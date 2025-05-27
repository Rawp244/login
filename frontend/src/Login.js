// frontend/src/Login.js

import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';

function Login() {
  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [mensagem, setMensagem] = useState('');
  const navigate = useNavigate();

  const handleLogin = async () => {
    setMensagem(''); // Limpa mensagens anteriores

    if (!email || !senha) {
        setMensagem('Por favor, preencha seu e-mail e senha.');
        return;
    }

    try {
      // CHAMA SEU BACKEND PHP DE LOGIN
      const response = await fetch("http://localhost/loginmvc/backend/controller/UserController.php/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, senha }),
      });

      const data = await response.json();

      if (response.ok) { // Verifica se a resposta HTTP é 2xx (sucesso)
        setMensagem(data.mensagem);
        // Redirecionar para a tela de dashboard após o login bem-sucedido
        setTimeout(() => {
          navigate('/dashboard');
        }, 1000); // Pequeno atraso para o usuário ver a mensagem
      } else {
        // Trata erros retornados pelo backend (e.g., e-mail ou senha inválidos)
        setMensagem(data.erro || "Erro desconhecido ao fazer login.");
      }
    } catch (error) {
      // Erros de rede, servidor fora do ar, etc.
      console.error("Erro ao conectar com o servidor de login:", error);
      setMensagem("Erro ao conectar com o servidor. Verifique sua conexão ou o servidor.");
    }
  };

  return (
    <div className="auth-container">
      <h2>Login</h2>
      <div className="input-group">
        <label htmlFor="email">E-mail</label>
        <input
          type="email"
          id="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="Seu e-mail"
        />
      </div>
      <div className="input-group">
        <label htmlFor="senha">Senha</label>
        <input
          type="password"
          id="senha"
          value={senha}
          onChange={(e) => setSenha(e.target.value)}
          placeholder="Sua senha"
        />
      </div>
      <button onClick={handleLogin} className="auth-button">Entrar</button>
      {mensagem && <p className={`message ${mensagem.includes('Erro') ? 'error-message' : ''}`}>{mensagem}</p>}
      <p>Não tem uma conta? <Link to="/register" className="link-button">Cadastre-se</Link></p>
    </div>
  );
}

export default Login;