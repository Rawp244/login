import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify'; // Importe o toast
import '../../styles/erp/FornecedoresPage.css';

function FornecedoresPage() {
  const [fornecedores, setFornecedores] = useState([]);
  const [novoFornecedor, setNovoFornecedor] = useState({
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    street: '',
    number: '',
    neighborhood: '',
    city: '',
    state: ''
  });
  const [editandoFornecedorId, setEditandoFornecedorId] = useState(null);
  // const [mensagem, setMensagem] = useState(''); // Não é mais necessário
  const [loading, setLoading] = useState(true);

  const API_URL = 'http://localhost/loginmvc/backend/controller/erp/FornecedorController.php';

  useEffect(() => {
    fetchFornecedores();
  }, []);

  const fetchFornecedores = async () => {
    setLoading(true);
    // setMensagem(''); // Não é mais necessário
    try {
      const response = await fetch(API_URL);

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Erro HTTP! Status: ${response.status}, Resposta: ${errorText}`);
      }

      const data = await response.json();
      setFornecedores(data);
    } catch (error) {
      console.error('Erro ao carregar fornecedores:', error);
      toast.error(`Erro ao conectar com o servidor para carregar fornecedores. Detalhes: ${error.message}`); // Notificação de erro
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    let formattedValue = value;

    if (name === 'phone') {
      formattedValue = value.replace(/\D/g, '');
      if (formattedValue.length > 10) {
        formattedValue = `(${formattedValue.substring(0, 2)}) ${formattedValue.substring(2, 7)}-${formattedValue.substring(7, 11)}`;
      } else if (formattedValue.length > 6) {
        formattedValue = `(${formattedValue.substring(0, 2)}) ${formattedValue.substring(2, 6)}-${formattedValue.substring(6, 10)}`;
      } else if (formattedValue.length > 2) {
        formattedValue = `(${formattedValue.substring(0, 2)}) ${formattedValue.substring(2)}`;
      } else if (formattedValue.length > 0) {
        formattedValue = `(${formattedValue.substring(0, formattedValue.length)}`;
      }
    }

    setNovoFornecedor(prevState => ({ ...prevState, [name]: formattedValue }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    // setMensagem(''); // Não é mais necessário

    const method = editandoFornecedorId ? 'PUT' : 'POST';
    const url = editandoFornecedorId ? `${API_URL}/${editandoFornecedorId}` : API_URL;

    try {
      const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(novoFornecedor),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();

      toast.success(data.mensagem); // Notificação de sucesso
      setNovoFornecedor({
        name: '',
        contact_person: '',
        phone: '',
        email: '',
        street: '',
        number: '',
        neighborhood: '',
        city: '',
        state: ''
      });
      setEditandoFornecedorId(null);
      fetchFornecedores();
    } catch (error) {
      console.error('Erro na operação de fornecedor:', error);
      toast.error(`Erro na operação: ${error.message}`); // Notificação de erro
    }
  };

  const handleEditar = (fornecedor) => {
    setNovoFornecedor({
      name: fornecedor.name,
      contact_person: fornecedor.contact_person,
      phone: fornecedor.phone,
      email: fornecedor.email,
      street: fornecedor.address ? fornecedor.address.split(', ')[0] || '' : '',
      number: fornecedor.address ? fornecedor.address.split(', ')[1] || '' : '',
      neighborhood: fornecedor.address ? fornecedor.address.split(', ')[2] || '' : '',
      city: fornecedor.address ? fornecedor.address.split(', ')[3] || '' : '',
      state: fornecedor.address ? fornecedor.address.split(', ')[4] || '' : ''
    });
    setEditandoFornecedorId(fornecedor.id);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleExcluir = async (id) => {
    if (!window.confirm('Tem certeza que deseja excluir este fornecedor?')) {
      return;
    }
    // setMensagem(''); // Não é mais necessário
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
        toast.success(data.mensagem); // Notificação de sucesso
        fetchFornecedores();
      }
    } catch (error) {
      console.error('Erro ao excluir fornecedor:', error);
      toast.error(`Erro ao excluir fornecedor: ${error.message}`); // Notificação de erro
    }
  };

  return (
    <div className="erp-page-container">
      <h2>Gerenciamento de Fornecedores</h2>
      {/* {mensagem && <p className={`message ${mensagem.includes('Erro') ? 'error' : ''}`}>{mensagem}</p>} */}

      {/* Formulário de Adição/Edição de Fornecedor */}
      <div className="form-section highlight-box">
        <h3>{editandoFornecedorId ? 'Editar Fornecedor' : 'Adicionar Novo Fornecedor'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="input-group">
            <label htmlFor="name">Nome do Fornecedor:</label>
            <input
              type="text"
              id="name"
              name="name"
              value={novoFornecedor.name}
              onChange={handleChange}
              placeholder="Nome do Fornecedor"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="contact_person">Pessoa de Contato:</label>
            <input
              type="text"
              id="contact_person"
              name="contact_person"
              value={novoFornecedor.contact_person}
              onChange={handleChange}
              placeholder="Nome da Pessoa de Contato"
            />
          </div>
          <div className="input-group">
            <label htmlFor="phone">Telefone:</label>
            <input
              type="text"
              id="phone"
              name="phone"
              value={novoFornecedor.phone}
              onChange={handleChange}
              placeholder="(XX) XXXX-XXXX"
              maxLength="15"
            />
          </div>
          <div className="input-group">
            <label htmlFor="email">E-mail:</label>
            <input
              type="email"
              id="email"
              name="email"
              value={novoFornecedor.email}
              onChange={handleChange}
              placeholder="email@exemplo.com"
              required
            />
          </div>
          {/* Campos de Endereço Separados */}
          <div className="input-group">
            <label htmlFor="street">Rua:</label>
            <input
              type="text"
              id="street"
              name="street"
              value={novoFornecedor.street}
              onChange={handleChange}
              placeholder="Rua"
            />
          </div>
          <div className="input-group">
            <label htmlFor="number">Número:</label>
            <input
              type="text"
              id="number"
              name="number"
              value={novoFornecedor.number}
              onChange={handleChange}
              placeholder="Número"
            />
          </div>
          <div className="input-group">
            <label htmlFor="neighborhood">Bairro:</label>
            <input
              type="text"
              id="neighborhood"
              name="neighborhood"
              value={novoFornecedor.neighborhood}
              onChange={handleChange}
              placeholder="Bairro"
            />
          </div>
          <div className="input-group">
            <label htmlFor="city">Cidade:</label>
            <input
              type="text"
              id="city"
              name="city"
              value={novoFornecedor.city}
              onChange={handleChange}
              placeholder="Cidade"
            />
          </div>
          <div className="input-group">
            <label htmlFor="state">Estado (UF):</label>
            <input
              type="text"
              id="state"
              name="state"
              value={novoFornecedor.state}
              onChange={handleChange}
              placeholder="Estado (Ex: MG)"
              maxLength="2"
            />
          </div>

          <button type="submit" className="action-button primary">
            {editandoFornecedorId ? 'Atualizar Fornecedor' : 'Adicionar Fornecedor'}
          </button>
          {editandoFornecedorId && (
            <button type="button" className="action-button secondary" onClick={() => {
              setEditandoFornecedorId(null);
              setNovoFornecedor({
                name: '',
                contact_person: '',
                phone: '',
                email: '',
                street: '',
                number: '',
                neighborhood: '',
                city: '',
                state: ''
              });
            }}>
              Cancelar Edição
            </button>
          )}
        </form>
      </div>

      {/* Lista de Fornecedores */}
      <div className="list-section">
        <h3>Lista de Fornecedores</h3>
        {loading ? (
          <p>Carregando fornecedores...</p>
        ) : fornecedores.length === 0 ? (
          <p>Nenhum fornecedor cadastrado.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Contato</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Endereço Completo</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              {fornecedores.map((fornecedor) => (
                <tr key={fornecedor.id}>
                  <td>{fornecedor.id}</td>
                  <td>{fornecedor.name}</td>
                  <td>{fornecedor.contact_person}</td>
                  <td>{fornecedor.phone}</td>
                  <td>{fornecedor.email}</td>
                  <td>{fornecedor.address}</td>
                  <td>
                    <div className="actions-column">
                      <button className="action-button edit" onClick={() => handleEditar(fornecedor)}>Editar</button>
                      <button className="action-button delete" onClick={() => handleExcluir(fornecedor.id)}>Excluir</button>
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
export default FornecedoresPage;