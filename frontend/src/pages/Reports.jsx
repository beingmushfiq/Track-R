import { useState } from 'react';
import { Calendar, Download, FileText, TrendingUp } from 'lucide-react';
import { Card, Button, Badge } from '../components/ui';
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';

const Reports = () => {
  const [dateRange, setDateRange] = useState('7days');

  // Mock data for charts
  const distanceData = [
    { date: 'Mon', distance: 245 },
    { date: 'Tue', distance: 312 },
    { date: 'Wed', distance: 189 },
    { date: 'Thu', distance: 278 },
    { date: 'Fri', distance: 356 },
    { date: 'Sat', distance: 198 },
    { date: 'Sun', distance: 267 },
  ];

  const fuelData = [
    { vehicle: 'ABC-123', fuel: 45.2 },
    { vehicle: 'XYZ-789', fuel: 38.7 },
    { vehicle: 'DEF-456', fuel: 52.1 },
    { vehicle: 'GHI-012', fuel: 41.5 },
    { vehicle: 'JKL-345', fuel: 47.8 },
  ];

  const alertsData = [
    { name: 'Overspeed', value: 12, color: '#ef4444' },
    { name: 'Geofence', value: 8, color: '#f59e0b' },
    { name: 'Idle', value: 15, color: '#3b82f6' },
    { name: 'Maintenance', value: 5, color: '#8b5cf6' },
  ];

  const utilizationData = [
    { hour: '00:00', vehicles: 2 },
    { hour: '04:00', vehicles: 1 },
    { hour: '08:00', vehicles: 18 },
    { hour: '12:00', vehicles: 22 },
    { hour: '16:00', vehicles: 20 },
    { hour: '20:00', vehicles: 12 },
    { hour: '24:00', vehicles: 5 },
  ];

  const summaryStats = [
    { label: 'Total Distance', value: '1,845 km', icon: TrendingUp, color: 'indigo' },
    { label: 'Total Trips', value: '156', icon: FileText, color: 'emerald' },
    { label: 'Avg Trip Duration', value: '45 min', icon: Calendar, color: 'blue' },
    { label: 'Fuel Consumed', value: '225.3 L', icon: Download, color: 'orange' },
  ];

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Reports & Analytics</h1>
          <p className="text-slate-500 dark:text-slate-400">Fleet performance insights and metrics</p>
        </div>
        <div className="flex items-center gap-3">
          <select
            value={dateRange}
            onChange={(e) => setDateRange(e.target.value)}
            className="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
          >
            <option value="7days">Last 7 Days</option>
            <option value="30days">Last 30 Days</option>
            <option value="90days">Last 90 Days</option>
            <option value="custom">Custom Range</option>
          </select>
          <Button>
            <Download className="w-4 h-4 mr-2" />
            Export
          </Button>
        </div>
      </div>

      {/* Summary Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {summaryStats.map((stat, index) => (
          <Card key={index}>
            <Card.Body>
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-slate-500 dark:text-slate-400 mb-1">{stat.label}</p>
                  <p className="text-2xl font-bold text-slate-900 dark:text-white">{stat.value}</p>
                </div>
                <div className={`w-12 h-12 rounded-xl flex items-center justify-center bg-${stat.color}-50 dark:bg-${stat.color}-500/10`}>
                  <stat.icon className={`w-6 h-6 text-${stat.color}-600 dark:text-${stat.color}-400`} />
                </div>
              </div>
            </Card.Body>
          </Card>
        ))}
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {/* Distance Trend */}
        <Card>
          <Card.Header>
            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Distance Trend</h3>
          </Card.Header>
          <Card.Body>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={distanceData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#334155" opacity={0.1} />
                <XAxis dataKey="date" stroke="#64748b" />
                <YAxis stroke="#64748b" />
                <Tooltip
                  contentStyle={{
                    backgroundColor: '#1e293b',
                    border: '1px solid #334155',
                    borderRadius: '8px',
                    color: '#fff',
                  }}
                />
                <Legend />
                <Line type="monotone" dataKey="distance" stroke="#6366f1" strokeWidth={2} name="Distance (km)" />
              </LineChart>
            </ResponsiveContainer>
          </Card.Body>
        </Card>

        {/* Fuel Consumption */}
        <Card>
          <Card.Header>
            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Fuel Consumption by Vehicle</h3>
          </Card.Header>
          <Card.Body>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={fuelData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#334155" opacity={0.1} />
                <XAxis dataKey="vehicle" stroke="#64748b" />
                <YAxis stroke="#64748b" />
                <Tooltip
                  contentStyle={{
                    backgroundColor: '#1e293b',
                    border: '1px solid #334155',
                    borderRadius: '8px',
                    color: '#fff',
                  }}
                />
                <Legend />
                <Bar dataKey="fuel" fill="#10b981" name="Fuel (L)" />
              </BarChart>
            </ResponsiveContainer>
          </Card.Body>
        </Card>

        {/* Alert Distribution */}
        <Card>
          <Card.Header>
            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Alert Distribution</h3>
          </Card.Header>
          <Card.Body>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={alertsData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={100}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {alertsData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip
                  contentStyle={{
                    backgroundColor: '#1e293b',
                    border: '1px solid #334155',
                    borderRadius: '8px',
                    color: '#fff',
                  }}
                />
              </PieChart>
            </ResponsiveContainer>
          </Card.Body>
        </Card>

        {/* Vehicle Utilization */}
        <Card>
          <Card.Header>
            <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Vehicle Utilization (24h)</h3>
          </Card.Header>
          <Card.Body>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={utilizationData}>
                <CartesianGrid strokeDasharray="3 3" stroke="#334155" opacity={0.1} />
                <XAxis dataKey="hour" stroke="#64748b" />
                <YAxis stroke="#64748b" />
                <Tooltip
                  contentStyle={{
                    backgroundColor: '#1e293b',
                    border: '1px solid #334155',
                    borderRadius: '8px',
                    color: '#fff',
                  }}
                />
                <Legend />
                <Bar dataKey="vehicles" fill="#3b82f6" name="Active Vehicles" />
              </BarChart>
            </ResponsiveContainer>
          </Card.Body>
        </Card>
      </div>

      {/* Recent Reports Table */}
      <Card>
        <Card.Header>
          <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Recent Generated Reports</h3>
        </Card.Header>
        <Card.Body>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-slate-200 dark:border-slate-800">
                  <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Report Name</th>
                  <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Type</th>
                  <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Date Range</th>
                  <th className="text-left py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Generated</th>
                  <th className="text-right py-3 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">Actions</th>
                </tr>
              </thead>
              <tbody>
                {[
                  { name: 'Fleet Summary', type: 'Summary', range: 'Jan 1 - Jan 7', date: '2 hours ago' },
                  { name: 'Fuel Analysis', type: 'Fuel', range: 'Dec 2024', date: '1 day ago' },
                  { name: 'Trip Report', type: 'Trips', range: 'Last Week', date: '3 days ago' },
                ].map((report, index) => (
                  <tr key={index} className="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td className="py-3 px-4 text-sm font-medium text-slate-900 dark:text-white">{report.name}</td>
                    <td className="py-3 px-4">
                      <Badge variant="primary" size="sm">{report.type}</Badge>
                    </td>
                    <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">{report.range}</td>
                    <td className="py-3 px-4 text-sm text-slate-600 dark:text-slate-400">{report.date}</td>
                    <td className="py-3 px-4 text-right">
                      <Button variant="ghost" size="sm">
                        <Download className="w-4 h-4" />
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card.Body>
      </Card>
    </div>
  );
};

export default Reports;
