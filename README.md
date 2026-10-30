# 🛒 E-commerce Project

This is a modern e-commerce application built with the Laravel ecosystem, utilizing a Single Page Application (SPA) approach via Inertia.js and Vue.js.

## 🛠️ Tech Stack

### Backend
- **Language:** PHP 8.5
- **Framework:** Laravel 13.38.0
- **Database:** MySQL
- **Authentication:** Laravel Fortify & Laravel Passkeys (WebAuthn)
- **Route Typing:** Laravel Wayfinder

### Frontend
- **Framework:** Vue.js 3
- **Bridge:** Inertia.js v3
- **Styling:** Tailwind CSS v4
- **Icons:** Lucide Vue

---

## 📚 Backend Documentation

### 🌐 Architecture & Routing
The backend is modularized into specific namespaces to separate concerns:

#### 1. Administration (`Admin\`)
Controls the internal management panel.
- **Categories:** `Admin\CategoryController` - Full CRUD for product categories.
- **Products:** `Admin\ProductController` - Product management and image uploads.
- **Orders:** `Admin\OrderController` - Order monitoring and status updates.
- **Shipping:** `Admin\ShippingZoneController` - Management of delivery zones and costs.

#### 2. Storefront (`Storefront\`)
Handles the public-facing customer experience.
- **Products:** `Storefront\ProductController` - Product listing and slug-based detail views.
- **Cart:** `Storefront\CartController` - Management of the shopping cart (add, update, remove).
- **Checkout:** `Storefront\CheckoutController` - Order placement and payment processing.
- **Tracking:** `Storefront\OrderTrackingController` - Order status tracking via unique codes.

#### 3. User Account (`Account\`, `Settings\`)
Manages user profiles and security.
- **Orders:** `Account\OrderController` - User's personal order history.
- **Profile:** `Settings\ProfileController` - User data management.
- **Security:** `Settings\SecurityController` - Password updates and security settings.

### 🔐 Security & Authentication
- **Laravel Fortify:** Provides a frontend-agnostic authentication backend for login, registration, and password resets.
- **Passkeys:** Implementation of passwordless authentication using the WebAuthn standard via `laravel/passkeys`.
- **2FA:** Support for two-factor authentication.

### 📦 Key System Features
- **Inertia.js v3:** Allows the backend to serve Vue components as pages, combining the SEO/routing benefits of Laravel with the reactivity of Vue.
- **Wayfinder:** Generates TypeScript functions for Laravel routes, ensuring type-safety when calling backend actions from the frontend.
- **Testing:** Built using **Pest**, ensuring high reliability through feature and unit tests.

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.5+
- Composer
- Node.js & NPM
- MySQL

### Installation
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd e-commerce
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JS dependencies:
   ```bash
   npm install
   ```
4. Setup environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```
6. Compile assets and start the server:
   ```bash
   npm run dev
   php artisan serve
   ```

## 🧪 Testing
Run the test suite using Pest:
```bash
php artisan test
```
