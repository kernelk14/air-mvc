# Air MVC

Lightweight, extensible PHP MVC framework. XAMPP-friendly (root `index.php`) and Composer/PSR-4 based.

## Requirements

- PHP 8.2+
- Composer

## Install

```bash
composer install
cp .env.example .env
```

## Run

```bash
composer serve
# or: php -S localhost:8000 index.php
```

Apache/XAMPP: point the host at the project root; `.htaccess` routes requests to `index.php`.

## CLI

```bash
php bin/air serve
php bin/air route:list
```

## Project Structure

- `index.php` - front controller
- `bootstrap/app.php` - application bootstrap
- `config/app.php`, `config/database.php`, `config/routes.php` - configuration and routes
- `src/` - framework core (`Air\`)
- `app/` - application skeleton (`App\`)
- `public/assets/` - web assets
- `tests/` - PHPUnit tests

## Testing

```bash
composer test
composer analyse
composer format:check
```
