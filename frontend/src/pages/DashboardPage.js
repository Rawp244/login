// frontend/src/pages/DashboardPage.js
import React, { useEffect, useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { toast } from 'react-toastify';

// CAMINHOS DE IMPORTAÇÃO CORRETOS PARA SUA ESTRUTURA DE PASTAS:
import MapComponent from '../components/MapComponent';
import SideMenu from '../components/SideMenu';
import '../styles/DashboardPage.css';


function DashboardPage() {
  const navigate = useNavigate();
  const [userProfile, setUserProfile] = useState(null);
  const [userName, setUserName] = useState('Usuário'); // Padrão
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const profile = localStorage.getItem('user_profile');
    const storedUsername = localStorage.getItem('username'); // <--- AGORA OBTÉM O USERNAME

    if (!profile) {
      toast.error("Sessão expirada ou usuário não logado. Faça login novamente.");
      navigate('/login');
      return;
    }

    setUserProfile(profile);

    if (storedUsername) {
      setUserName(storedUsername); // <--- USA O USERNAME DIRETAMENTE PARA EXIBIÇÃO
    }
    setLoading(false);

  }, [navigate]);

  const handleLogout = () => {
    localStorage.removeItem('jwt');
    localStorage.removeItem('user_id');
    localStorage.removeItem('user_profile');
    localStorage.removeItem('username'); // <--- LIMPA O USERNAME TAMBÉM
    localStorage.removeItem('user_email'); // Remover o user_email antigo se existir
    toast.info("Você foi desconectado.");
    navigate('/login');
  };

  if (loading || userProfile === null) {
    return <div className="loading-container">Carregando dashboard...</div>;
  }

  return (
    <div className="dashboard-layout"> {/* Container principal do layout */}
      <SideMenu userProfile={userProfile} /> {/* Inclui o menu lateral aqui */}

      <div className="main-content"> {/* Conteúdo principal do dashboard */}
        <header className="dashboard-header">
          <h1>Bem-vindo, {userName}!</h1> {/* Nome do usuário exibido aqui */}
          <button onClick={handleLogout} className="logout-button">Sair</button>
        </header>

        {/* Admin: Apenas o mapa, sem o painel administrativo na tela principal, como solicitado */}
        {userProfile === 'admin' && (
          <section className="map-section dashboard-section">
              <h2>Encontre a concessionária mais próxima de você!</h2>
              <div className="map-container-wrapper">
                  <MapComponent />
              </div>
          </section>
        )}

        {/* Usuário comum: Apenas o mapa */}
        {userProfile === 'user' && (
          <section className="map-section dashboard-section">
            <h2>Encontre a concessionária mais próxima de você!</h2>
            <div className="map-container-wrapper">
                <MapComponent />
            </div>
          </section>
        )}

      </div>
    </div>
  );
}

export default DashboardPage;