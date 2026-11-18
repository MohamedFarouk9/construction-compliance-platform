# BuildGuard: Construction Project Compliance Platform

A production-style practice project for a Procore/Data Services interview.

## 1. Project goal

BuildGuard is a construction operations and compliance platform inspired by Procore.

It helps construction companies manage projects, contractors, permits, inspections, insurance certificates, safety documents, compliance requirements, notifications, and audit history.

The main business question is:

> Is this contractor allowed to work on this project, and are all required documents and permits valid?

The platform will:

- Manage organizations and users.
- Manage construction projects and job sites.
- Manage contractors and vendors.
- Upload and process compliance documents.
- Import public permit data.
- Normalize companies, addresses and projects.
- Detect duplicate entities.
- Calculate contractor compliance.
- Publish business events.
- Send notifications.
- Expose a Symfony REST API.
- Run locally with Docker.
- Deploy locally to Kubernetes.
- Provide monitoring, logs, security scans and CI/CD.

## 2. Main business workflow

```text
Admin creates an organization
        ↓
Admin creates a construction project
        ↓
Admin adds a job site
        ↓
Admin assigns contractors
        ↓
Contractors upload insurance and safety documents
        ↓
System stores original files in S3
        ↓
Background workers process documents
        ↓
System extracts document type and expiration date
        ↓
Compliance engine validates project requirements
        ↓
System publishes compliance events
        ↓
Notification service alerts project managers
        ↓
Symfony API exposes project compliance status
```

Example result:

```json
{
  "project": "Downtown Tower",
  "contractor": "ABC Electrical",
  "status": "non_compliant",
  "missing_documents": [
    "safety_certificate"
  ],
  "expiring_documents": [
    {
      "type": "insurance_certificate",
      "expires_at": "2027-05-30"
    }
  ]
}
```

## 3. Technology coverage

| Skill | Where it is used |
|---|---|
| PHP and Symfony | Main API and modular monolith |
| Composer | PHP dependency management |
| Doctrine | Entities, repositories, migrations and queries |
| MySQL | Transactional and canonical data |
| MongoDB | Raw external payloads and extracted document data |
| S3/LocalStack | Uploaded files and original documents |
| Python | Document parsing and normalization |
| RabbitMQ | Background processing jobs |
| Symfony Messenger | RabbitMQ message handling |
| Kafka | Durable business events |
| Node.js | Kafka notification consumer |
| Neo4j | Project, company and document relationships |
| Airflow | Scheduled permit imports |
| Prometheus | Application and worker metrics |
| Grafana | Dashboards |
| OpenSearch | Centralized structured logs |
| Alertmanager | Operational alerts |
| Docker | Local services and containers |
| Kubernetes | Local deployment using kind |
| Helm | Kubernetes packaging |
| Terraform | AWS infrastructure definitions |
| Jenkins | CI/CD pipeline |
| GitHub Actions | Pull-request automation |
| Trivy | Container vulnerability scanning |
| Dependabot | Dependency update automation |
| PHPUnit | PHP tests |
| pytest | Python tests |
| Node test runner | Node.js tests |

## 4. Architecture

```text
                         ┌─────────────────┐
                         │  Web / API User  │
                         └────────┬────────┘
                                  │
                         ┌────────▼────────┐
                         │  Symfony API    │
                         └────────┬────────┘
                                  │
        ┌─────────────────────────┼─────────────────────────┐
        │                         │                         │
 ┌──────▼──────┐          ┌───────▼──────┐          ┌───────▼──────┐
 │    MySQL    │          │   RabbitMQ   │          │    Kafka     │
 │ Clean data  │          │ Background   │          │ Domain events│
 └─────────────┘          │ jobs         │          └───────┬──────┘
                          └───────┬──────┘                  │
                                  │                  ┌───────▼──────┐
                          ┌───────▼──────┐           │ Node.js      │
                          │ Python       │           │ Consumer     │
                          │ Processor    │           └──────────────┘
                          └──────────────┘

 ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
 │  MongoDB    │  │ S3/LocalStack│  │   Neo4j     │
 │ Raw data    │  │ Documents    │  │ Relationships│
 └─────────────┘  └─────────────┘  └─────────────┘
```

## 5. Symfony modules

```text
src/
├── Identity/
├── Organizations/
├── Projects/
├── Contractors/
├── Permits/
├── Inspections/
├── Documents/
├── Compliance/
├── Notifications/
├── Reporting/
└── Shared/
```

Each module should follow:

```text
Module/
├── Application/
├── Domain/
├── Infrastructure/
├── Presentation/
└── Tests/
```

## 6. Main entities

```text
Organization
User
Project
JobSite
Company
ContractorAssignment
Permit
Inspection
Document
DocumentType
ComplianceRequirement
ComplianceCheck
Notification
AuditLog
```

Relationships:

```text
Organization has many Projects
Project has many Contractors
Project has many Permits
Project has many Inspections
Contractor has many Documents
Document belongs to DocumentType
ComplianceCheck belongs to Project and Contractor
```

## 7. Development milestones

### Milestone 0: Foundation

Build:

- Symfony application.
- Composer configuration.
- Docker Compose.
- PHP container.
- MySQL container.
- Health endpoint.
- PHPUnit.
- Makefile.
- `.env.example`.
- GitHub repository.
- Initial README.

Failure drill:

- Stop MySQL and diagnose the health-check failure.

### Milestone 1: Core domain

Build:

- Organizations.
- Users.
- Projects.
- Job sites.
- Contractors.
- Doctrine entities.
- Migrations.
- Fixtures.
- Repositories.
- Basic REST API.

Practice:

- N+1 queries.
- Eager loading.
- Indexes.
- `EXPLAIN`.
- Batching with `flush()` and `clear()`.

### Milestone 2: Document management

Build:

- Document upload API.
- Document metadata.
- Document types.
- File validation.
- S3/LocalStack storage.
- Private file access.
- File hashing.
- Duplicate file detection.
- Audit logging.

Failure drill:

- Upload an invalid file.
- Upload a duplicate file.
- Make storage unavailable.

### Milestone 3: RabbitMQ processing

Build:

- Symfony Messenger.
- Document-processing messages.
- Retry strategy.
- Failure transport.
- Dead-letter handling.
- Idempotent consumers.
- Worker commands.
- Queue metrics.

Failure drills:

- Poison message.
- Worker crash.
- RabbitMQ unavailable.
- Queue backlog.
- Duplicate message delivery.

### Milestone 4: Python document processor

Build:

- Python worker.
- Document metadata extraction.
- Document-type detection.
- Expiration-date extraction.
- pytest tests.
- Local service communication.

Example output:

```json
{
  "document_type": "insurance_certificate",
  "expires_at": "2027-05-30",
  "company_name": "ABC Electrical",
  "confidence": 0.94
}
```

### Milestone 5: Compliance engine

Build:

- Compliance requirements.
- Project requirements.
- Contractor requirements.
- Compliance checks.
- Missing document detection.
- Expiration detection.
- Compliance status.
- API endpoints.
- Unit and integration tests.

Statuses:

```text
compliant
expiring_soon
expired
missing
rejected
needs_review
```

### Milestone 6: Permit ingestion

Build:

- Public permit-data importer.
- Symfony console command.
- MongoDB raw storage.
- Source hashes.
- Idempotent imports.
- Normalization.
- Permit-to-project matching.

Failure drills:

- Source timeout.
- Invalid JSON.
- Duplicate import.
- Partial import failure.

### Milestone 7: Entity resolution and Neo4j

Build:

- Company normalization.
- Address normalization.
- Exact matching.
- Fuzzy matching.
- Review queue.
- Merge lineage.
- Unmerge support.
- Neo4j constraints.
- Cypher queries.
- Graph relationships.

Example relationships:

```text
Company -[:ASSIGNED_TO]-> Project
Company -[:HAS_DOCUMENT]-> Document
Project -[:HAS_PERMIT]-> Permit
Project -[:LOCATED_AT]-> JobSite
```

Measure:

- Precision.
- Recall.
- False positives.
- False negatives.

### Milestone 8: Kafka and Node.js

Publish:

```text
DocumentUploaded
DocumentProcessed
DocumentExpired
ContractorBecameNonCompliant
PermitApproved
```

Build:

- Kafka topics.
- Partition strategy.
- Consumer groups.
- Offset handling.
- Replay.
- Dead-letter topic.
- Node.js notification consumer.
- Notification read model.

Explain:

```text
RabbitMQ = background work distribution
Kafka = durable business event streaming
```

### Milestone 9: Observability

Build:

- Prometheus metrics.
- Grafana dashboards.
- JSON logs.
- Correlation IDs.
- OpenSearch logging.
- Alertmanager alerts.
- Queue-depth metrics.
- API latency metrics.
- Worker-duration metrics.
- Compliance freshness metrics.

Failure drill:

- Simulate a production incident.
- Find it using logs and metrics.
- Fix it.
- Write a postmortem.

### Milestone 10: Security and Docker

Build:

- Multi-stage Dockerfiles.
- Non-root containers.
- Health checks.
- `.dockerignore`.
- Trivy scanning.
- Composer audit.
- npm audit.
- pip-audit.
- Dependabot.
- Input validation.
- Authorization.
- Secure upload handling.

### Milestone 11: Kubernetes and Helm

Deploy locally using kind:

- Deployments.
- Services.
- ConfigMaps.
- Secrets.
- Readiness probes.
- Liveness probes.
- Resource limits.
- Horizontal Pod Autoscaler.
- Rolling updates.
- Rollbacks.
- Helm values for development and staging.

Failure drills:

- CrashLoopBackOff.
- Bad rollout.
- Failed readiness probe.
- Rollback to the previous version.

### Milestone 12: Terraform and AWS design

Create Terraform modules for:

- S3.
- IAM.
- RDS MySQL.
- VPC basics.
- EKS plan.
- MSK plan.

Use LocalStack where possible.

Do not create expensive AWS resources unless explicitly approved.

### Milestone 13: CI/CD

Build:

- GitHub Actions.
- Jenkinsfile.
- PHP linting.
- PHPUnit.
- Python tests.
- Node.js tests.
- Dependency audits.
- Docker build.
- Trivy scan.
- Helm deployment.
- Smoke tests.
- Rollback stage.

### Milestone 14: Airflow

Build a scheduled DAG that:

1. Downloads permit data.
2. Stores the raw source.
3. Starts normalization.
4. Runs compliance checks.
5. Sends alerts when it fails.

Support:

- Retry.
- Backfill.
- Scheduling.
- Failure alerts.

### Milestone 15: Documentation and interview preparation

Create:

```text
docs/architecture.md
docs/adr/
docs/runbooks/
docs/incidents/
docs/security/
docs/migration-plan.md
docs/learning-log.md
```

For every milestone document:

- What was built.
- Why the design was chosen.
- Tests added.
- Failure drill.
- Lessons learned.
- Interview questions.
- Honest CV claims.
- Suggested Git commit.

## 8. Strict teaching rules

Work on one milestone at a time.

Never start the next milestone until I say `next`.

For each step:

1. Explain the goal simply.
2. Explain the Laravel equivalent where useful.
3. List files to create or modify.
4. Show the exact command.
5. Show the expected output.
6. Explain important code.
7. Add automated tests.
8. Include one failure drill.
9. Explain how to verify the result.
10. Suggest a Git commit message.

Do not generate the entire project at once.

Never add a library without explaining why it is needed.

Use current official documentation and clearly state selected versions.

## 9. Security rules

- Never commit secrets.
- Use `.env.example`.
- Validate external input.
- Use authorization checks.
- Use private file storage.
- Use non-root containers.
- Use least privilege.
- Add audit logs.
- Use idempotent workers.
- Do not expose internal errors to API users.
- Run dependency and image scans.

## 10. Interview preparation

For every technology, prepare answers using:

```text
Problem
Decision
Implementation
Failure
Measurement
Trade-off
Future improvement
```

Do not claim real production experience.

Use:

> I built and tested this as a personal production-style project locally.

Do not use:

> I operated this system in production.

## 11. First action

Before writing code, ask me:

1. Which operating system do I use?
2. Which tools are installed?
3. Do I want PHP locally or inside Docker?
4. How many hours per week can I spend?

Then begin Milestone 0, Step 1 only.

Wait for my confirmation before continuing.