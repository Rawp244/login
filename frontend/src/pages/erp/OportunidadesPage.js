import React, { useState, useEffect, useCallback, useRef } from 'react';
import { toast } from 'react-toastify';
import '../../styles/erp/OportunidadesPage.css';

function OportunidadesPage() {
  const [oportunidades, setOportunidades] = useState([]);
  const [clientes, setClientes] = useState([]);
  const [novaOportunidade, setNovaOportunidade] = useState({
    titulo: '',
    descricao: '',
    data_inicio: '',
    data_fim: '',
    valor_associado: '',
    status: 'ativa',
  });
  const [editandoOportunidadeId, setEditandoOportunidadeId] = useState(null);
  const [loading, setLoading] = useState(true);
  const hasToastShown = useRef(false); 

  const API_URL_OPORTUNIDADES = 'http://localhost/loginmvc/backend/controller/erp/OportunidadeController.php';
  const API_URL_CLIENTES = 'http://localhost/loginmvc/backend/controller/erp/ClienteController.php';

  const getAuthToken = () => {
    return localStorage.getItem('jwt');
  };

  const fetchClientes = useCallback(async () => {
    try {
      const token = getAuthToken();
      if (!token) {
        return [];
      }
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
      return [];
    }
  }, [API_URL_CLIENTES]);

  const fetchOportunidades = useCallback(async () => {
    setLoading(true);
    console.log('DEBUG: fetchOportunidades está sendo executado.');
    try {
      const token = getAuthToken();
      if (!token) {
        setLoading(false);
        return;
      }
      const response = await fetch(`${API_URL_OPORTUNIDADES}?promocoes_gerais=true`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (!response.ok) {
        throw new Error('Erro ao carregar promoções.');
      }
      const data = await response.json();
      setOportunidades(data);
      console.log('DEBUG: Dados de oportunidades carregados (verificar created_by_username):', data); 
      if (!hasToastShown.current) {
        toast.success('Promoções carregadas com sucesso!');
        hasToastShown.current = true;
      }
    } catch (error) {
      console.error('Erro ao carregar promoções:', error);
      toast.error(`Erro ao carregar promoções: ${error.message}`);
    } finally {
      setLoading(false);
    }
  }, [API_URL_OPORTUNIDADES]);

  useEffect(() => {
    const loadInitialData = async () => {
      await fetchClientes();
      await fetchOportunidades();
    };
    loadInitialData();
  }, [fetchClientes, fetchOportunidades]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNovaOportunidade(prevState => ({
      ...prevState,
      [name]: value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!novaOportunidade.titulo || !novaOportunidade.data_inicio) {
      toast.error('Título e Data de Início são obrigatórios para a Promoção!');
      return;
    }

    const promocaoDataToSend = {
      ...novaOportunidade,
      tipo: 'promocao',
      id_cliente: null,
    };

    const method = editandoOportunidadeId ? 'PUT' : 'POST';
    const url = editandoOportunidadeId ? `${API_URL_OPORTUNIDADES}/${editandoOportunidadeId}` : API_URL_OPORTUNIDADES;
    const token = getAuthToken();

    try {
      const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
        body: JSON.stringify(promocaoDataToSend),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      toast.success(editandoOportunidadeId ? 'Promoção atualizada com sucesso!' : 'Promoção criada com sucesso!');
      setNovaOportunidade({
        titulo: '',
        descricao: '',
        data_inicio: '',
        data_fim: '',
        valor_associado: '',
        status: 'ativa',
      });
      setEditandoOportunidadeId(null);
      await fetchOportunidades(); // Garante o refresh após a operação
      console.log('DEBUG: fetchOportunidades foi acionada após o submit para refresh.');
    } catch (error) {
      console.error('Erro na operação de promoção:', error);
      toast.error(`Erro na operação: ${error.message}`);
    }
  };

  const handleEditar = (oportunidade) => {
    setNovaOportunidade({
      titulo: oportunidade.titulo,
      descricao: oportunidade.descricao || '',
      data_inicio: oportunidade.data_inicio ? oportunidade.data_inicio.split(' ')[0] : '', 
      data_fim: oportunidade.data_fim ? oportunidade.data_fim.split(' ')[0] : '', 
      valor_associado: oportunidade.valor_associado || '',
      status: oportunidade.status,
      id_cliente: oportunidade.id_cliente || null,
    });
    setEditandoOportunidadeId(oportunidade.id);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleExcluir = async (id) => {
    if (!window.confirm('Tem certeza que deseja excluir esta promoção?')) {
      return;
    }
    const token = getAuthToken();
    try {
      const response = await fetch(`${API_URL_OPORTUNIDADES}/${id}`, {
        method: 'DELETE',
        headers: { 'Authorization': `Bearer ${token}` },
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      toast.success('Promoção excluída com sucesso!');
      await fetchOportunidades();
    } catch (error) {
      console.error('Erro ao excluir promoção:', error);
      toast.error(`Erro ao excluir promoção: ${error.message}`);
    }
  };

  const formatarData = (dataString) => {
    if (!dataString) return 'N/A';
    const dateParts = dataString.split(' ')[0].split('-');
    const year = parseInt(dateParts[0]);
    const month = parseInt(dateParts[1]) - 1; 
    const day = parseInt(dateParts[2]);
    const date = new Date(year, month, day); 

    return date.toLocaleDateString('pt-BR');
  };

  return (
    <div className="erp-page-container">
      <h2>Gerenciamento de Promoções</h2>

      <div className="form-section highlight-box">
        <h3>{editandoOportunidadeId ? 'Editar Promoção' : 'Adicionar Nova Promoção'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="input-group">
            <label htmlFor="titulo">Título:</label>
            <input
              type="text"
              id="titulo"
              name="titulo"
              value={novaOportunidade.titulo}
              onChange={handleInputChange}
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="descricao">Descrição:</label>
            <textarea
              id="descricao"
              name="descricao"
              value={novaOportunidade.descricao}
              onChange={handleInputChange}
              rows="3"
            ></textarea>
          </div>

          <div className="input-group">
            <label htmlFor="data_inicio">Data de Início:</label>
            <input
              type="date"
              id="data_inicio"
              name="data_inicio"
              value={novaOportunidade.data_inicio}
              onChange={handleInputChange}
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="data_fim">Data de Fim (Opcional):</label>
            <input
              type="date"
              id="data_fim"
              name="data_fim"
              value={novaOportunidade.data_fim}
              onChange={handleInputChange}
            />
          </div>
          <div className="input-group">
            <label htmlFor="valor_associado">Valor Associado (Opcional):</label>
            <input
              type="number"
              step="0.01"
              id="valor_associado"
              name="valor_associado"
              value={novaOportunidade.valor_associado}
              onChange={handleInputChange}
              placeholder="Ex: 99.99"
            />
          </div>
          <div className="input-group">
            <label htmlFor="status">Status:</label>
            <select
              id="status"
              name="status"
              value={novaOportunidade.status}
              onChange={handleInputChange}
              required
            >
              <option value="ativa">Ativa</option>
              <option value="concluida">Concluída</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>

          <div className="form-buttons-row">
            <button type="submit" className="action-button primary large-button">
              {editandoOportunidadeId ? 'Atualizar Promoção' : 'Adicionar Promoção'}
            </button>
            {editandoOportunidadeId && (
              <button type="button" className="action-button secondary large-button" onClick={() => {
                setEditandoOportunidadeId(null);
                setNovaOportunidade({
                  titulo: '',
                  descricao: '',
                  data_inicio: '',
                  data_fim: '',
                  valor_associado: '',
                  status: 'ativa',
                });
              }}>
                Cancelar Edição
              </button>
            )}
          </div>
        </form>
      </div>

      <div className="list-section">
        <h3>Lista de Promoções</h3>
        {loading ? (
          <p>Carregando promoções...</p>
        ) : oportunidades.length === 0 ? (
          <p>Nenhuma promoção cadastrada.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Criado Por</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              {oportunidades.map((oportunidade) => (
                <tr key={oportunidade.id}>
                  <td data-label="ID">{oportunidade.id}</td>
                  <td data-label="Título">{oportunidade.titulo}</td>
                  <td data-label="Data Início">{formatarData(oportunidade.data_inicio)}</td>
                  <td data-label="Data Fim">{formatarData(oportunidade.data_fim)}</td>
                  <td data-label="Valor">
                    {oportunidade.valor_associado ? `R$ ${parseFloat(oportunidade.valor_associado).toFixed(2).replace('.', ',')}` : 'N/A'}
                  </td>
                  <td data-label="Status">{oportunidade.status}</td>
                  <td data-label="Criado Por">{oportunidade.created_by_username || 'Desconhecido'}</td> {/* Garante que 'Desconhecido' aparece se for null */}
                  <td data-label="Ações">
                    <div className="actions-column">
                      <button className="action-button edit" onClick={() => handleEditar(oportunidade)}>Editar</button>
                      <button className="action-button delete" onClick={() => handleExcluir(oportunidade.id)}>Excluir</button>
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

export default OportunidadesPage;