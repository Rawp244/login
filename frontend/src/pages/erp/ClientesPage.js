import React, { useState, useEffect } from 'react';
import { toast } from 'react-toastify'; // Importe o toast
import '../../styles/erp/ClientesPage.css';

function ClientesPage() {
  const [clientes, setClientes] = useState([]);
  const [novoCliente, setNovoCliente] = useState({
    name: '',
    email: '',
    phone: '',
    street: '',
    number: '',
    neighborhood: '',
    city: '',
    state: '',
    cpf: '',
    birth_date: ''
  });
  const [editandoClienteId, setEditandoClienteId] = useState(null);
  // const [mensagem, setMensagem] = useState(''); // Não é mais necessário
  const [loading, setLoading] = useState(true);

  const API_URL = 'http://localhost/loginmvc/backend/controller/erp/ClienteController.php';

  useEffect(() => {
    fetchClientes();
  }, []);

  const fetchClientes = async () => {
    setLoading(true);
    // setMensagem(''); // Não é mais necessário
    try {
      const response = await fetch(API_URL);

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Erro HTTP! Status: ${response.status}, Resposta: ${errorText}`);
      }

      const data = await response.json();
      setClientes(data);
    } catch (error) {
      console.error('Erro ao carregar clientes:', error);
      toast.error(`Erro ao conectar com o servidor para carregar clientes. Detalhes: ${error.message}`); // Notificação de erro
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
    } else if (name === 'cpf') {
        formattedValue = value.replace(/\D/g, '');
        if (formattedValue.length > 9) {
            formattedValue = `${formattedValue.substring(0, 3)}.${formattedValue.substring(3, 6)}.${formattedValue.substring(6, 9)}-${formattedValue.substring(9, 11)}`;
        } else if (formattedValue.length > 6) {
            formattedValue = `${formattedValue.substring(0, 3)}.${formattedValue.substring(3, 6)}.${formattedValue.substring(6)}`;
        } else if (formattedValue.length > 3) {
            formattedValue = `${formattedValue.substring(0, 3)}.${formattedValue.substring(3)}`;
        }
    }

    setNovoCliente(prevState => ({ ...prevState, [name]: formattedValue }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    // setMensagem(''); // Não é mais necessário

    const addressToSend = [
      novoCliente.street,
      novoCliente.number,
      novoCliente.neighborhood,
      novoCliente.city,
      novoCliente.state
    ].filter(Boolean).join(', ');

    const clientDataToSend = {
      name: novoCliente.name,
      email: novoCliente.email,
      phone: novoCliente.phone,
      address: addressToSend,
      cpf: novoCliente.cpf.replace(/\D/g, ''),
      birth_date: novoCliente.birth_date
    };

    const method = editandoClienteId ? 'PUT' : 'POST';
    const url = editandoClienteId ? `${API_URL}/${editandoClienteId}` : API_URL;

    try {
      const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(clientDataToSend),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.erro || `Erro HTTP! Status: ${response.status}`);
      }

      const data = await response.json();

      toast.success(data.mensagem); // Notificação de sucesso
      setNovoCliente({ name: '', email: '', phone: '', street: '', number: '', neighborhood: '', city: '', state: '', cpf: '', birth_date: '' });
      setEditandoClienteId(null);
      fetchClientes();
    } catch (error) {
      console.error('Erro na operação de cliente:', error);
      toast.error(`Erro na operação: ${error.message}`); // Notificação de erro
    }
  };

  const handleEditar = (cliente) => {
    setNovoCliente({
      name: cliente.name,
      email: cliente.email,
      phone: cliente.phone,
      street: cliente.address ? cliente.address.split(', ')[0] || '' : '',
      number: cliente.address ? cliente.address.split(', ')[1] || '' : '',
      neighborhood: cliente.address ? cliente.address.split(', ')[2] || '' : '',
      city: cliente.address ? cliente.address.split(', ')[3] || '' : '',
      state: cliente.address ? cliente.address.split(', ')[4] || '' : '',
      cpf: cliente.cpf ? cliente.cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : '',
      birth_date: cliente.birth_date ? cliente.birth_date.substring(0, 10) : ''
    });
    setEditandoClienteId(cliente.id);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleExcluir = async (id) => {
    if (!window.confirm('Tem certeza que deseja excluir este cliente?')) {
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
        fetchClientes();
      }
    } catch (error) {
      console.error('Erro ao excluir cliente:', error);
      toast.error(`Erro ao excluir cliente: ${error.message}`); // Notificação de erro
    }
  };

  const formatarCpfParaExibicao = (cpf) => {
    if (!cpf) return '';
    const cleaned = cpf.replace(/\D/g, '');
    if (cleaned.length === 11) {
      return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
    return cpf;
  };

  return (
    <div className="erp-page-container">
      <h2>Gerenciamento de Clientes</h2>
      {/* {mensagem && <p className={`message ${mensagem.includes('Erro') ? 'error' : ''}`}>{mensagem}</p>} */}

      {/* Formulário de Adição/Edição de Cliente */}
      <div className="form-section highlight-box">
        <h3>{editandoClienteId ? 'Editar Cliente' : 'Adicionar Novo Cliente'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="input-group">
            <label htmlFor="name">Nome do Cliente:</label>
            <input
              type="text"
              id="name"
              name="name"
              value={novoCliente.name}
              onChange={handleChange}
              placeholder="Nome do Cliente"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="email">E-mail:</label>
            <input
              type="email"
              id="email"
              name="email"
              value={novoCliente.email}
              onChange={handleChange}
              placeholder="email@exemplo.com"
              required
            />
          </div>
          <div className="input-group">
            <label htmlFor="phone">Telefone:</label>
            <input
              type="text"
              id="phone"
              name="phone"
              value={novoCliente.phone}
              onChange={handleChange}
              placeholder="(XX) XXXX-XXXX"
              maxLength="15"
            />
          </div>
          {/* Campos de Endereço Separados - Padrão Fornecedores */}
          <div className="input-group">
            <label htmlFor="street">Rua:</label>
            <input
              type="text"
              id="street"
              name="street"
              value={novoCliente.street}
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
              value={novoCliente.number}
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
              value={novoCliente.neighborhood}
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
              value={novoCliente.city}
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
              value={novoCliente.state}
              onChange={handleChange}
              placeholder="Estado (Ex: MG)"
              maxLength="2"
            />
          </div>
          {/* Novos Campos: CPF e Data de Nascimento */}
          <div className="input-group">
            <label htmlFor="cpf">CPF:</label>
            <input
              type="text"
              id="cpf"
              name="cpf"
              value={novoCliente.cpf}
              onChange={handleChange}
              placeholder="000.000.000-00"
              maxLength="14"
            />
          </div>
          <div className="input-group">
            <label htmlFor="birth_date">Data de Nascimento:</label>
            <input
              type="date"
              id="birth_date"
              name="birth_date"
              value={novoCliente.birth_date}
              onChange={handleChange}
            />
          </div>
          <button type="submit" className="action-button primary">
            {editandoClienteId ? 'Atualizar Cliente' : 'Adicionar Cliente'}
          </button>
          {editandoClienteId && (
            <button type="button" className="action-button secondary" onClick={() => {
              setEditandoClienteId(null);
              setNovoCliente({ name: '', email: '', phone: '', street: '', number: '', neighborhood: '', city: '', state: '', cpf: '', birth_date: '' });
            }}>
              Cancelar Edição
            </button>
          )}
        </form>
      </div>

      {/* Lista de Clientes */}
      <div className="list-section">
        <h3>Lista de Clientes</h3>
        {loading ? (
          <p>Carregando clientes...</p>
        ) : clientes.length === 0 ? (
          <p>Nenhum cliente cadastrado.</p>
        ) : (
          <table className="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Endereço</th>
                <th>CPF</th>
                <th>Nascimento</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              {clientes.map((cliente) => (
                <tr key={cliente.id}>
                  <td>{cliente.id}</td>
                  <td>{cliente.name}</td>
                  <td>{cliente.email}</td>
                  <td>{cliente.phone}</td>
                  <td>{cliente.address}</td>
                  <td>{formatarCpfParaExibicao(cliente.cpf)}</td>
                  <td>{cliente.birth_date ? new Date(cliente.birth_date).toLocaleDateString('pt-BR') : ''}</td>
                  <td>
                    <div className="actions-column">
                      <button className="action-button edit" onClick={() => handleEditar(cliente)}>Editar</button>
                      <button className="action-button delete" onClick={() => handleExcluir(cliente.id)}>Excluir</button>
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

export default ClientesPage;