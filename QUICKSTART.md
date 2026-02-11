# Track-R Platform - Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0
- Redis (optional, for caching)

---

## Step 1: Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Configure database in .env
DB_DATABASE=trackr
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations and seed test data
php artisan migrate:fresh --seed

# Start the server
php artisan serve
```

Backend will be available at: **http://localhost:8000**

---

## Step 2: Frontend Setup

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Setup environment
cp .env.example .env

# Start development server
npm run dev
```

Frontend will be available at: **http://localhost:5173**

---

## Step 3: Login

Open http://localhost:5173 in your browser and login with:

- **Email**: `admin@demotransport.com`
- **Password**: `password`

---

## 🎉 You're Ready!

Explore the features:
- 📊 **Dashboard** - Fleet overview and metrics
- 🗺️ **Live Map** - Real-time vehicle tracking
- 🚗 **Vehicles** - Manage your fleet
- 📈 **Reports** - Analytics and insights

---

## 📚 Next Steps

- Read [PROJECT_SUMMARY.md](./PROJECT_SUMMARY.md) for complete documentation
- Check [frontend/README.md](./frontend/README.md) for frontend details
- Review API routes in `backend/routes/api.php`
- Run tests: `cd backend && php artisan test`

---

## 🆘 Troubleshooting

**Database connection error?**
- Check MySQL is running
- Verify .env database credentials
- Run `php artisan migrate:fresh --seed`

**Frontend not loading?**
- Check backend is running on port 8000
- Verify .env has correct API URL
- Clear browser cache

**Login not working?**
- Use email: `admin@demotransport.com`
- Password: `password`
- Check browser console for errors

---

## 📞 Support

For issues or questions, check:
- Backend logs: `backend/storage/logs/laravel.log`
- Frontend console: Browser DevTools
- Test results: `php artisan test`

---

**Happy Tracking! 🚀**
