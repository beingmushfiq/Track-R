import React from 'react';
import { Thermometer, Zap, Gauge, Droplet } from 'lucide-react';

const MetricCard = ({ icon: Icon, label, value, unit, status, trend }) => {
  const getStatusColor = (s) => {
    if (s === 'normal') return 'text-emerald-500 bg-emerald-50';
    if (s === 'warning') return 'text-amber-500 bg-amber-50';
    if (s === 'critical') return 'text-red-500 bg-red-50';
    return 'text-gray-500 bg-gray-50';
  };

  return (
    <div className="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
      <div className="flex items-center gap-3">
        <div className={`p-2 rounded-lg ${getStatusColor(status)}`}>
          <Icon size={20} />
        </div>
        <div>
          <p className="text-sm text-gray-500">{label}</p>
          <div className="flex items-baseline gap-1">
            <span className="text-xl font-bold text-gray-900">{value}</span>
            <span className="text-xs text-gray-400">{unit}</span>
          </div>
        </div>
      </div>
      {trend && (
        <span className={`text-xs ${trend > 0 ? 'text-emerald-500' : 'text-red-500'}`}>
          {trend > 0 ? '+' : ''}{trend}%
        </span>
      )}
    </div>
  );
};

const DiagnosticMetrics = ({ metrics }) => {
  if (!metrics || !metrics.available) {
    return (
      <div className="p-4 bg-gray-50 rounded-lg text-center text-gray-500">
        No active diagnostic data available
      </div>
    );
  }

  // Helper to determine status based on thresholds (simplified logic)
  const getStatus = (val, type) => {
    if (type === 'temp' && val > 105) return 'critical';
    if (type === 'temp' && val > 95) return 'warning';
    if (type === 'voltage' && (val < 12 || val > 15)) return 'warning';
    if (type === 'load' && val > 90) return 'critical';
    if (type === 'load' && val > 80) return 'warning';
    return 'normal';
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <MetricCard
        icon={Gauge}
        label="Engine RPM"
        value={metrics.rpm}
        unit="RPM"
        status="normal"
      />
      <MetricCard
        icon={Thermometer}
        label="Coolant Temp"
        value={metrics.coolant_temp}
        unit="°C"
        status={getStatus(metrics.coolant_temp, 'temp')}
      />
      <MetricCard
        icon={Zap}
        label="Battery"
        value={metrics.battery_voltage}
        unit="V"
        status={getStatus(metrics.battery_voltage, 'voltage')}
      />
      <MetricCard
        icon={Droplet}
        label="Fuel Level"
        value={metrics.fuel_level}
        unit="%"
        status={metrics.fuel_level < 10 ? 'warning' : 'normal'}
      />
    </div>
  );
};

export default DiagnosticMetrics;
