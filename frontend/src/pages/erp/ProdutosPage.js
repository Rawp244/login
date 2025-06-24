import React, { useState, useEffect } from 'react';
import '../../styles/erp/ProdutosPage.css'; // Certifique-se de que este caminho está correto

function ProdutosPage() {
  const [produtos, setProdutos] = useState([]);
  const [novoProduto, setNovoProduto] = useState({ name: '', description: '', price: '', stock: '', sku: '' });
  const [editandoProdutoId, setEditandoProdutoId] = useState(null);
  const [mensagem, setMensagem] = useState('');
  const [loading, setLoading] = useState(true);

  const API_URL = 'http://localhost/loginmvc/backend/controller/erp/ProdutoController.php';

  useEffect(() => {
    fetchProdutos();
  }, []);

  const fetchProdutos = async () => {
    setLoading(true);
    setMensagem('');
    try {
      const response = await fetch(API_URL);

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Erro HTTP! Status: ${response.status}, Resposta: ${errorText}`);
      }

      const data = await response.json();
      setProdutos(data);
    } catch (error) {
      console.error('Erro ao carregar produtos:', error);
      setMensagem(`Erro ao conectar com o servidor para carregar produtos. Detalhes: ${error.message}`);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    let formattedValue = value;

    if (name === 'price') {
      // Remove tudo que não é dígito ou vírgula, e depois substitui a vírgula por ponto para cálculo
      const rawValue = value.replace(/[^\d,]/g, '').replace(',', '.');
      const numericValue = parseFloat(rawValue);

      if (!isNaN(numericValue)) {
        // Formata para BRL e lida com pontos e vírgulas
        formattedValue = new Intl.NumberFormat('pt-BR', {
          style: 'currency',
          currency: 'BRL',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(numericValue);
      } else {
        formattedValue = ''; // Limpa se não for um número válido
      }
    }

    setNovoProduto(prevState => ({ ...prevState, [name]: formattedValue }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMensagem('');

    // Remove o "R$" e formata o preço para ponto decimal antes de enviar
    const priceToSend = novoProduto.price
      .replace('R$', '')
      .replace(/\./g, '') // Remove pontos de milhar
      .replace(',', '.')   // Troca vírgula por ponto decimal
      .trim();

    const productData = { ...novoProduto, price: priceToSend };

    const method = editandoProdutoId ? 'PUT' : 'POST';
    const url = editandoProdutoId ? `${API_URL}/${editandoProdutoId}` : API_URL;

    try {
      const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(productData),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();

      setMensagem(data.mensagem);
      setNovoProduto({ name: '', description: '', price: '', stock: '', sku: '' });
      setEditandoProdutoId(null);
      fetchProdutos(); // Recarrega a lista
    } catch (error) {
      console.error('Erro na operação de produto:', error);
      setMensagem(`Erro na operação: ${error.message}`);
    }
  };

  const handleEditar = (produto) => {
    // Ao editar, formatar o preço para exibição no input
    const formattedPriceForEdit = new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(parseFloat(produto.price));

    setNovoProduto({
      name: produto.name,
      description: produto.description,
      price: formattedPriceForEdit,
      stock: produto.stock,
      sku: produto.sku
    });
    setEditandoProdutoId(produto.id);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleExcluir = async (id) => {
    if (!window.confirm('Tem certeza que deseja excluir este produto?')) {
      return;
    }
    setMensagem('');
    try {
      const response = await fetch(`${API_URL}/${id}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();

      if (response.ok) {
        setMensagem(data.mensagem);
        fetchProdutos(); // Recarrega a lista
      }
    } catch (error) {
      console.error('Erro ao excluir produto:', error);
      setMensagem(`Erro ao excluir produto: ${error.message}`);
    }
  };

  return (
    <div className="erp-page-container">
      <h2>Gerenciamento de Produtos</h2>
      {mensagem && <p className={`message ${mensagem.includes('Erro') ? 'error' : ''}`}>{mensagem}</p>}

      {/* Formulário de Adição/Edição de Produto */}
      <div className="form-section highlight-box"> {/* Adicionado highlight-box aqui */}
        <h3>{editandoProdutoId ? 'Editar Produto' : 'Adicionar Novo Produto'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="input-group">
            <label htmlFor="name">Nome do Produto:</label>
            <input
              type="text"
              id="name"
              name="name"
              value={novoProduto.name}
              onChange={handleChange}
              placeholder="Nome do Produto"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="description">Descrição:</label>
            <textarea
              id="description"
              name="description"
              value={novoProduto.description}
              onChange={handleChange}
              placeholder="Descrição do Produto"
            ></textarea>
          </div>
          <div className="input-group">
            <label htmlFor="price">Preço:</label>
            <input
              type="text" // Alterado para text para permitir formatação
              id="price"
              name="price"
              value={novoProduto.price}
              onChange={handleChange}
              placeholder="R$ 0,00"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="stock">Estoque:</label>
            <input
              type="number"
              id="stock"
              name="stock"
              value={novoProduto.stock}
              onChange={handleChange}
              placeholder="Quantidade em estoque"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="sku">Código do Produto:</label> {/* Alterado de SKU para Código do Produto */}
            <input
              type="text"
              id="sku"
              name="sku"
              value={novoProduto.sku}
              onChange={handleChange}
              placeholder="Código Único do Produto"
            />
          </div>
          <button type="submit" className="action-button primary">
            {editandoProdutoId ? 'Atualizar Produto' : 'Adicionar Produto'}
          </button>
          {editandoProdutoId && (
            <button type="button" className="action-button secondary" onClick={() => {
              setEditandoProdutoId(null);
              setNovoProduto({ name: '', description: '', price: '', stock: '', sku: '' });
            }}>
              Cancelar Edição
            </button>
          )}
        </form>
      </div>

      {/* Lista de Produtos */}
      <div className="list-section">
        <h3>Lista de Produtos</h3>
        {loading ? (
          <p>Carregando produtos...</p>
        ) : produtos.length === 0 ? (
          <p>Nenhum produto cadastrado.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Código do Produto</th> {/* Alterado de SKU para Código do Produto */}
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              {produtos.map((produto) => (
                <tr key={produto.id}>
                  <td>{produto.id}</td>
                  <td>{produto.name}</td>
                  <td>{produto.description}</td>
                  <td>{new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(produto.price)}</td>
                  <td>{produto.stock}</td>
                  <td>{produto.sku}</td>
                  <td>
                    <div className="actions-column"> {/* Novo wrapper para os botões */}
                        <button className="action-button edit" onClick={() => handleEditar(produto)}>Editar</button>
                        <button className="action-button delete" onClick={() => handleExcluir(produto.id)}>Excluir</button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}

export default ProdutosPage;