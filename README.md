# GroceryPro 🛒

GroceryPro is a Point of Sale (POS) system built using **Laravel**, **Inertia.js**, **Vue 3**, and **Vuetify**.
This project is designed for managing grocery store operations such as transactions, stock, and reporting.

---

## 🚀 Tech Stack

* **Backend:** Laravel
* **Frontend:** Vue 3 (Inertia.js)
* **UI Framework:** Vuetify
* **Build Tool:** Vite
* **Database:** MySQL
* **Auth:** Laravel Breeze (Inertia + Vue)

---

## 📦 Installation Guide

### 1. Clone Repository

```bash
git clone https://github.com/PPL-485/GroceryPro.git
cd GroceryPro
```

---

### 2. Install Dependencies

#### Backend (Laravel)

```bash
composer install
```

#### Frontend (Vue + Vite)

```bash
npm install
```

---

### 3. Environment Setup

Copy `.env` file:

```bash
cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

---

### 4. Configure Database

Edit `.env`:

```env
DB_DATABASE=grocerypro
DB_USERNAME=root
DB_PASSWORD=
```

---

### 5. Run Migration

```bash
php artisan migrate
```

👉 Optional (with seeder or first time migrating):

```bash
php artisan migrate:fresh --seed
```

---

### 6. Run Development Server

#### Backend

```bash
php artisan serve
```

#### Frontend

```bash
npm run dev
```

---

## 🔐 Default Login (Seeder)

If you run seeder:

```text
Email: admin@gmail.com
Password: password
```

---

## 📁 Project Structure (Important)

```
resources/js/
  ├── Pages/        # Inertia pages
  ├── Components/   # Reusable components
  ├── Layouts/      # Layouts (Sidebar, Navbar)
  └── plugins/      # Vuetify config

app/
  ├── Models/
  ├── Http/
  └── Providers/

database/
  ├── migrations/
  └── seeders/
```

---

## 🧠 Development Notes

* Uses **Inertia.js** → no traditional API needed (monolith SPA)
* Vuetify handles UI components
* Tailwind can be used for utility styling (optional)
* Authentication handled by Laravel Breeze

---

## ⚡ Useful Commands

```bash
# Reset database
php artisan migrate:fresh

# Reset + seed
php artisan migrate:fresh --seed

# Clear cache
php artisan optimize:clear

# Run build
npm run build
```

---

## 🎯 Features (Planned / Ongoing)

* POS Transactions
* Product & Stock Management
* Category Management
* Reports & Analytics
* User Management

---
