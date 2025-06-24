import React, { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import '../../styles/erp/PedidosPage.css';

function PedidosPage() {
  const [pedidos, setPedidos] = useState([]);
  const [produtos, setProdutos] = useState([]);
  const [clientes, setClientes] = useState([]);
  const [novoPedido, setNovoPedido] = useState({ client_id: '', client_name_display: '', total_amount: 0, status: 'Venda de Veículo Zero KM', items: [] });
  const [itemAtual, setItemAtual] = useState({ product_id: '', quantity: '', price_at_order: '' });
  const [editandoPedidoId, setEditandoPedidoId] = useState(null);
  // CORREÇÃO: Usar useState para a variável loading
  const [loading, setLoading] = useState(true); 

  const navigate = useNavigate();

  const API_URL_PEDIDOS = 'http://localhost/loginmvc/backend/controller/erp/PedidoController.php';
  const API_URL_PRODUTOS = 'http://localhost/loginmvc/backend/controller/erp/ProdutoController.php';
  const API_URL_CLIENTES = 'http://localhost/loginmvc/backend/controller/erp/ClienteController.php';

  const getAuthToken = () => {
    return localStorage.getItem('jwt');
  };

  const fetchProdutosParaSelecao = useCallback(async () => {
    try {
      const token = getAuthToken();
      const response = await fetch(API_URL_PRODUTOS, {
        headers: { 'Authorization': `Bearer ${token}` }
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
  }, [API_URL_PRODUTOS]);

  const fetchClientesParaSelecao = useCallback(async () => {
    try {
      const token = getAuthToken();
      const response = await fetch(API_URL_CLIENTES, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (!response.ok) {
        throw new Error('Erro ao carregar clientes para seleção.');
      }
      const data = await response.json();
      setClientes(data);
      return data;
    } catch (error) {
      console.error('Erro ao carregar clientes:', error);
      toast.error(`Erro ao carregar clientes para seleção: ${error.message}`);
      return [];
    }
  }, [API_URL_CLIENTES]);

  const fetchPedidos = useCallback(async (loadedProducts, loadedClients) => {
    setLoading(true);
    try {
      const token = getAuthToken();
      const response = await fetch(API_URL_PEDIDOS, {
        headers: { 'Authorization': `Bearer ${token}` }
      });

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Erro HTTP! Status: ${response.status}, Resposta: ${errorText}`);
      }

      const data = await response.json();
      const pedidosComNomes = data.map(pedido => ({
        ...pedido,
        client_name_display: loadedClients.find(c => c.id === pedido.client_id)?.name || 'Cliente Desconhecido',
        items: Array.isArray(pedido.items) ? pedido.items.map(item => ({
          ...item,
          product_name: loadedProducts.find(p => p.id === item.product_id)?.name || 'Produto Desconhecido'
        })) : []
      }));
      setPedidos(pedidosComNomes);
    } catch (error) {
      console.error('Erro ao carregar pedidos:', error);
      toast.error(`Erro ao conectar com o servidor para carregar pedidos. Detalhes: ${error.message}`);
    } finally {
      setLoading(false);
    }
  }, [API_URL_PEDIDOS]);

  useEffect(() => {
    const loadInitialData = async () => {
      setLoading(true);
      const [loadedProducts, loadedClients] = await Promise.all([
        fetchProdutosParaSelecao(),
        fetchClientesParaSelecao()
      ]);
      if (loadedProducts && loadedClients) {
        await fetchPedidos(loadedProducts, loadedClients);
      }
      setLoading(false);
    };
    loadInitialData();
  }, [fetchProdutosParaSelecao, fetchClientesParaSelecao, fetchPedidos]);

  const handlePedidoChange = (e) => {
    const { name, value } = e.target;
    if (name === 'client_id') {
      const selectedClient = clientes.find(c => c.id === parseInt(value));
      setNovoPedido(prevState => ({
        ...prevState,
        client_id: parseInt(value),
        client_name_display: selectedClient ? selectedClient.name : ''
      }));
    } else {
      setNovoPedido(prevState => ({ ...prevState, [name]: value }));
    }
  };

  const handleItemChange = (e) => {
    const { name, value } = e.target;
    if (name === 'product_id') {
      const selectedProduct = produtos.find(p => p.id === parseInt(value));
      setItemAtual(prevState => ({
        ...prevState,
        [name]: value,
        price_at_order: selectedProduct ? parseFloat(selectedProduct.price).toFixed(2) : ''
      }));
    } else {
      setItemAtual(prevState => ({ ...prevState, [name]: value }));
    }
  };

  const handleAddItem = () => {
    if (!itemAtual.product_id || !itemAtual.quantity || !itemAtual.price_at_order || parseFloat(itemAtual.quantity) <= 0 || parseFloat(itemAtual.price_at_order) <= 0) {
      toast.error('Por favor, preencha todos os campos do item (produto, quantidade, preço).');
      return;
    }

    const produtoSelecionado = produtos.find(p => p.id === parseInt(itemAtual.product_id));
    if (!produtoSelecionado) {
        toast.error('Produto selecionado inválido.');
        return;
    }

    const itemToAdd = {
      product_id: parseInt(itemAtual.product_id),
      product_name: produtoSelecionado.name,
      quantity: parseInt(itemAtual.quantity),
      price_at_order: parseFloat(itemAtual.price_at_order),
    };

    setNovoPedido(prevState => {
      const updatedItems = [...prevState.items, itemToAdd];
      const newTotal = updatedItems.reduce((acc, item) => acc + (item.quantity * item.price_at_order), 0);
      return {
        ...prevState,
        items: updatedItems,
        total_amount: newTotal
      };
    });
    setItemAtual({ product_id: '', quantity: '', price_at_order: '' });
  };

  const handleRemoveItem = (indexToRemove) => {
    setNovoPedido(prevState => {
      const updatedItems = prevState.items.filter((_, index) => index !== indexToRemove);
      const newTotal = updatedItems.reduce((acc, item) => acc + (item.quantity * item.price_at_order), 0);
      return {
        ...prevState,
        items: updatedItems,
        total_amount: newTotal
      };
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!novoPedido.client_id) {
      toast.error('Por favor, selecione um cliente.');
      return;
    }
    if (novoPedido.items.length === 0) {
      toast.error('Adicione pelo menos um item ao pedido.');
      return;
    }

    const pedidoDataToSend = {
      client_id: parseInt(novoPedido.client_id),
      sale_type: novoPedido.status,
      items: novoPedido.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        price_at_order: item.price_at_order,
      })),
      total_amount: parseFloat(novoPedido.total_amount).toFixed(2),
    };

    console.log('DEBUG FRONTEND: novoPedido.status antes do envio:', novoPedido.status);
    console.log('DEBUG FRONTEND: pedidoDataToSend completo antes do envio:', pedidoDataToSend);

    const method = editandoPedidoId ? 'PUT' : 'POST';
    const url = editandoPedidoId ? `${API_URL_PEDIDOS}/${editandoPedidoId}` : API_URL_PEDIDOS;
    const token = getAuthToken();

    try {
      const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(pedidoDataToSend),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();
      toast.success(data.mensagem);
      setNovoPedido({ client_id: '', client_name_display: '', total_amount: 0, status: 'Venda de Veículo Zero KM', items: [] });
      setItemAtual({ product_id: '', quantity: '', price_at_order: '' });
      setEditandoPedidoId(null);
      const [loadedProducts, loadedClients] = await Promise.all([
        fetchProdutosParaSelecao(),
        fetchClientesParaSelecao()
      ]);
      await fetchPedidos(loadedProducts, loadedClients);

    } catch (error) {
      console.error('Erro na operação de pedido:', error);
      toast.error(`Erro na operação: ${error.message}`);
    }
  };

  const handleEditar = (pedido) => {
    const parsedTotalAmount = parseFloat(pedido.total_amount);
    setNovoPedido({
      client_id: pedido.client_id,
      client_name_display: pedido.client_name_display,
      total_amount: parsedTotalAmount,
      status: pedido.status,
      items: Array.isArray(pedido.items) ? pedido.items.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        price_at_order: parseFloat(item.price_at_order),
        product_name: item.product_name
      })) : []
    });
    setEditandoPedidoId(pedido.id);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleExcluir = async (id) => {
    if (!window.confirm('Tem certeza que deseja excluir este pedido? Isso pode afetar o estoque dos produtos se o backend for configurado para isso!')) {
      return;
    }
    const token = getAuthToken();
    try {
      const response = await fetch(`${API_URL_PEDIDOS}/${id}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();
      toast.success(data.mensagem);
      const [loadedProducts, loadedClients] = await Promise.all([
        fetchProdutosParaSelecao(),
        fetchClientesParaSelecao()
      ]);
      await fetchPedidos(loadedProducts, loadedClients);

    } catch (error) {
      console.error('Erro ao excluir pedido:', error);
      toast.error(`Erro ao excluir pedido: ${error.message}`);
    }
  };

  const formatarData = (dataString) => {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
    return new Date(dataString).toLocaleString('pt-BR', options);
  };

  return (
    <div className="erp-page-container">
      <h2>Gerenciamento de Pedidos</h2>

      <div className="form-section highlight-box">
        <h3>{editandoPedidoId ? 'Editar Pedido' : 'Adicionar Novo Pedido'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="pedido-panels-wrapper">
            <div className="pedido-panel client-panel">
              <div className="input-group client-select-group">
                <label htmlFor="client_id">Cliente:</label>
                <select
                  id="client_id"
                  name="client_id"
                  value={novoPedido.client_id}
                  onChange={handlePedidoChange}
                  required
                >
                  <option value="">Selecione um Cliente</option>
                  {clientes.map(cliente => (
                    <option key={cliente.id} value={cliente.id}>
                      {cliente.name} ({cliente.email})
                    </option>
                  ))}
                </select>
                <button
                  type="button"
                  className="action-button secondary small-button"
                  onClick={() => navigate('/erp/clientes')}
                >
                  + Novo Cliente
                </button>
              </div>
            </div>

            <div className="pedido-panel items-order-panel">
              <h4>Itens do Pedido</h4>
              <div className="item-input-group">
                <select
                  name="product_id"
                  value={itemAtual.product_id}
                  onChange={handleItemChange}
                >
                  <option value="">Selecione um Produto</option>
                  {produtos.map(produto => (
                    <option key={produto.id} value={produto.id}>
                      {produto.name} (Estoque: {produto.stock} / R$ {parseFloat(produto.price).toFixed(2).replace('.', ',')})
                    </option>
                  ))}
                </select>
                <input
                  type="number"
                  name="quantity"
                  value={itemAtual.quantity}
                  onChange={handleItemChange}
                  placeholder="Quantidade"
                  min="1"
                />
                <input
                  type="text"
                  name="price_at_order"
                  value={itemAtual.price_at_order ? `R$ ${parseFloat(itemAtual.price_at_order).toFixed(2).replace('.', ',')}` : ''}
                  onChange={(e) => {
                    const rawValue = e.target.value.replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
                    setItemAtual(prevState => ({ ...prevState, price_at_order: rawValue }));
                  }}
                  placeholder="Preço no Pedido"
                />
                <button type="button" className="action-button primary" onClick={handleAddItem}>
                  Adicionar Item
                </button>
              </div>

              {novoPedido.items.length > 0 && (
                <table className="data-table item-table">
                  <thead>
                    <tr>
                      <th>Produto</th>
                      <th>Qtd.</th>
                      <th>Preço Unit.</th>
                      <th>Total Item</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    {novoPedido.items.map((item, index) => (
                      <tr key={index}>
                        <td>{item.product_name}</td>
                        <td>{item.quantity}</td>
                        <td>R$ {parseFloat(item.price_at_order).toFixed(2).replace('.', ',')}</td>
                        <td>R$ {(item.quantity * item.price_at_order).toFixed(2).replace('.', ',')}</td>
                        <td>
                          <button
                            type="button"
                            className="action-button delete small"
                            onClick={() => handleRemoveItem(index)}
                          >
                            Remover
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              )}
              <div className="total-amount">
                <strong>Valor Total do Pedido:</strong> R$ {parseFloat(novoPedido.total_amount).toFixed(2).replace('.', ',')}
              </div>
            </div>
          </div>

          <div className="bottom-form-controls">
            <div className="input-group">
              <label htmlFor="status">Tipo de Venda:</label>
              <select
                id="status"
                name="status"
                value={novoPedido.status}
                onChange={handlePedidoChange}
                required
              >
                {/* As opções de status conforme o seu desejo */}
                <option value="Venda de Veículo Zero KM">Venda de Veículo Zero KM</option>
                <option value="Venda de Veículo Usado">Venda de Veículo Usado</option>
              </select>
            </div>

            <div className="form-buttons-row">
              <button type="submit" className="action-button primary large-button">
                {editandoPedidoId ? 'Atualizar Pedido' : 'Adicionar Pedido'}
              </button>
              {editandoPedidoId && (
                <button type="button" className="action-button secondary large-button" onClick={() => {
                  setEditandoPedidoId(null);
                  setNovoPedido({ client_id: '', client_name_display: '', total_amount: 0, status: 'Venda de Veículo Zero KM', items: [] });
                  setItemAtual({ product_id: '', quantity: '', price_at_order: '' });
                }}>
                  Cancelar Edição
                </button>
              )}
            </div>
          </div>
        </form>
      </div>

      <div className="list-section">
        <h3>Lista de Pedidos</h3>
        {loading ? (
          <p>Carregando pedidos...</p>
        ) : pedidos.length === 0 ? (
          <p>Nenhum pedido cadastrado.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Valor Total</th>
                <th>Tipo de Venda</th>
                <th>Data do Pedido</th>
                <th>Itens</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              {pedidos.map((pedido) => (
                <tr key={pedido.id}>
                  <td data-label="ID">{pedido.id}</td>
                  <td data-label="Cliente">{pedido.client_name_display}</td>
                  <td data-label="Valor Total">R$ {parseFloat(pedido.total_amount).toFixed(2).replace('.', ',')}</td>
                  <td data-label="Tipo de Venda">{pedido.status}</td>
                  <td data-label="Data do Pedido">{formatarData(pedido.order_date)}</td>
                  <td data-label="Itens">
                    <ul className="order-items-list">
                      {pedido.items && Array.isArray(pedido.items) && pedido.items.map((item, idx) => (
                        <li key={idx}>
                          {item.product_name} ({item.quantity}x) - R$ {parseFloat(item.price_at_order).toFixed(2).replace('.', ',')}
                        </li>
                      ))}
                    </ul>
                  </td>
                  <td data-label="Ações">
                    <div className="actions-column">
                      <button className="action-button edit" onClick={() => handleEditar(pedido)}>Editar</button>
                      <button className="action-button delete" onClick={() => handleExcluir(pedido.id)}>Excluir</button>
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

export default PedidosPage;