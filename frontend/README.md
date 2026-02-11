# Track-R Frontend

Modern, responsive web interface for the Track-R GPS tracking platform built with React and Vite.

## Tech Stack

- **Build Tool**: Vite 7.3.1
- **Framework**: React 19.2.0
- **Styling**: Tailwind CSS 3.4.17
- **State Management**: Zustand 5.0.11
- **Routing**: React Router 7.13.0
- **Maps**: React Leaflet 5.0.0 + Leaflet 1.9.4
- **Charts**: Recharts 3.7.0
- **HTTP Client**: Axios 1.13.5
- **Icons**: Lucide React

## Getting Started

### Prerequisites

- Node.js 18+ and npm
- Backend API running at `http://localhost:8000`

### Installation

```bash
# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start development server
npm run dev
```

The app will be available at `http://localhost:5173`

### Environment Variables

Create a `.env` file in the root directory:

```env
VITE_API_URL=http://localhost:8000
VITE_BROADCAST_URL=http://localhost:8000
VITE_APP_ENV=development
VITE_API_DEBUG=true
```

## Features

### Authentication
- Login/Register with JWT tokens
- Protected routes
- Auto token refresh
- Persistent sessions

### Dashboard
- Fleet overview with stat cards
- Active vehicles count
- Total distance traveled
- Active alerts
- Average speed metrics

### Live Tracking
- Real-time vehicle positions on map
- Custom vehicle markers
- Status indicators (Moving/Idle/Offline)
- Vehicle information popups
- OpenStreetMap integration

### Vehicle Management
- Vehicle list with search
- CRUD operations
- Status badges
- Driver assignment
- Battery monitoring

### Reports & Analytics
- Interactive charts (Line, Bar, Pie)
- Distance trend analysis
- Fuel consumption reports
- Alert distribution
- Vehicle utilization (24h)
- Date range filters
- Export functionality

## Project Structure

```
src/
├── assets/         # Images, fonts
├── components/     # Reusable UI components
│   ├── ui/        # Button, Input, Card, Modal, Badge
│   └── layout/    # Sidebar, Topbar
├── layouts/       # Page layouts (Auth, Dashboard)
├── pages/         # Page components
│   ├── Login.jsx
│   ├── Dashboard.jsx
│   ├── LiveMap.jsx
│   ├── VehicleList.jsx
│   └── Reports.jsx
├── services/      # API integration
│   ├── api.js
│   ├── authService.js
│   ├── vehicleService.js
│   └── trackingService.js
├── store/         # Zustand state management
│   ├── useAuthStore.js
│   ├── useVehicleStore.js
│   └── useTrackingStore.js
├── hooks/         # Custom React hooks
├── utils/         # Helper functions
└── App.jsx        # Main app component
```

## Default Credentials

After running `php artisan migrate:fresh --seed` on the backend:

- **Email**: admin@demotransport.com
- **Password**: password

## Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint

## Dark Mode

The app supports dark mode with automatic theme detection and manual toggle. Theme preference is persisted in localStorage.

## API Integration

All API calls are proxied through Vite to avoid CORS issues:
- `/api/*` → `http://localhost:8000/api/*`
- `/broadcasting/*` → `http://localhost:8000/broadcasting/*`

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

Proprietary - Track-R Platform
