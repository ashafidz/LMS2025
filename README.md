# ruangstudi.id

ruangstudi.id is a comprehensive Learning Management System built with the Laravel framework, designed to handle courses, quizzes, assignments, and more in an interactive and user-friendly environment.
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)

ruangstudi.id is a comprehensive, enterprise-grade Learning Management System (LMS) built with the Laravel framework. It is designed to handle courses, quizzes, assignments, and more within an interactive, gamified, and user-friendly environment.

## 🚀 Features

- **Course Management:** Create, organize, and manage learning materials easily.
- **Quizzes & Assignments:** Interactive quiz system and drag-and-drop assignment submissions with size validation.
- **User Roles & Permissions:** Robust role management (Instructor, Student, Admin).
- **Gamification:** Badges, leaderboards, and point system.
- **Modern UI/UX:** Responsive design with smooth user interactions.
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

To run this project, you will need:
To run this project locally, you will need:

- PHP >= 8.3
- Composer
- Node.js >= 20 & NPM
- Database (MySQL / SQLite / PostgreSQL)
- Docker & Podman (Optional, for containerized development services)

## 🛠️ Installation & Setup

We have prepared a comprehensive guide for setting up your development environment.
We have prepared a comprehensive guide for setting up your development environment in UNIX-based systems.

Please refer to the documentation:

- [Ubuntu Development Setup](docs/ubuntu-development-setup.md)

### Quick Start

1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database
3. Copy `.env.example` to `.env` and configure your database & service keys
4. Run `composer install`
5. Run `npm install` and `npm run dev`
6. Run `npm install` && `npm run build`
7. Generate application key: `php artisan key:generate`
8. Run migrations & seeders: `php artisan migrate --seed`
9. Start the local server: `php artisan serve`
10. Copy `.env.example` to `.env` and configure your database & service keys
11. Run `composer install`
12. Run `npm install` && `npm run build`
13. Generate application key: `php artisan key:generate`
14. Run migrations & seeders: `php artisan migrate --seed`
15. Start the local server: `php artisan serve`

## 🧪 Testing & Code Quality

This project enforces strict code quality and testing standards via automated CI/CD pipelines.

**Run Code Style Linter (Laravel Pint):**

```bash
vendor/bin/pint
```

_(Append `--test` to only inspect without modifying files)._

**Run Automated Tests (PHPUnit):**

```bash
php artisan test
```

## 🔄 CI/CD & Branching Strategy

This project follows an organized Branching Strategy (inspired by GitFlow) and uses GitHub Actions for CI/CD.
This project follows an organized Branching Strategy (GitFlow) and uses GitHub Actions for continuous integration (CI) and continuous deployment (CD).

For a detailed explanation of our branching rules, environments, and workflows, please refer to the documentation:

- [Branching Strategy & CI/CD Guide](docs/branching-strategy.md)

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to the development team. All security vulnerabilities will be promptly addressed.

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This project inherits and complies with the necessary licensing.
