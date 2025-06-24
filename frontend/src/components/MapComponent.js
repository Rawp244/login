// frontend/src/components/MapComponent.js
import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import 'leaflet/dist/leaflet.css'; // Importe o CSS do Leaflet
import { Icon } from 'leaflet'; // Para usar ícones personalizados
import { toast } from 'react-toastify';

// Correção para ícones padrão do Leaflet no Webpack/React
// (Leaflet usa ícones padrão que o Webpack não encontra sem essa correção)
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

const defaultIcon = new Icon({
  iconRetinaUrl,
  iconUrl,
  shadowUrl,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
});
// Atribui o ícone padrão ao Leaflet
Icon.Default.mergeOptions({
  iconRetinaUrl: iconRetinaUrl,
  iconUrl: iconUrl,
  shadowUrl: shadowUrl,
});
// Fim da correção de ícones

function MapComponent() {
  const [concessionarias, setConcessionarias] = useState([]);
  const [loading, setLoading] = useState(true);
  // Centro inicial do mapa (Centro geográfico do Brasil)
  const [mapCenter, setMapCenter] = useState([-14.235, -51.9253]);
  const [mapZoom, setMapZoom] = useState(4); // Zoom inicial para o Brasil

  const API_URL_CONCESSIONARIAS = 'http://localhost/loginmvc/backend/controller/erp/ConcessionariaVWController.php';

  // Função para obter o JWT do localStorage
  const getAuthToken = () => {
    return localStorage.getItem('jwt');
  };

  useEffect(() => {
    const fetchConcessionarias = async () => {
      setLoading(true);
      try {
        const token = getAuthToken();
        const response = await fetch(API_URL_CONCESSIONARIAS, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`Erro HTTP! Status: ${response.status}, Resposta: ${errorText}`);
        }

        const data = await response.json();
        
        // Filtra concessionárias que têm latitude e longitude válidas
        const validConcessionarias = data.filter(c => 
            c.latitude !== null && c.longitude !== null && 
            parseFloat(c.latitude) !== 0.0 && parseFloat(c.longitude) !== 0.0
        );
        
        setConcessionarias(validConcessionarias);

        // Opcional: Ajustar o centro do mapa para Divinópolis se houver dados
        // Latitude e Longitude de Divinópolis, MG: -20.147778, -44.908056
        if (validConcessionarias.length > 0) {
            setMapCenter([-20.147778, -44.908056]); // Centraliza em Divinópolis
            setMapZoom(8); // Um zoom mais próximo para a região de MG
        }

      } catch (error) {
        console.error('Erro ao carregar concessionárias:', error);
        toast.error(`Erro ao carregar concessionárias para o mapa. Detalhes: ${error.message}`);
      } finally {
        setLoading(false);
      }
    };

    fetchConcessionarias();
  }, []);

  if (loading) {
    return <p>Carregando mapa e concessionárias...</p>;
  }

  if (concessionarias.length === 0) {
    return <p>Nenhuma concessionária com coordenadas válidas encontrada para exibir no mapa.</p>;
  }

  return (
    <MapContainer center={mapCenter} zoom={mapZoom} scrollWheelZoom={true} className="dashboard-map">
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
      />
      {concessionarias.map(concessionaria => (
        <Marker
          key={concessionaria.id}
          position={[parseFloat(concessionaria.latitude), parseFloat(concessionaria.longitude)]}
          icon={defaultIcon}
        >
          <Popup>
            <strong>{concessionaria.nome}</strong><br />
            {concessionaria.endereco}, {concessionaria.cidade} - {concessionaria.estado}<br />
            {concessionaria.telefone && `Tel: ${concessionaria.telefone}`}<br />
            {concessionaria.site && <a href={concessionaria.site} target="_blank" rel="noopener noreferrer">Visitar Site</a>}
          </Popup>
        </Marker>
      ))}
    </MapContainer>
  );
}

export default MapComponent;