# ruangstudi.id

<p align="left">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/License-Proprietary-red.svg?style=for-the-badge" alt="License">
</p>

**ruangstudi.id** is a comprehensive, enterprise-grade Learning Management System (LMS) built with the Laravel framework. It is designed to handle courses, quizzes, assignments, and more within an interactive, gamified, and user-friendly environment.

## 🚀 Features

- **Course Management:** Create, organize, and manage learning materials seamlessly.
- **Quizzes & Assignments:** Interactive quiz system and drag-and-drop assignment submissions with client-side & server-side size validation.
- **User Roles & Permissions:** Robust role management using Spatie (Instructor, Student, Admin).
- **Gamification:** Badges, leaderboards, and an integrated point-to-diamond reward system.
- **Secure Payments:** Integrated payment gateway (Midtrans) for course purchases.
- **Modern UI/UX:** Fully responsive design built with modern CSS frameworks and smooth user interactions.

## 💻 Tech Stack

- **Backend:** Laravel 11.x, PHP 8.3
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** MySQL
- **Integrations:** Midtrans (Payment Gateway), Google reCAPTCHA v3
- **Development Tools:** Laravel Pint (Code Style), PHPUnit (Testing), Docker

## 📋 Requirements

To run this project locally, you will need:

- PHP >= 8.3
- Composer
- Node.js >= 20 & NPM
- Database (MySQL / SQLite / PostgreSQL)

## 🛠️ Installation & Setup

We have prepared a comprehensive guide for setting up your development environment in UNIX-based systems. Please refer to the documentation:
- [Ubuntu Development Setup](docs/ubuntu-development-setup.md)

### Quick Start

1. Clone the repository:
   ```bash
   git clone <repository-url>
   ```
2. Copy `.env.example` to `.env` and configure your database & service keys:
   ```bash
   cp .env.example .env
   ```
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Install Node dependencies and build assets:
   ```bash
   npm install && npm run build
   ```
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Run migrations & seeders:
   ```bash
   php artisan migrate --seed
   ```
7. Start the local server:
   ```bash
   php artisan serve
   ```

## 🧪 Testing & Code Quality

This project enforces strict code quality and testing standards via automated CI/CD pipelines.

**Run Code Style Linter (Laravel Pint):**
```bash
vendor/bin/pint
```
*(Append `--test` to only inspect without modifying files).*

**Run Automated Tests (PHPUnit):**
```bash
php artisan test
```

## 🔄 CI/CD & Branching Strategy

This project follows an organized Branching Strategy (GitFlow) and uses GitHub Actions for continuous integration (CI) and continuous deployment (CD).

- [Branching Strategy & CI/CD Guide](docs/branching-strategy.md)

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to the development team immediately. All security vulnerabilities will be promptly addressed.

## 📄 License

**Proprietary and Confidential**

Unauthorized copying, distribution, modification, or use of this software and associated documentation files (the "Software"), via any medium, is strictly prohibited. The Software is proprietary to ruangstudi.id. All rights reserved.
