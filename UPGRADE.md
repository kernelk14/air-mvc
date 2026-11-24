# Upgrade Guide

## v1 -> v2

v2 is a breaking rewrite. The legacy global/class-name API is removed.

### Breaking changes

- **Composer/PSR-4**: Framework classes live under `Air\`; app classes under `App\`.
- **Entry/bootstrap**: Root `index.php` boots `bootstrap/app.php` and dispatches through `Air\Http\Kernel`. Old `spl_autoload_register` and `APP_DIR` constants removed.
- **Routes**: Routes moved from `config/Routes.php` to `config/routes.php` and use `Air\Routing\Router` with `[Controller::class, 'method']` handlers.
- **Controllers**: Extend `Air\Http\Controller`; actions receive `Air\Http\Request` and return `Response|string|array`.
- **Views**: Located in `app/Views/`; rendered via `$this->view()` / `view()`; assets via `asset()` helper.
- **Config**: `config/app.php` and `config/database.php` return arrays; access via `config()` / `env()`.
- **Legacy core removed**: `core/Router.php`, `core/Controller.php`, `core/Model.php`, `core/Request.php`, `core/Auth.php`, `core/InputValidation.php`, `core/Helper.php`.

### Migration checklist

1. Run `composer install`.
2. Copy `.env.example` to `.env`.
3. Move routes to `config/routes.php` using class-array handlers.
4. Namespace controllers as `App\Controllers\*` and extend `Air\Http\Controller`.
5. Move views to `app/Views/` and replace relative asset paths with `asset(...)`.
6. Replace direct `$_GET/$_POST` usage with `$request->input()`.
7. Run `composer test`.
