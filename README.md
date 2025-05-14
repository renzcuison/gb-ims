# 📦 SAD Project – Inventory Management System & E-Commerce Website

This project is a full-stack web application built with **Laravel** (backend) and **Vue.js** (frontend). It includes features for inventory management and e-commerce functionality.

---

## 🚀 Getting Started

### 📁 Backend Setup (Laravel)
```
cd backend
composer install
composer require laravel/socialite
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan serve --port=8001
```
### 🌐 Frontend Setup (Vue.js)

```
npm install
npm run dev
```

## 👤 Default Admin Credentials

- **Email**: `admin`  
- **Password**: `adminpassword`

## 🛠 Technologies Used

- Laravel
- Vue.js
- MySQL
- Vite

