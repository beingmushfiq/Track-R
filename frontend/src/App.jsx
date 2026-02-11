import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import AuthLayout from './layouts/AuthLayout';
import Login from './pages/Login';
import { useAuthStore } from './store/useAuthStore';

// Protected Route Wrapper
const ProtectedRoute = ({ children }) => {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }
  return children;
};

import DashboardLayout from './layouts/DashboardLayout';
import Dashboard from './pages/Dashboard';
import LiveMap from './pages/LiveMap';
import VehicleList from './pages/VehicleList';
import Reports from './pages/Reports';
import Playback from './pages/Playback';
import GeofenceManager from './pages/GeofenceManager';
import AlertRuleManager from './pages/AlertRuleManager';
import PanicEventsDashboard from './pages/PanicEventsDashboard';
import FleetDiagnostics from './pages/FleetDiagnostics';
import EngineDiagnostics from './pages/EngineDiagnostics';
import DailySummary from './pages/DailySummary';

function App() {
  return (
    <Router>
      <Routes>
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<div className="p-8 text-center dark:text-white">Register Page (Coming Soon)</div>} />
        </Route>

        <Route element={
          <ProtectedRoute>
            <DashboardLayout />
          </ProtectedRoute>
        }>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          
          {/* Tracking & Fleet */}
          <Route path="/map" element={<LiveMap />} />
          <Route path="/vehicles" element={<VehicleList />} />
          <Route path="/playback" element={<Playback />} />
          <Route path="/reports" element={<Reports />} />
          
          {/* Management */}
          <Route path="/geofences" element={<GeofenceManager />} />
          <Route path="/alerts" element={<AlertRuleManager />} />
          <Route path="/panic-events" element={<PanicEventsDashboard />} />
          <Route path="/diagnostics" element={<FleetDiagnostics />} />
          <Route path="/vehicles/:id/diagnostics" element={<EngineDiagnostics />} />
          <Route path="/reports/daily" element={<DailySummary />} />
          <Route path="/settings" element={<div className="text-slate-500">Settings Coming Soon</div>} />
        </Route>
      </Routes>
    </Router>
  );
}

export default App;
