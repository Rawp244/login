import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify';
// Ajuste o caminho do CSS se necessário, mas '../../styles/erp/EstoquePage.css' parece correto
import '../../styles/erp/EstoquePage.css';

function EstoquePage() {
  const [estoque, setEstoque] = useState([]);
  const [produtos, setProdutos] = useState([]); // Ainda necessário para o dropdown de seleção
  const [novoMovimento, setNovoMovimento] = useState({ product_id: '', quantity: '', type: 'entrada' });
  const [loading, setLoading] = useState(true);

  const API_URL_ESTOQUE = 'http://localhost/loginmvc/backend/controller/erp/EstoqueController.php';
  const API_URL_PRODUTOS = 'http://localhost/loginmvc/backend/controller/erp/ProdutoController.php';

  // Função para obter o JWT do localStorage
  const getAuthToken = () => {
    return localStorage.getItem('jwt');
  };

  useEffect(() => {
    // Para garantir que os produtos para seleção e o estoque sejam carregados
    // Idealmente, as duas requisições devem ocorrer, mas a exibição do nome
    // do produto no histórico agora depende apenas do backend.
    fetchEstoque();
    fetchProdutosParaSelecao();
  }, []); // Removido 'produtos' da dependência, pois agora o nome vem do backend.

  const fetchEstoque = async () => {
    setLoading(true);
    try {
      const token = getAuthToken(); // Obter o token
      const response = await fetch(API_URL_ESTOQUE, {
        headers: {
          'Authorization': `Bearer ${token}` // Incluir o token na requisição
        }
      });

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Erro HTTP! Status: ${response.status}, Resposta: ${errorText}`);
      }

      const data = await response.json();
      // CORREÇÃO AQUI: Agora usamos diretamente o 'product_name' que vem do backend
      setEstoque(data); 
    } catch (error) {
      console.error('Erro ao carregar estoque:', error);
      toast.error(`Erro ao conectar com o servidor para carregar estoque. Detalhes: ${error.message}`);
    } finally {
      setLoading(false);
    }
  };

  const fetchProdutosParaSelecao = async () => {
    try {
      const token = getAuthToken(); // Obter o token
      const response = await fetch(API_URL_PRODUTOS, {
        headers: {
          'Authorization': `Bearer ${token}` // Incluir o token na requisição
        }
      });
      if (!response.ok) {
        throw new Error('Erro ao carregar produtos para seleção.');
      }
      const data = await response.json();
      setProdutos(data);
      return data;
    } catch (error) {
      console.error('Erro ao carregar produtos:', error);
      toast.error(`Erro ao carregar produtos para seleção: ${error.message}`);
      return [];
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setNovoMovimento(prevState => ({ ...prevState, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!novoMovimento.product_id || !novoMovimento.quantity || novoMovimento.quantity <= 0) {
      toast.error('Por favor, selecione um produto e insira uma quantidade válida.');
      return;
    }

    try {
      const token = getAuthToken(); // Obter o token
      const response = await fetch(API_URL_ESTOQUE, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}` // Incluir o token
        },
        body: JSON.stringify(novoMovimento),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();
      toast.success(data.mensagem);
      setNovoMovimento({ product_id: '', quantity: '', type: 'entrada' });
      fetchEstoque(); // Recarregar estoque após o envio
    } catch (error) {
      console.error('Erro ao registrar movimento de estoque:', error);
      toast.error(`Erro ao registrar movimento: ${error.message}`);
    }
  };

  const formatarData = (dataString) => {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
    return new Date(dataString).toLocaleString('pt-BR', options);
  };

  return (
    <div className="erp-page-container">
      <h2>Gerenciamento de Estoque</h2>

      {/* Formulário de Movimentação de Estoque */}
      <div className="form-section highlight-box">
        <h3>Registrar Movimento de Estoque</h3>
        <form onSubmit={handleSubmit}>
          <div className="input-group">
            <label htmlFor="product_id">Produto:</label>
            <select
              id="product_id"
              name="product_id"
              value={novoMovimento.product_id}
              onChange={handleChange}
              required
            >
              <option value="">Selecione um Produto</option>
              {produtos.map(produto => (
                <option key={produto.id} value={produto.id}>
                  {produto.name} (Estoque atual: {produto.stock})
                </option>
              ))}
            </select>
          </div>
          <div className="input-group">
            <label htmlFor="quantity">Quantidade:</label>
            <input
              type="number"
              id="quantity"
              name="quantity"
              value={novoMovimento.quantity}
              onChange={handleChange}
              placeholder="Quantidade"
              min="1"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="type">Tipo de Movimento:</label>
            <select
              id="type"
              name="type"
              value={novoMovimento.type}
              onChange={handleChange}
              required
            >
              <option value="entrada">Entrada (Aumentar)</option>
              <option value="saida">Saída (Diminuir)</option>
            </select>
          </div>
          <button type="submit" className="action-button primary">
            Registrar Movimento
          </button>
        </form>
      </div>

      {/* Lista de Movimentações de Estoque */}
      <div className="list-section">
        <h3>Histórico de Movimentações</h3>
        {loading ? (
          <p>Carregando histórico de estoque...</p>
        ) : estoque.length === 0 ? (
          <p>Nenhuma movimentação de estoque registrada.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Tipo</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              {estoque.map((movimento) => (
                <tr key={movimento.id}>
                  <td data-label="ID">{movimento.id}</td>
                  <td data-label="Produto">{movimento.product_name}</td> {/* <--- USANDO product_name DIRETAMENTE DO BACKEND */}
                  <td data-label="Quantidade">{movimento.quantity}</td>
                  <td>{movimento.type === 'entrada' ? 'Entrada' : 'Saída'}</td>
                  <td>{formatarData(movimento.movement_date)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}

export default EstoquePage;