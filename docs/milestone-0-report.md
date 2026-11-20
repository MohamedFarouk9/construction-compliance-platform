# Milestone 0 Report — BuildGuard Foundation (Beginner's Guide to Symfony)

## 1. Current Project Status

- **Milestone 0 (Foundation): Complete & verified locally** on PHP 8.2.12 (`7 tests, 33 assertions` passing offline).
- **Milestone 1 (Core Domain): Not started yet** (we will only begin Milestone 1 when you say `next`).
- **Docker status: Parked until the end of the project.** Earlier in Milestone 0, Docker files (`compose.yaml`, `docker/`) were created and tested with PHP 8.4, and then we switched to **100% local Windows development** (XAMPP PHP 8.2.12 + local MySQL) per your request. `vendor/` has already been synced for your local PHP 8.2.12 runtime, and we will revisit Docker only at the end when containerizing the full app.

---

## 2. How Symfony Works (For a Symfony Beginner)

If you know **Laravel** (or general PHP MVC), Symfony is very easy to pick up. The biggest mindset difference is:
- **Laravel** uses facades (`Route::get()`, `DB::select()`, `Log::error()`) and magic conventions.
- **Symfony** uses **explicit classes, constructor Dependency Injection (Autowiring), and PHP 8 Attributes (`#[Route]`)**.

### Request Lifecycle in BuildGuard (`GET /health`)

```mermaid
flowchart LR
    Client["Client / Browser<br/>GET /health"] --> Index["public/index.php<br/>(Front Controller)"]
    Index --> Runtime["Symfony Runtime<br/>Loads .env or .env.example"]
    Runtime --> Kernel["src/Kernel.php<br/>Boots Bundles & DI Container"]
    Kernel --> Router["Router<br/>Matches #[Route('/health')]"]
    Router --> Controller["HealthController::__invoke()<br/>(Presentation Layer)"]
    Controller --> Checker["HealthChecker::check()<br/>(Application Layer)"]
    Checker --> Interface["DatabaseHealthProbeInterface<br/>(Domain Contract)"]
    Interface -.implemented by.-> Probe["DoctrineDatabaseHealthProbe<br/>Runs SELECT 1 via DBAL<br/>(Infrastructure Layer)"]
    Probe --> Response["JsonResponse<br/>200 OK or 503 Degraded"]
```

### 4 Core Symfony Concepts You Just Used

1. **Front Controller (`public/index.php`)**: Every HTTP request enters through one file, `public/index.php`. Nothing in `src/` or `config/` is ever exposed directly to the browser.
2. **The Kernel (`src/Kernel.php`)**: The heart of Symfony. It loads installed **Bundles** (from `config/bundles.php`), reads `config/` YAML files, and builds the **Service Container** (which wires all your classes together).
3. **Autowiring (`config/services.yaml`)**: When `HealthController` asks for `HealthChecker` in its `__construct()`, and `HealthChecker` asks for `DatabaseHealthProbeInterface`, Symfony automatically sees that `DoctrineDatabaseHealthProbe` implements that interface and injects it for you — zero manual binding needed!
4. **PHP 8 Attributes (`#[Route]`)**: Instead of maintaining a separate `routes/api.php` file, each controller declares its own URL right above the class or method:
   ```php
   #[Route(path: '/health', name: 'shared_health', methods: ['GET'])]
   ```

---

## 3. Symfony vs. Laravel Cheat Sheet

| Concept | Laravel Equivalent | BuildGuard (Symfony 7.4 LTS) |
|---|---|---|
| **CLI tool** | `php artisan ...` | `php bin/console ...` |
| **List routes** | `php artisan route:list` | `php bin/console debug:router` |
| **Clear cache** | `php artisan cache:clear` | `php bin/console cache:clear` |
| **Framework info** | `php artisan about` | `php bin/console about` |
| **Route definitions** | `routes/api.php` | `#[Route(...)]` attributes on Controllers + `config/routes.yaml` |
| **Dependency Injection** | `AppServiceProvider::register()` | Automatic autowiring in `config/services.yaml` |
| **Database Layer (raw SQL)** | `DB::select('SELECT 1')` | `Doctrine\DBAL\Connection::executeQuery('SELECT 1')` |
| **Database ORM (later)** | Eloquent (`app/Models`) | Doctrine ORM (`Entity` + `Repository` — coming in Milestone 1) |
| **Environment config** | `.env` / `.env.example` | `.env` / `.env.example` via `symfony/dotenv` + `symfony/runtime` |
| **Logging** | `Log::error(...)` | Inject `Psr\Log\LoggerInterface` (powered by Monolog) |
| **Feature / HTTP tests** | `$this->getJson('/health')` (`TestCase`) | `$client->request('GET', '/health')` (`WebTestCase`) |
| **Compiled cache folder** | `bootstrap/cache/` | `var/cache/` |

---

## 4. Every Folder & File Created So Far (Explained One by One)

### A. Root Configuration Files

- [composer.json](file:///e:/construction-compliance-platform/composer.json): Defines project metadata (`buildguard/construction-compliance-platform`), PHP packages (`require`), test-only packages (`require-dev`), and PSR-4 autoloading (`App\` maps to `src/`).
- [composer.lock](file:///e:/construction-compliance-platform/composer.lock): Exact locked versions of every installed PHP package so everyone installs identical versions.
- [symfony.lock](file:///e:/construction-compliance-platform/symfony.lock): Tracks which Symfony Flex configuration recipes have been applied.
- [.env.example](file:///e:/construction-compliance-platform/.env.example): Safe, committed template of environment variables (`APP_ENV`, `APP_DEBUG`, `APP_SECRET`, `DEFAULT_URI`, `DATABASE_URL` pointing to local `127.0.0.1:3306/buildguard`). Never put real production passwords here.
- `.env` *(gitignored, local only)*: Your local copy of `.env.example` where you can adjust your XAMPP MySQL username/password/port without ever committing secrets.
- [.gitignore](file:///e:/construction-compliance-platform/.gitignore): Prevents `.env`, `vendor/`, `var/`, and `.phpunit.cache/` from being committed to Git.
- [.gitattributes](file:///e:/construction-compliance-platform/.gitattributes): Keeps LF line endings consistent across Windows and Linux.
- [phpunit.dist.xml](file:///e:/construction-compliance-platform/phpunit.dist.xml): PHPUnit configuration. Forces `APP_ENV=test` and `KERNEL_CLASS=App\Kernel`, and scans both `tests/` and `src/*/Tests/` for `*Test.php` files.
- [Makefile](file:///e:/construction-compliance-platform/Makefile): Convenience shortcuts saved alongside the Docker files (unused while running locally without `make`/Docker).

### B. Entrypoints (`public/`, `bin/`, `tests/`)

- [public/index.php](file:///e:/construction-compliance-platform/public/index.php): Web entrypoint. Includes a safety check so if `.env` does not exist yet on a fresh clone, Symfony automatically falls back to `.env.example` instead of crashing.
- [bin/console](file:///e:/construction-compliance-platform/bin/console): Symfony's CLI runner (like Laravel's `artisan`), with the same `.env.example` fallback.
- [bin/phpunit](file:///e:/construction-compliance-platform/bin/phpunit): Runs the installed PHPUnit test runner locally.
- [tests/bootstrap.php](file:///e:/construction-compliance-platform/tests/bootstrap.php): Loads Composer autoloading and `.env` / `.env.example` before PHPUnit executes tests.

### C. Symfony Configuration (`config/`)

- [config/bundles.php](file:///e:/construction-compliance-platform/config/bundles.php): Registry of enabled Symfony bundles (`FrameworkBundle`, `DoctrineBundle`, `MonologBundle`).
- [config/services.yaml](file:///e:/construction-compliance-platform/config/services.yaml): Configures automatic Dependency Injection (`autowire: true`, `autoconfigure: true`) across `src/`, excludes `Kernel.php`, `Tests/`, and immutable value objects (`HealthCheckResult`), and exposes `DatabaseHealthProbeInterface` publicly in the `test` environment (`when@test:`) so functional tests can swap in fake probes without needing a live MySQL server.
- [config/routes.yaml](file:///e:/construction-compliance-platform/config/routes.yaml): Tells Symfony to scan `src/` recursively for PHP `#[Route]` attributes so each module owns its own routes.
- [config/packages/framework.yaml](file:///e:/construction-compliance-platform/config/packages/framework.yaml): Core framework settings (`APP_SECRET`, sessions, and `framework.test: true` when `APP_ENV=test`).
- [config/packages/routing.yaml](file:///e:/construction-compliance-platform/config/packages/routing.yaml): Sets `default_uri: '%env(DEFAULT_URI)%'` (required by Symfony 7.4 for URL generation in CLI commands).
- [config/packages/doctrine.yaml](file:///e:/construction-compliance-platform/config/packages/doctrine.yaml): Configures **Doctrine DBAL only** (no ORM yet — ORM comes in Milestone 1), UTF-8 (`utf8mb4`), a 2-second connection timeout (`PDO::ATTR_TIMEOUT: 2`) so `/health` fails fast instead of hanging when MySQL is down, and `_test` database suffix in `test` mode.
- [config/packages/monolog.yaml](file:///e:/construction-compliance-platform/config/packages/monolog.yaml): Logs application events to `var/log/dev.log` (and `php://stderr` for errors) so you can diagnose database failures without exposing internal errors in API JSON responses.
- [config/packages/cache.yaml](file:///e:/construction-compliance-platform/config/packages/cache.yaml): Default filesystem cache settings.
- [config/routes/framework.yaml](file:///e:/construction-compliance-platform/config/routes/framework.yaml): Dev error preview routes (`/_error/...`).

### D. Modular Application Code (`src/`)

Instead of putting everything into flat `src/Controller/` and `src/Service/` folders, BuildGuard follows a **modular monolith** structure where each business area lives in its own folder (`src/Shared/`, and later `src/Organizations/`, `src/Projects/`, `src/Documents/`, etc.) divided into clean layers:

- [src/Kernel.php](file:///e:/construction-compliance-platform/src/Kernel.php): Symfony application kernel.
- [src/Shared/Domain/Health/DatabaseHealthProbeInterface.php](file:///e:/construction-compliance-platform/src/Shared/Domain/Health/DatabaseHealthProbeInterface.php) (**Domain layer**): Pure PHP interface (`isReachable(): bool`) defining *what* the app needs without coupling business logic to Doctrine or MySQL.
- [src/Shared/Infrastructure/Health/DoctrineDatabaseHealthProbe.php](file:///e:/construction-compliance-platform/src/Shared/Infrastructure/Health/DoctrineDatabaseHealthProbe.php) (**Infrastructure layer**): Implements `DatabaseHealthProbeInterface` using `Doctrine\DBAL\Connection`. Runs `SELECT 1`. If an exception occurs, it logs the real error via `LoggerInterface`, closes the broken connection, and returns `false` cleanly.
- [src/Shared/Application/Health/HealthCheckResult.php](file:///e:/construction-compliance-platform/src/Shared/Application/Health/HealthCheckResult.php) (**Application layer DTO / Value Object**): Immutable `readonly` result object holding service name (`buildguard-api`), database status (`up`/`down`), and UTC timestamp, plus `toArray()` formatting.
- [src/Shared/Application/Health/HealthChecker.php](file:///e:/construction-compliance-platform/src/Shared/Application/Health/HealthChecker.php) (**Application layer Use Case**): Calls `DatabaseHealthProbeInterface` and returns a `HealthCheckResult`.
- [src/Shared/Presentation/Controller/HealthController.php](file:///e:/construction-compliance-platform/src/Shared/Presentation/Controller/HealthController.php) (**Presentation layer**): Handles `GET /health` and returns JSON with HTTP `200 OK` (healthy) or `503 Service Unavailable` (degraded) — never leaking SQL details to API callers.

### E. Automated Tests (`src/Shared/Tests/`)

- [src/Shared/Tests/Unit/HealthCheckerTest.php](file:///e:/construction-compliance-platform/src/Shared/Tests/Unit/HealthCheckerTest.php): Pure unit tests verifying `HealthChecker` returns `'ok'` / `'up'` and `'degraded'` / `'down'` with UTC timestamps.
- [src/Shared/Tests/Integration/DoctrineDatabaseHealthProbeTest.php](file:///e:/construction-compliance-platform/src/Shared/Tests/Integration/DoctrineDatabaseHealthProbeTest.php): Integration tests using real Doctrine DBAL (`pdo_sqlite` in-memory for success, invalid path + logger spy for failure) to prove `SELECT 1` and error logging work without needing external services.
- [src/Shared/Tests/Functional/HealthEndpointTest.php](file:///e:/construction-compliance-platform/src/Shared/Tests/Functional/HealthEndpointTest.php): Symfony `WebTestCase` functional tests verifying `GET /health` returns `200`, `GET /health` returns `503` without leaking SQL/PDO text when the probe fails, and `POST /health` returns `405 Method Not Allowed`.

### F. Parked Docker Files (For the Final Milestone)

- [compose.yaml](file:///e:/construction-compliance-platform/compose.yaml), [docker/php/Dockerfile](file:///e:/construction-compliance-platform/docker/php/Dockerfile), [docker/php/php.ini](file:///e:/construction-compliance-platform/docker/php/php.ini), [docker/nginx/default.conf](file:///e:/construction-compliance-platform/docker/nginx/default.conf), and [docker/mysql/init/01-create-test-db.sql](file:///e:/construction-compliance-platform/docker/mysql/init/01-create-test-db.sql) remain in the repository for the final containerization stage and are **not needed** while developing locally.

---

## 5. Installed Packages & Why Each Is Needed

| Package | Purpose in BuildGuard |
|---|---|
| `symfony/framework-bundle` (`7.4.*`) | Core HTTP kernel, routing, dependency injection container, and `WebTestCase` support |
| `symfony/console` (`7.4.*`) | CLI tool (`php bin/console`) for cache clearing, route debugging, and future commands |
| `symfony/runtime` (`7.4.*`) | Bootstraps Symfony cleanly across CLI and web servers |
| `symfony/dotenv` (`7.4.*`) | Reads `.env` and `.env.example` into environment variables |
| `symfony/yaml` (`7.4.*`) | Parses YAML files inside `config/` |
| `symfony/flex` (`^2.12`) | Manages Symfony package configuration recipes |
| `doctrine/dbal` (`^4.6`) | Low-level database connection layer used to run `SELECT 1` against MySQL |
| `doctrine/doctrine-bundle` (`^2.19.4`) | Connects Symfony's DI container and `DATABASE_URL` to Doctrine DBAL |
| `symfony/monolog-bundle` (`^4.1`) | Structured PSR-3 logging to `var/log/` and `stderr` |
| `phpunit/phpunit` (`^11.5.58`, dev) | Automated unit, integration, and functional test runner |
| `symfony/browser-kit` & `symfony/css-selector` (`7.4.*`, dev) | Simulates HTTP requests inside Symfony `WebTestCase` functional tests |

---

## 6. How to Run BuildGuard Locally on Windows (No Docker)

> Your `vendor/` dependencies are **already synced and verified** for local PHP 8.2.12 (`Symfony v7.4.23 LTS`, `DoctrineBundle 2.19.4`, `PHPUnit 11.5.58`).

### Step 1: Optional One-Time XAMPP `php.ini` Tweak (Recommended Before Future Milestones)
Open `C:\xampp\php\php.ini`, find these two lines, and remove the leading `;`:
```ini
extension=intl
extension=zip
```
*(Without `zip` enabled globally, future `composer require` commands fall back to cloning packages slowly from GitHub instead of downloading fast `.zip` archives.)*

### Step 2: Create the `buildguard` Database in Local MySQL (XAMPP)
Your local MySQL on `127.0.0.1:3306` is already reachable (`var/log/dev.log` confirmed MySQL answered with `Unknown database 'buildguard'`). Create the empty database in phpMyAdmin or MySQL CLI:
```sql
CREATE DATABASE IF NOT EXISTS buildguard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Check [.env](file:///e:/construction-compliance-platform/.env) (copied from [.env.example](file:///e:/construction-compliance-platform/.env.example)) and adjust `DATABASE_URL` if your local MySQL uses a password or different port:
```dotenv
DATABASE_URL="mysql://root:@127.0.0.1:3306/buildguard?charset=utf8mb4"
```

### Step 3: Start the Local API Server & Run Automated Tests
Start PHP's built-in development server (note: port `8000` is currently used by another local app on your machine, so `8085` avoids any conflict):
```powershell
php -S 127.0.0.1:8085 -t public
```
In another PowerShell window, call `GET /health`:
```powershell
curl.exe -i http://127.0.0.1:8085/health
```
Expected healthy response (`200 OK`) once the `buildguard` database exists:
```json
{"service":"buildguard-api","status":"ok","checks":{"database":{"status":"up"}},"timestamp":"..."}
```
Run all automated tests (passes offline even when MySQL is stopped):
```powershell
php bin/phpunit
```
Useful Symfony CLI commands:
```powershell
php bin/console about
php bin/console debug:router
php bin/console cache:clear
```

### Step 4: Milestone 0 Failure Drill (Local MySQL Outage)
1. Stop **MySQL** in XAMPP Control Panel.
2. Call `curl.exe -i http://127.0.0.1:8085/health` again.
3. Expected response (`503 Service Unavailable`, no raw SQL errors leaked):
   ```json
   {"service":"buildguard-api","status":"degraded","checks":{"database":{"status":"down"}},"timestamp":"..."}
   ```
4. Inspect `var/log/dev.log` to see the detailed Doctrine DBAL error recorded for developers.
5. Start **MySQL** again in XAMPP and call `curl.exe -i http://127.0.0.1:8085/health` to confirm recovery back to `200 OK`.
