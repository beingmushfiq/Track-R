import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import { useEffect, useState } from 'react';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default marker icons in React-Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const LiveMap = () => {
  const [vehicles, setVehicles] = useState([
    { id: 1, name: 'Vehicle ABC-123', lat: 23.8103, lng: 90.4125, status: 'moving', speed: 45 },
    { id: 2, name: 'Vehicle XYZ-789', lat: 23.7805, lng: 90.4258, status: 'idle', speed: 0 },
    { id: 3, name: 'Vehicle DEF-456', lat: 23.7925, lng: 90.4078, status: 'moving', speed: 60 },
  ]);

  const center = [23.8103, 90.4125]; // Dhaka, Bangladesh

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Live Tracking</h1>
        <p className="text-slate-500 dark:text-slate-400">Real-time vehicle locations and status</p>
      </div>

      <div className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden">
        <div className="h-[calc(100vh-200px)]">
          <MapContainer 
            center={center} 
            zoom={13} 
            style={{ height: '100%', width: '100%' }}
            className="z-0"
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            {vehicles.map((vehicle) => (
              <Marker key={vehicle.id} position={[vehicle.lat, vehicle.lng]}>
                <Popup>
                  <div className="p-2">
                    <p className="font-semibold text-slate-900">{vehicle.name}</p>
                    <p className="text-sm text-slate-600">Status: <span className={vehicle.status === 'moving' ? 'text-emerald-600' : 'text-orange-600'}>{vehicle.status}</span></p>
                    <p className="text-sm text-slate-600">Speed: {vehicle.speed} km/h</p>
                  </div>
                </Popup>
              </Marker>
            ))}
          </MapContainer>
        </div>
      </div>
    </div>
  );
};

export default LiveMap;
