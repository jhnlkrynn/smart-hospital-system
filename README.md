## Smart Hospital Management System

This Laravel application is being developed as a modular Smart Hospital Management System. Phase 1 established the working Laravel project, XAMPP database connection, Laravel Breeze authentication, frontend dependencies, migrations, and Git repository.

Phase 2 adds the technical planning layer before implementation of the main modules. The planned architecture uses Blade, Tailwind CSS, Alpine.js, controllers, Form Requests, policies, service classes, PHP enums, Eloquent models, migrations, seeders, audit logging, and MySQL/MariaDB-backed normalized data.

### Phase 2 Documentation

- [Architecture](docs/architecture.md)
- [Database Design](docs/database-design.md)
- [Entity Relationship Diagram](docs/entity-relationship-diagram.md)
- [Module Dependencies](docs/module-dependencies.md)
- [Permission Matrix](docs/permission-matrix.md)
- [Route Plan](docs/route-plan.md)
- [Security Plan](docs/security-plan.md)
- [Development Roadmap](docs/development-roadmap.md)

### Phase 3 Access Control

Phase 3 implements role and permission management with Spatie Laravel Permission, account status handling, role-based dashboard redirects, protected dashboard routes, fictional demo users, permission-aware navigation, and authorization tests.

Roles:

- Super Admin
- Hospital Admin
- Doctor
- Nurse
- Patient
- Pharmacist
- Laboratory Staff
- Cashier

Dashboard URLs:

- `/super-admin/dashboard`
- `/admin/dashboard`
- `/doctor/dashboard`
- `/nurse/dashboard`
- `/patient/dashboard`
- `/pharmacist/dashboard`
- `/laboratory/dashboard`
- `/cashier/dashboard`

Phase 3 docs:

- [Access Control](docs/access-control.md)
- [Role Permission Matrix](docs/role-permission-matrix.md)
- [Demo Accounts](docs/demo-accounts.md)
- [Dashboard Routing](docs/dashboard-routing.md)

Seed roles, permissions, and development demo users:

```bash
php artisan db:seed
```

Run tests:

```bash
php artisan test
```

Development-only demo password: `Password123!`. Change or remove demo accounts before deployment.

Next phase: **Phase 4: Department and Employee Management**.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
