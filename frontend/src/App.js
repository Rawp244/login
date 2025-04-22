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
      const response = await fetch("http://localhost/login/backend/controller/UserController.php", {
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
