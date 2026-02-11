import React from 'react';
import { Car, MapPin, AlertTriangle, TrendingUp } from 'lucide-react';

const StatCard = ({ icon: Icon, label, value, change, changeType = 'positive' }) => {
  return (
    <div className="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow duration-200">
      <div className="flex items-start justify-between mb-4">
        <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
          Icon === Car ? 'bg-indigo-50 dark:bg-indigo-500/10' :
          Icon === MapPin ? 'bg-emerald-50 dark:bg-emerald-500/10' :
          Icon === AlertTriangle ? 'bg-red-50 dark:bg-red-500/10' :
          'bg-blue-50 dark:bg-blue-500/10'
        }`}>
          <Icon className={`w-6 h-6 ${
            Icon === Car ? 'text-indigo-600 dark:text-indigo-400' :
            Icon === MapPin ? 'text-emerald-600 dark:text-emerald-400' :
            Icon === AlertTriangle ? 'text-red-600 dark:text-red-400' :
            'text-blue-600 dark:text-blue-400'
          }`} />
        </div>
        {change && (
          <div className={`flex items-center gap-1 text-sm font-medium ${
            changeType === 'positive' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'
          }`}>
            <TrendingUp className="w-4 h-4" />
            {change}
          </div>
        )}
      </div>
      <p className="text-sm text-slate-500 dark:text-slate-400 mb-1">{label}</p>
      <p className="text-3xl font-bold text-slate-900 dark:text-white">{value}</p>
    </div>
  );
};

const Dashboard = () => {
  // Mock data - will be replaced with real API calls
  const stats = [
    { icon: Car, label: 'Active Vehicles', value: '24', change: '+12%', changeType: 'positive' },
    { icon: MapPin, label: 'Total Distance', value: '1,245 km', change: '+8%', changeType: 'positive' },
    { icon: AlertTriangle, label: 'Active Alerts', value: '3', change: '-5%', changeType: 'positive' },
    { icon: TrendingUp, label: 'Avg Speed', value: '65 km/h', change: '+3%', changeType: 'positive' },
  ];

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Dashboard</h1>
        <p className="text-slate-500 dark:text-slate-400">Overview of your fleet status and metrics</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {stats.map((stat, index) => (
          <StatCard key={index} {...stat} />
        ))}
      </div>

      {/* Placeholder for Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Fleet Activity</h3>
          <div className="h-64 flex items-center justify-center text-slate-400 dark:text-slate-600">
            Chart Coming Soon
          </div>
        </div>
        
        <div className="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <h3 className="text-lg font-semibold text-slate-900 dark:text-white mb-4">Recent Alerts</h3>
          <div className="space-y-3">
            {[1, 2, 3].map((i) => (
              <div key={i} className="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                <div className="flex-1">
                  <p className="text-sm font-medium text-slate-900 dark:text-white">Overspeed Alert</p>
                  <p className="text-xs text-slate-500 dark:text-slate-400">Vehicle ABC-123 • 2 min ago</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
