// frontend/src/Register.js (Exemplo, adapte se seu arquivo for diferente)
import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { toast } from 'react-toastify';

function Register() {
  const [username, setUsername] = useState(''); // <--- ALTERADO
  const [senha, setSenha] = useState('');
  const [role, setRole] = useState('user'); // Padrão
  const navigate = useNavigate();

  const handleRegister = async () => {
    if (!username || !senha) { // <--- ALTERADO
        toast.error('Por favor, preencha seu nome de usuário e senha.'); // <--- ALTERADO
        return;
    }

    try {
      const response = await fetch("http://localhost/loginmvc/backend/controller/UserController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, senha, role }), // <--- ENVIANDO USERNAME
      });

      const data = await response.json();

      if (response.ok) {
        toast.success(data.mensagem || "Usuário registrado com sucesso!");
        setTimeout(() => {
          navigate('/login');
        }, 1000);
      } else {
        toast.error(data.erro || "Erro desconhecido ao registrar usuário.");
      }
    } catch (error) {
      console.error("Erro ao conectar com o servidor de registro:", error);
      toast.error("Erro ao conectar com o servidor. Verifique sua conexão ou o servidor.");
    }
  };

  return (
    <div className="auth-container">
      <h2>Cadastro</h2>
      <div className="input-group">
        <label htmlFor="username">Nome de Usuário</label> {/* <--- LABEL ALTERADO */}
        <input
          type="text" // <--- TIPO ALTERADO
          id="username"
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          placeholder="Escolha um nome de usuário" // <--- PLACEHOLDER ALTERADO
          required
        />
      </div>
      <div className="input-group">
        <label htmlFor="senha">Senha</label>
        <input
          type="password"
          id="senha"
          value={senha}
          onChange={(e) => setSenha(e.target.value)}
          placeholder="Crie uma senha"
          required
        />
      </div>
      <button onClick={handleRegister} className="auth-button">Registrar</button>
      <p>Já tem uma conta? <Link to="/login" className="link-button">Fazer Login</Link></p>
    </div>
  );
}

export default Register;