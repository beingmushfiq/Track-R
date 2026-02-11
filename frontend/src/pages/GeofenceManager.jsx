import { useState } from 'react';
import { Plus, Edit, Trash2, MapPin } from 'lucide-react';
import { MapContainer, TileLayer, Polygon, Popup } from 'react-leaflet';
import { Card, Button, Modal, Input, Badge, Select } from '../components/ui';
import 'leaflet/dist/leaflet.css';

const GeofenceManager = () => {
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [selectedGeofence, setSelectedGeofence] = useState(null);

  // Mock geofence data
  const geofences = [
    {
      id: 1,
      name: 'Warehouse Zone',
      type: 'polygon',
      color: '#10b981',
      coordinates: [
        [23.8103, 90.4125],
        [23.8150, 90.4125],
        [23.8150, 90.4200],
        [23.8103, 90.4200],
      ],
      vehicles: 5,
      alerts: 12,
    },
    {
      id: 2,
      name: 'Restricted Area',
      type: 'polygon',
      color: '#ef4444',
      coordinates: [
        [23.8200, 90.4300],
        [23.8250, 90.4300],
        [23.8250, 90.4400],
        [23.8200, 90.4400],
      ],
      vehicles: 2,
      alerts: 8,
    },
  ];

  const geofenceTypes = [
    { value: 'polygon', label: 'Polygon' },
    { value: 'circle', label: 'Circle' },
  ];

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Geofence Manager</h1>
          <p className="text-slate-500 dark:text-slate-400">Create and manage geofences</p>
        </div>
        <Button onClick={() => setIsAddModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          Create Geofence
        </Button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Geofence List */}
        <div className="lg:col-span-1">
          <Card>
            <Card.Header>
              <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Geofences</h3>
            </Card.Header>
            <Card.Body>
              <div className="space-y-3">
                {geofences.map((geofence) => (
                  <div
                    key={geofence.id}
                    className={`p-4 rounded-lg border transition-all cursor-pointer ${
                      selectedGeofence?.id === geofence.id
                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10'
                        : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
                    }`}
                    onClick={() => setSelectedGeofence(geofence)}
                  >
                    <div className="flex items-start justify-between mb-2">
                      <div className="flex items-center gap-2">
                        <div
                          className="w-4 h-4 rounded"
                          style={{ backgroundColor: geofence.color }}
                        />
                        <span className="font-semibold text-slate-900 dark:text-white">
                          {geofence.name}
                        </span>
                      </div>
                      <div className="flex gap-1">
                        <button className="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded">
                          <Edit className="w-4 h-4 text-slate-500" />
                        </button>
                        <button className="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded">
                          <Trash2 className="w-4 h-4 text-red-500" />
                        </button>
                      </div>
                    </div>
                    <div className="flex gap-4 text-sm text-slate-600 dark:text-slate-400">
                      <span>{geofence.vehicles} vehicles</span>
                      <span>{geofence.alerts} alerts</span>
                    </div>
                  </div>
                ))}
              </div>
            </Card.Body>
          </Card>
        </div>

        {/* Map */}
        <div className="lg:col-span-2">
          <Card>
            <Card.Body className="p-0">
              <div className="h-[600px] rounded-2xl overflow-hidden">
                <MapContainer
                  center={[23.8103, 90.4125]}
                  zoom={13}
                  style={{ height: '100%', width: '100%' }}
                >
                  <TileLayer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                  />
                  
                  {/* Render geofences */}
                  {geofences.map((geofence) => (
                    <Polygon
                      key={geofence.id}
                      positions={geofence.coordinates}
                      pathOptions={{
                        color: geofence.color,
                        fillColor: geofence.color,
                        fillOpacity: 0.2,
                        weight: 3,
                      }}
                    >
                      <Popup>
                        <div className="text-sm">
                          <p className="font-semibold">{geofence.name}</p>
                          <p className="text-slate-600">{geofence.vehicles} vehicles</p>
                          <p className="text-slate-600">{geofence.alerts} alerts</p>
                        </div>
                      </Popup>
                    </Polygon>
                  ))}
                </MapContainer>
              </div>
            </Card.Body>
          </Card>
        </div>
      </div>

      {/* Add Geofence Modal */}
      <Modal
        isOpen={isAddModalOpen}
        onClose={() => setIsAddModalOpen(false)}
        title="Create Geofence"
        size="md"
      >
        <div className="space-y-4">
          <Input label="Geofence Name" placeholder="e.g., Warehouse Zone" />
          <Select
            label="Type"
            options={geofenceTypes}
            placeholder="Select type"
          />
          <Input label="Color" type="color" defaultValue="#10b981" />
          <div className="p-4 bg-blue-50 dark:bg-blue-500/10 rounded-lg border border-blue-200 dark:border-blue-800">
            <p className="text-sm text-blue-800 dark:text-blue-400">
              After creating the geofence, you can draw it on the map by clicking points to define the boundary.
            </p>
          </div>
        </div>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setIsAddModalOpen(false)}>
            Cancel
          </Button>
          <Button onClick={() => setIsAddModalOpen(false)}>
            Create & Draw
          </Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default GeofenceManager;
