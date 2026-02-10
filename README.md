# Track-R: Enterprise GPS Tracking Platform

**Track-R** is a scalable, multi-tenant GPS tracking SaaS platform designed for commercial fleet management. It features a high-performance Node.js ingestion engine for real-time device communication and a robust Laravel backend for business logic, reporting, and API management.

## 🚀 Features

- **Real-time Tracking**: Live vehicle positions with low latency updates via WebSockets.
- **Multi-Tenancy**: Built-in support for B2B SaaS (Super Admin -> Resellers -> Companies).
- **Device Agnostic**: Supports multiple protocols including GT06, HT02, Concox, and a generic adapter.
- **Geofencing**: Create circular and polygon geofences with entry/exit alerts.
- **Advanced Reporting**: Trip history, fuel usage, stops, and distance reports.
- **Alert System**: Instant notifications for overspeeding, harsh braking, device offline, and more.
- **RBAC**: Granular permission system for users and roles.
- **API-First**: Full REST API for mobile app integration (Flutter).

## 🏗 Architecture

The system follows a hybrid architecture to ensure scalability and reliability:

- **Ingestion Layer (Node.js)**: Handles TCP/UDP connections from thousands of GPS devices concurrently. Parses raw binary data and pushes standardized JSON to Redis.
- **Queue Layer (Redis)**: Acts as a buffer between the ingestion layer and the backend, ensuring data isn't lost during high load.
- **Backend Layer (Laravel 11)**: Consumes data from Redis, persists it to MySQL (with spatial indexing), and processes business logic (alerts, geofences).
- **Frontend**: Built with Laravel Blade, Alpine.js, and Tailwind CSS.
- **Database**: MySQL 8.0 with spatial extensions for location queries.

## 🛠 Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Device Server**: Node.js, `net`, `dgram`
- **Database**: MySQL 8.0, Redis
- **Frontend**: Blade, Alpine.js, Tailwind CSS, Leaflet.js
- **Infrastructure**: Docker, Nginx

## 📂 Project Structure

```bash
Track-R/
├── backend/            # Laravel application (API, Web, Business Logic)
├── device-server/      # Node.js TCP/UDP Ingestion Service
├── mobile/             # Flutter Mobile Application
├── docker/             # Docker configuration for production
└── docs/               # Detailed documentation files
```

## ⚡ Getting Started

### Prerequisites

- PHP 8.2+ & Composer
- Node.js 18+ & NPM
- MySQL 8.0
- Redis

### Local Development Setup

1.  **Clone the repository**

    ```bash
    git clone https://github.com/yourusername/Track-R.git
    cd Track-R
    ```

2.  **Setup Backend**

    ```bash
    cd backend
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    ```

3.  **Setup Device Server**

    ```bash
    cd ../device-server
    npm install
    cp .env.example .env
    ```

4.  **Run Services**
    - **Backend**: `php artisan serve`
    - **Queue Worker**: `php artisan queue:work`
    - **Device Server**: `npm run dev`

## 📖 Documentation

- [System Architecture](docs/ARCHITECTURE.md)
- [Database Schema](docs/DATABASE_SCHEMA.md)
- [API Documentation](docs/API.md) (Coming Soon)

## 📄 License

This project is proprietary software. All rights reserved.
