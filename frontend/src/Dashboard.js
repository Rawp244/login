import React from 'react';
// import { toast } from 'react-toastify'; // Não é necessário aqui a menos que adicione ações

function Dashboard() {
  return (
    <div className="dashboard-container">
      <h1>Parabéns você acessou!</h1>
      {/* Exemplo: Se houvesse um botão de "Sair", poderia ter um toast.success("Desconectado!") */}
    </div>
  );
}

export default Dashboard;