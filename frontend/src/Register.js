import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';

function Register() {
  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [mensagem, setMensagem] = useState('');
  const navigate = useNavigate();

  const handleRegister = async () => {
    setMensagem(''); // Limpa mensagens anteriores
    if (!email || !senha) {
        setMensagem('Por favor, preencha todos os campos.');
        return;
    }

    try {
      // ESTE TRECHO JÁ CHAMA SEU BACKEND PHP DE CADASTRO ORIGINAL
     const response = await fetch("http://localhost/loginmvc/backend/controller/UserController.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, senha }),
});

      const data = await response.json();
      setMensagem(data.mensagem || data.erro);

      if (data.mensagem === "Usuário salvo com sucesso") {
        // Redirecionar para a tela de login após o cadastro bem-sucedido
        setTimeout(() => {
          navigate('/'); // Redireciona para a rota raiz, que é o login
        }, 1500); // Espera 1.5 segundos para o usuário ver a mensagem
      }
    } catch (error) {
      setMensagem("Erro ao conectar com o servidor.");
    }
  };

  return (
    <div className="auth-container">
      <h2>Cadastro de Usuário</h2>
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
      <button onClick={handleRegister} className="auth-button">Cadastrar</button>
      {mensagem && <p className="message">{mensagem}</p>}
      <p>Já tem uma conta? <Link to="/" className="link-button">Faça login</Link></p>
    </div>
  );
}

export default Register;