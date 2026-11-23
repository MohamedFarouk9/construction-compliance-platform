# Learning Log

## 2026-11-19

### Project started

Started BuildGuard, a construction project compliance platform.

### Goals

- Learn Symfony through a realistic project.
- Practice Doctrine and MySQL.
- Build asynchronous processing with RabbitMQ.
- Learn Kafka and event-driven design.
- Practice Python and Node.js services.
- Deploy locally with Docker and Kubernetes.
- Build production-style monitoring and CI/CD.

### Current milestone

Milestone 0 - Foundation

### Decisions

- Use a Symfony modular monolith for the main application.
- Use MySQL for transactional data.
- Use MongoDB for raw external data.
- Use RabbitMQ for background jobs.
- Use Kafka for business events.
- Use Docker for local development.
- Use kind for local Kubernetes.

### Mistakes and lessons

No mistakes recorded yet.

### Interview notes

I am building BuildGuard as a personal production-style learning project. I will only claim technologies and features after implementing and testing them.

---

## 2026-11-20 — Milestone 0: Foundation

### What was built

- Bootstrapped **Symfony 7.4 LTS** (`symfony/skeleton:^7.4`) at the repository root with modular `src/Shared/{Domain,Application,Infrastructure,Presentation,Tests}` layering.
- Configured `.env.example` fallback loading in `public/index.php`, `bin/console`, and `tests/bootstrap.php` so fresh clones and test runs boot without requiring `.env` to be committed.
- Configured **Doctrine DBAL** (without ORM until Milestone 1) with a 2-second connection timeout (`PDO::ATTR_TIMEOUT: 2`) and **Monolog** error logging to `var/log/` and `php://stderr`.
- Implemented `GET /health` (`HealthController` -> `HealthChecker` -> `DatabaseHealthProbeInterface` -> `DoctrineDatabaseHealthProbe`) returning `200 OK` when MySQL responds to `SELECT 1` and `503 Service Unavailable` with safe JSON (no raw SQL/PDO errors leaked) when MySQL is unreachable.
- Created unit, DBAL integration, and `WebTestCase` functional tests under `src/Shared/Tests/`.
- Wrote [docs/milestone-0-report.md](file:///e:/construction-compliance-platform/docs/milestone-0-report.md) as a beginner's guide to Symfony and local execution.

### Workflow decision (Local-first, Docker deferred)

- Initially scaffolded and tested Docker Compose (`nginx:1.27-alpine`, `php:8.4-fpm-alpine`, `mysql:8.4`), where `GET /health` and the MySQL-stop `503` failure drill were verified against live containers.
- Switched to **100% local Windows execution** (XAMPP PHP 8.2.12 + Composer 2.9.5 + local MySQL) for Milestones 1–9 to iterate faster and learn Symfony fundamentals without Docker friction, keeping `compose.yaml` and `docker/` parked for final containerization.
- Adjusted `composer.json` constraints (`doctrine/doctrine-bundle: ^2.19.4`, `phpunit/phpunit: ^11.5.58`) and verified `php bin/phpunit` (`7 tests, 33 assertions`), `php bin/console about` (`Symfony v7.4.23`, `PHP 8.2.12`), and live local HTTP `/health` (`503` degraded when `buildguard` DB was absent, logging `SQLSTATE[HY000] [1049] Unknown database 'buildguard'` to `var/log/dev.log`).

### Mistakes and lessons

1. **Symfony Flex defaults can surprise custom stacks**: Installing `doctrine/doctrine-bundle` injected an `orm:` section in `doctrine.yaml` (failing `cache:clear` without `doctrine/orm`) and appended a Postgres `database` service to `compose.yaml` + `compose.override.yaml`. Always inspect `git status` and Flex recipes after `composer require`.
2. **Symfony 7.4 requires `DEFAULT_URI`**: `config/packages/routing.yaml` references `%env(DEFAULT_URI)%`, which must be declared in `.env.example` and `.env`.
3. **PHP runtime parity matters**: Dependencies resolved inside PHP 8.4 (`doctrine-bundle 3.x`, `phpunit 13.x`) trigger Composer's `platform_check.php` when run on local PHP 8.2.12. Aligning `composer.json` constraints to PHP 8.2 keeps local runs seamless.
4. **PHPUnit env overrides inside containers/shells**: Using both `<server name="APP_ENV" value="test" force="true" />` and `<env name="APP_ENV" value="test" force="true" />` in `phpunit.dist.xml` guarantees `KernelTestCase` boots in the `test` environment even if `APP_ENV=dev` is already set in the shell.

### Interview notes (Health check & foundation)

- **Problem**: Before adding domain endpoints, operators and load balancers need a fast, safe readiness endpoint verifying the API and database are reachable without leaking internal credentials or SQL errors.
- **Decision**: Built a DBAL-only `GET /health` endpoint behind a `DatabaseHealthProbeInterface` abstraction in the `Shared` module, separating domain contracts from Doctrine infrastructure and mapping failures to HTTP `503`.
- **Implementation**: Symfony 7.4 LTS `HealthController` delegates to `HealthChecker` and `DoctrineDatabaseHealthProbe`, executing `SELECT 1` with a 2-second PDO timeout, logging exceptions via PSR-3/Monolog, and returning immutable `HealthCheckResult` JSON.
- **Failure**: Tested stopping MySQL; `/health` returned `503 Service Unavailable` (`{"status":"degraded","checks":{"database":{"status":"down"}}}`) within the timeout window while `var/log/dev.log` recorded the `Doctrine\DBAL\Exception\ConnectionException` for debugging.
- **Measurement**: Verified with unit tests (`HealthCheckerTest`), DBAL integration tests (`DoctrineDatabaseHealthProbeTest`), and Symfony `WebTestCase` functional tests (`HealthEndpointTest`).
- **Trade-off**: Using `SELECT 1` checks basic database connectivity rather than deeper schema/table readiness, and keeping ORM out in Milestone 0 avoids premature entities at the cost of adding ORM/migrations in Milestone 1.
- **Future improvement**: Expand `/health` in later milestones with probes for S3/LocalStack, RabbitMQ, and Kafka, and split `/health/live` vs `/health/ready` for Kubernetes probes.

> I built and tested this as a personal production-style project locally.


---

## 2026-11-22 — Milestone 1: Core Domain

### What was built

- Installed `doctrine/orm` (`3.8.0`), `doctrine/doctrine-migrations-bundle` (`3.7.1`), `symfony/validator` (`v7.4.23`), and `doctrine/doctrine-fixtures-bundle` (`4.3.1`, dev) for local PHP 8.2.12.
- Configured per-module Doctrine ORM attribute mappings in `config/packages/doctrine.yaml` across `Organizations`, `Identity`, `Projects`, `Contractors`, and `Permits`.
- Built 7 entities (`Organization`, `User`, `Project`, `JobSite`, `Company`, `ContractorAssignment`, `Permit`) with unique constraints, foreign keys (`ON DELETE CASCADE`), and single/composite indexes.
- Generated and executed initial Doctrine migration (`migrations/Version20261122211041.php`) on both `buildguard` and `buildguard_test` (`MariaDB 10.4.32`), verified with `doctrine:schema:validate`.
- Built 7 repositories including `ProjectRepository::findAllWithDetails()` using 4-step eager hydration to eliminate both N+1 queries and SQL Cartesian products.
- Built validated REST API endpoints (`/api/organizations`, `/api/users`, `/api/projects`, `/api/projects/{id}/job-sites`, `/api/projects/{id}/contractors`, `/api/contractors`, `/api/permits`) with `400` malformed JSON, `404` missing parent, `409` duplicate key, and `422` validation error handling.
- Built `AppFixtures` with realistic construction seed data plus a batched 25-contractor bulk insert using `flush()` + `clear()` every 10 rows.
- Added unit and functional API tests (`11 tests, 117 assertions` passing) including an eager-loading assertion verifying all collections and proxies are initialized in memory.
- Verified `EXPLAIN` plans on indexed `projects` and `permits` queries (`type=ref`, `key=idx_permits_project_status`).
- Wrote [docs/milestone-1-report.md](file:///e:/construction-compliance-platform/docs/milestone-1-report.md).

### Mistakes and lessons

1. **Specify `serverVersion` in `DATABASE_URL`**: Without `serverVersion=10.4.32-MariaDB`, `doctrine:database:create` attempted to connect to the non-existent `buildguard` database to auto-detect the platform version. Pinning `serverVersion` fixes offline DDL generation and matches MariaDB 10.4 accurately.
2. **Avoid joining multiple `OneToMany` collections in one DQL query**: Joining `jobSites`, `contractorAssignments`, and `permits` in a single query multiplies rows (Cartesian product). Multi-step `IN (:ids)` hydration executes constant `O(1)` queries while keeping all collections initialized.

### Interview notes (Doctrine ORM, N+1, Indexing & Batching)

- **Problem**: A construction platform frequently lists projects alongside their organization, job sites, assigned contractors, and permits; naive ORM lazy loading causes `O(N)` N+1 queries, while naive multi-collection joins cause Cartesian row explosions.
- **Decision**: Modeled 7 normalized tables across modular Symfony namespaces with targeted composite indexes (`(organization_id, status)`, `(project_id, status)`), multi-step eager hydration in `ProjectRepository`, and chunked `flush()`/`clear()` batching for bulk writes.
- **Implementation**: Doctrine ORM 3.8 attribute entities, versioned migration `Version20261122211041`, validated request DTOs via `symfony/validator`, and `AppFixtures` batching 25 trade contractors in chunks of 10.
- **Failure**: Tested invalid JSON (`400`), invalid DTO fields and `ends_at < starts_at` (`422`), missing parent IDs (`404`), and duplicate unique keys/assignments (`409`) so database constraint errors never bubble up as raw `500` responses.
- **Measurement**: Verified with `doctrine:schema:validate`, `EXPLAIN` (`type=ref`, `key=idx_permits_project_status`, `rows=1`), live HTTP `/api/projects`, and PHPUnit (`11 tests, 117 assertions`, including `PersistentCollection::isInitialized()` assertions).
- **Trade-off**: Using explicit DTOs and `toArray()` methods adds slight boilerplate compared to magic serializers, but guarantees strict input validation and prevents accidental data leaks or lazy-loading surprises during serialization.
- **Future improvement**: Add cursor/offset pagination to list endpoints and authentication/RBAC enforcement on write routes in upcoming milestones.

> I built and tested this as a personal production-style project locally.
