# ruangstudi.id

ruangstudi.id is a comprehensive Learning Management System built with the Laravel framework, designed to handle courses, quizzes, assignments, and more in an interactive and user-friendly environment.

## 🚀 Features

- **Course Management:** Create, organize, and manage learning materials easily.
- **Quizzes & Assignments:** Interactive quiz system and drag-and-drop assignment submissions with size validation.
- **User Roles & Permissions:** Robust role management (Instructor, Student, Admin).
- **Gamification:** Badges, leaderboards, and point system.
- **Modern UI/UX:** Responsive design with smooth user interactions.

## 📋 Requirements

To run this project, you will need:
- PHP >= 8.3
- Composer
- Node.js >= 20 & NPM
- Database (MySQL / SQLite / PostgreSQL)
- Docker & Podman (Optional, for containerized development services)

## 🛠️ Installation & Setup

We have prepared a comprehensive guide for setting up your development environment.

Please refer to the documentation:
- [Ubuntu Development Setup](docs/ubuntu-development-setup.md)

### Quick Start
1. Clone the repository
2. Copy `.env.example` to `.env` and configure your database
3. Run `composer install`
4. Run `npm install` and `npm run dev`
5. Generate application key: `php artisan key:generate`
6. Run migrations & seeders: `php artisan migrate --seed`
7. Start the local server: `php artisan serve`

## 🔄 CI/CD & Branching Strategy

This project follows an organized Branching Strategy (inspired by GitFlow) and uses GitHub Actions for CI/CD.

For a detailed explanation of our branching rules, environments, and workflows, please refer to the documentation:
- [Branching Strategy & CI/CD Guide](docs/branching-strategy.md)

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This project inherits and complies with the necessary licensing.
