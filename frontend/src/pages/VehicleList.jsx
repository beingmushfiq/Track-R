import { useState } from 'react';
import { Search, Plus, Car, MapPin, Clock, Battery } from 'lucide-react';
import { Card, Button, Badge, Modal, Input } from '../components/ui';

const VehicleList = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);

  // Mock vehicle data
  const vehicles = [
    { 
      id: 1, 
      name: 'Vehicle ABC-123', 
      type: 'Sedan',
      driver: 'John Doe',
      status: 'moving', 
      speed: 45,
      location: 'Dhaka, Bangladesh',
      battery: 85,
      lastUpdate: '2 min ago'
    },
    { 
      id: 2, 
      name: 'Vehicle XYZ-789', 
      type: 'SUV',
      driver: 'Jane Smith',
      status: 'idle', 
      speed: 0,
      location: 'Chittagong, Bangladesh',
      battery: 62,
      lastUpdate: '5 min ago'
    },
    { 
      id: 3, 
      name: 'Vehicle DEF-456', 
      type: 'Truck',
      driver: 'Mike Johnson',
      status: 'offline', 
      speed: 0,
      location: 'Unknown',
      battery: 15,
      lastUpdate: '2 hours ago'
    },
  ];

  const getStatusBadge = (status) => {
    const statusConfig = {
      moving: { variant: 'success', label: 'Moving' },
      idle: { variant: 'warning', label: 'Idle' },
      offline: { variant: 'danger', label: 'Offline' },
    };
    const config = statusConfig[status] || statusConfig.offline;
    return <Badge variant={config.variant}>{config.label}</Badge>;
  };

  const filteredVehicles = vehicles.filter(v => 
    v.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    v.driver.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Vehicles</h1>
          <p className="text-slate-500 dark:text-slate-400">Manage your fleet vehicles</p>
        </div>
        <Button onClick={() => setIsAddModalOpen(true)}>
          <Plus className="w-4 h-4 mr-2" />
          Add Vehicle
        </Button>
      </div>

      {/* Search */}
      <div className="mb-6">
        <div className="relative max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input
            type="text"
            placeholder="Search vehicles or drivers..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500"
          />
        </div>
      </div>

      {/* Vehicle Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {filteredVehicles.map((vehicle) => (
          <Card key={vehicle.id} hover>
            <Card.Body>
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl flex items-center justify-center">
                    <Car className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                  </div>
                  <div>
                    <h3 className="font-semibold text-slate-900 dark:text-white">{vehicle.name}</h3>
                    <p className="text-sm text-slate-500 dark:text-slate-400">{vehicle.type}</p>
                  </div>
                </div>
                {getStatusBadge(vehicle.status)}
              </div>

              <div className="space-y-2 mb-4">
                <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                  <MapPin className="w-4 h-4" />
                  <span className="truncate">{vehicle.location}</span>
                </div>
                <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                  <Clock className="w-4 h-4" />
                  <span>Last update: {vehicle.lastUpdate}</span>
                </div>
                <div className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                  <Battery className="w-4 h-4" />
                  <span>Battery: {vehicle.battery}%</span>
                </div>
              </div>

              <div className="pt-4 border-t border-slate-100 dark:border-slate-800">
                <p className="text-sm text-slate-500 dark:text-slate-400">
                  Driver: <span className="font-medium text-slate-900 dark:text-white">{vehicle.driver}</span>
                </p>
              </div>
            </Card.Body>
            <Card.Footer>
              <div className="flex gap-2">
                <Button variant="secondary" size="sm" className="flex-1">Track</Button>
                <Button variant="ghost" size="sm" className="flex-1">Details</Button>
              </div>
            </Card.Footer>
          </Card>
        ))}
      </div>

      {/* Add Vehicle Modal */}
      <Modal
        isOpen={isAddModalOpen}
        onClose={() => setIsAddModalOpen(false)}
        title="Add New Vehicle"
        size="md"
      >
        <div className="space-y-4">
          <Input label="Vehicle Name" placeholder="e.g., ABC-123" />
          <Input label="Vehicle Type" placeholder="e.g., Sedan, SUV, Truck" />
          <Input label="Driver Name" placeholder="e.g., John Doe" />
          <Input label="Device IMEI" placeholder="Enter device IMEI" />
        </div>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setIsAddModalOpen(false)}>
            Cancel
          </Button>
          <Button onClick={() => setIsAddModalOpen(false)}>
            Add Vehicle
          </Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default VehicleList;
