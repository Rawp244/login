// frontend/src/Login.js
import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { toast } from 'react-toastify';

function Login() {
  const [username, setUsername] = useState(''); // Alterado de 'email' para 'username'
  const [senha, setSenha] = useState('');
  const navigate = useNavigate();

  const handleLogin = async () => {
    if (!username || !senha) { // Validação com 'username'
        toast.error('Por favor, preencha seu nome de usuário e senha.');
        return;
    }

    try {
      const response = await fetch("http://localhost/loginmvc/backend/controller/UserController.php/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, senha }), // Enviando 'username' para o backend
      });

      const data = await response.json();

      if (response.ok) {
        if (data.jwt) {
          localStorage.setItem('jwt', data.jwt); // Salva o token JWT
          localStorage.setItem('user_id', data.usuario.id); // Salva o ID do usuário
          localStorage.setItem('user_profile', data.usuario.role); // Salva o perfil do usuário
          localStorage.setItem('username', data.usuario.username); // <--- AGORA SALVA O USERNAME
          localStorage.removeItem('user_email'); // Garante que o user_email antigo seja removido

          toast.success(data.mensagem);
          setTimeout(() => {
            navigate('/dashboard'); // Redireciona para o dashboard
          }, 1000);
        } else {
          toast.error("Login bem-sucedido, mas nenhum token JWT recebido.");
        }
      } else {
        toast.error(data.erro || "Erro desconhecido ao fazer login.");
      }
    } catch (error) {
      console.error("Erro ao conectar com o servidor de login:", error);
      toast.error("Erro ao conectar com o servidor. Verifique sua conexão ou o servidor.");
    }
  };

return (
    <div className="auth-container">
      <h2>Login</h2>
      <div className="input-group">
        <label htmlFor="username">Nome de Usuário</label> {/* Label alterado */}
        <input
          type="text" // Tipo de input para 'username'
          id="username"
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          placeholder="Seu nome de usuário"
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
      <p>Não tem uma conta? <Link to="/register" className="link-button">Cadastre-se</Link></p>
    </div>
  );
}

export default Login;