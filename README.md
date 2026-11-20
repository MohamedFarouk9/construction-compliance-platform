# BuildGuard

BuildGuard is a production-style construction project compliance platform inspired by Procore.

It helps construction companies manage:

- Construction projects
- Job sites
- Contractors and vendors
- Permits
- Inspections
- Insurance documents
- Safety certificates
- Compliance requirements
- Expiring documents
- Notifications
- Audit history

## Main business question

> Is this contractor allowed to work on this project, and are all required documents and permits valid?

## Current Status & Workflow

- **Current Milestone:** Milestone 0 (Foundation)
- **Development Mode:** Local Windows runtime (PHP 8.2.12 + Composer 2.9.5 + local MySQL/XAMPP). Docker files (`compose.yaml`, `docker/`) are kept in the repository for containerization at the end of the project.
- **New to Symfony?** Start with the step-by-step [Milestone 0 Beginner's Report](file:///e:/construction-compliance-platform/docs/milestone-0-report.md).

## Milestone 0 Stack

| Component | Selection | Purpose |
|---|---|---|
| Runtime | PHP 8.2.12 (local) | Application runtime |
| Framework | Symfony 7.4 LTS (`symfony/skeleton`) | Modular monolith API foundation |
| Database | Local MySQL / MariaDB + Doctrine DBAL `^4.6` (`DoctrineBundle ^2.19.4`) | Transactional storage and `SELECT 1` health check |
| Logging | Monolog (`symfony/monolog-bundle ^4.1`) | Structured dev logs in `var/log/dev.log` |
| Testing | PHPUnit `^11.5.58` (`symfony/test-pack`) | Unit, DBAL integration, and `WebTestCase` functional tests |

## Local Quickstart (No Docker)

1. Copy `.env.example` to `.env` and adjust `DATABASE_URL` for your local MySQL credentials:

```powershell
Copy-Item .env.example .env
```

2. Create the `buildguard` database in local MySQL (`CREATE DATABASE IF NOT EXISTS buildguard;`).
3. Start the local PHP server (use another free port such as `8085` if `8000` is already in use):

```powershell
php -S 127.0.0.1:8085 -t public
```

4. Check the health endpoint:

```powershell
curl.exe -i http://127.0.0.1:8085/health
```

- Healthy (`200 OK`): `{"service":"buildguard-api","status":"ok","checks":{"database":{"status":"up"}},"timestamp":"..."}`
- Database down (`503 Service Unavailable`): `{"service":"buildguard-api","status":"degraded","checks":{"database":{"status":"down"}},"timestamp":"..."}`

5. Run automated tests:

```powershell
php bin/phpunit
```

## Symfony vs. Laravel Quick Reference

| Concept | Laravel | Symfony (BuildGuard) |
|---|---|---|
| Console CLI | `php artisan` | `php bin/console` |
| Routes | `routes/api.php` | `#[Route]` attributes in `src/*/Presentation/Controller/` |
| Dependency Injection | Service Providers | Constructor autowiring (`config/services.yaml`) |
| DB Query Layer | `DB::select()` | `Doctrine\DBAL\Connection` |
| Feature Tests | `TestCase` (`getJson`) | `WebTestCase` (`$client->request`) |

## Project Documentation

- [Milestone 0 Report (Symfony Guide)](file:///e:/construction-compliance-platform/docs/milestone-0-report.md)
- [Project Prompt & Specifications](file:///e:/construction-compliance-platform/docs/project-prompt.md)
- [Architecture Overview](file:///e:/construction-compliance-platform/docs/architecture.md)
- [Development Roadmap](file:///e:/construction-compliance-platform/docs/roadmap.md)
- [Learning & Decision Log](file:///e:/construction-compliance-platform/docs/learning-log.md)
- [Agent Rules](file:///e:/construction-compliance-platform/AGENTS.md)
