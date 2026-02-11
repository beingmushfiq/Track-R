import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Activity, AlertTriangle, CheckCircle, Search, Filter } from 'lucide-react';
import api from '../services/api';
import HealthScoreGauge from '../components/HealthScoreGauge';

const FleetDiagnostics = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState(null);
  const [filter, setFilter] = useState('all'); // all, critical, warning, healthy
  const [search, setSearch] = useState('');

  useEffect(() => {
    fetchSummary();
  }, []);

  const fetchSummary = async () => {
    try {
      setLoading(true);
      const response = await api.get('/diagnostics/summary');
      setData(response.data);
    } catch (error) {
      console.error('Failed to fetch fleet diagnostics:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredVehicles = data?.health_scores?.filter((v) => {
    const matchesSearch = v.vehicle_name.toLowerCase().includes(search.toLowerCase());
    const matchesFilter = filter === 'all' || 
      (filter === 'critical' && v.status === 'critical') ||
      (filter === 'warning' && (v.status === 'poor' || v.status === 'fair')) ||
      (filter === 'healthy' && (v.status === 'good' || v.status === 'excellent'));
    
    return matchesSearch && matchesFilter;
  }) || [];

  if (loading) {
    return (
      <div className="p-6 flex justify-center items-center h-96">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>
    );
  }

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Fleet Diagnostics</h1>
          <p className="text-gray-500">Monitor health status of {data?.total_vehicles} active vehicles</p>
        </div>
        
        <div className="flex gap-2">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={20} />
            <input 
              type="text" 
              placeholder="Search vehicles..." 
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full md:w-64"
            />
          </div>
          <select 
            value={filter} 
            onChange={(e) => setFilter(e.target.value)}
            className="px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
          >
            <option value="all">All Status</option>
            <option value="critical">Critical Only</option>
            <option value="warning">Warnings</option>
            <option value="healthy">Healthy</option>
          </select>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-500">Vehicles with Issues</p>
            <p className="text-3xl font-bold text-gray-900 mt-2">{data?.vehicles_with_issues}</p>
          </div>
          <div className="p-3 bg-red-50 rounded-lg text-red-600">
            <AlertTriangle size={24} />
          </div>
        </div>

        <div className="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-500">Total Active DTCs</p>
            <p className="text-3xl font-bold text-gray-900 mt-2">{data?.active_codes_total}</p>
          </div>
          <div className="p-3 bg-amber-50 rounded-lg text-amber-600">
            <Activity size={24} />
          </div>
        </div>

        <div className="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-500">Healthy Fleet Ratio</p>
            <p className="text-3xl font-bold text-gray-900 mt-2">
              {Math.round(((data?.total_vehicles - data?.vehicles_with_issues) / data?.total_vehicles) * 100)}%
            </p>
          </div>
          <div className="p-3 bg-emerald-50 rounded-lg text-emerald-600">
            <CheckCircle size={24} />
          </div>
        </div>
      </div>

      {/* Vehicle Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {filteredVehicles.map((vehicle) => (
          <div 
            key={vehicle.vehicle_id}
            onClick={() => navigate(`/vehicles/${vehicle.vehicle_id}/diagnostics`)}
            className="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow cursor-pointer group"
          >
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                  {vehicle.vehicle_name}
                </h3>
                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize mt-1
                  ${vehicle.status === 'excellent' ? 'bg-emerald-100 text-emerald-800' : 
                    vehicle.status === 'good' ? 'bg-blue-100 text-blue-800' :
                    vehicle.status === 'critical' ? 'bg-red-100 text-red-800' : 
                    'bg-amber-100 text-amber-800'}`}>
                  {vehicle.status}
                </span>
              </div>
              {/* Mini Gauge */}
              <div className="w-12 h-12 relative flex items-center justify-center">
                 <div className="text-xs font-bold" style={{
                   color: vehicle.score >= 90 ? '#10b981' : vehicle.score >= 60 ? '#f59e0b' : '#ef4444'
                 }}>
                   {vehicle.score}
                 </div>
              </div>
            </div>
            
            <div className="flex items-center text-sm text-gray-500 font-medium">
              View Engine Diagnostics →
            </div>
          </div>
        ))}

        {filteredVehicles.length === 0 && (
          <div className="col-span-full py-12 text-center text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
            No vehicles found matching criteria
          </div>
        )}
      </div>
    </div>
  );
};

export default FleetDiagnostics;
