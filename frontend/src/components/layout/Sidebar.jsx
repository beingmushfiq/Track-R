import { NavLink } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Map as MapIcon, 
  Car, 
  History, 
  FileBarChart, 
  MapPin,
  Bell,
  AlertCircle,
  Activity,
  Settings, 
  LogOut,
  FileText
} from 'lucide-react';
import { useAuthStore } from '../../store/useAuthStore';
import { clsx } from 'clsx';

const Sidebar = () => {
  const logout = useAuthStore((state) => state.logout);

  const navItems = [
    { icon: LayoutDashboard, label: 'Dashboard', path: '/dashboard' },
    { icon: MapIcon, label: 'Live Map', path: '/map' },
    { icon: Car, label: 'Vehicles', path: '/vehicles' },
    { icon: History, label: 'Playback', path: '/playback' },
    { icon: FileBarChart, label: 'Reports', path: '/reports' },
    { icon: FileText, label: 'Daily Summary', path: '/reports/daily' },
    { icon: MapPin, label: 'Geofences', path: '/geofences' },
    { icon: Bell, label: 'Alerts', path: '/alerts' },
    { icon: AlertCircle, label: 'Panic Events', path: '/panic-events' },
    { icon: Activity, label: 'Diagnostics', path: '/diagnostics' },
    { icon: Settings, label: 'Settings', path: '/settings' },
  ];

  return (
    <aside className="w-64 h-screen bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-colors duration-200 fixed left-0 top-0 z-30">
      {/* Logo Area */}
      <div className="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-800">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
            <MapIcon className="w-5 h-5 text-white" />
          </div>
          <span className="text-xl font-display font-bold text-slate-900 dark:text-white tracking-tight">
            Track<span className="text-indigo-600">-R</span>
          </span>
        </div>
      </div>

      {/* Navigation */}
      <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
        {navItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) => clsx(
              "flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group text-sm font-medium",
              isActive 
                ? "bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400" 
                : "text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200"
            )}
          >
            <item.icon className="w-5 h-5 opacity-70 group-hover:opacity-100 transition-opacity" />
            {item.label}
          </NavLink>
        ))}
      </nav>

      {/* User / Logout */}
      <div className="p-4 border-t border-slate-100 dark:border-slate-800">
        <button 
          onClick={logout}
          className="flex items-center gap-3 w-full px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl transition-colors"
        >
          <LogOut className="w-5 h-5" />
          Sign Out
        </button>
      </div>
    </aside>
  );
};

export default Sidebar;
