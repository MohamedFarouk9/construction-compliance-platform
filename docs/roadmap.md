
## `docs/roadmap.md`

```md
# BuildGuard Roadmap

## Milestone 0: Foundation

- Symfony application
- Docker Compose
- MySQL
- Health endpoint
- PHPUnit
- GitHub repository
- README
- Environment configuration

## Milestone 1: Core domain

- Organizations
- Projects
- Contractors
- Permits
- Doctrine entities
- Migrations
- API endpoints
- Fixtures

## Milestone 2: Documents

- Upload documents
- Store metadata
- Store files in LocalStack/S3
- Validate file types
- Add audit logs

## Milestone 3: RabbitMQ

- Symfony Messenger
- Background jobs
- Retries
- Failure transport
- Idempotent consumers

## Milestone 4: Python processing

- Extract document metadata
- Detect expiration dates
- Add pytest tests
- Connect Python worker to Symfony

## Milestone 5: Compliance

- Compliance requirements
- Compliance checks
- Expired and missing documents
- Contractor compliance status

## Milestone 6: Permit ingestion

- Import public permit data
- Store raw data in MongoDB
- Normalize records
- Avoid duplicate imports

## Milestone 7: Entity resolution

- Normalize companies and addresses
- Fuzzy matching
- Review queue
- Neo4j relationships
- Merge lineage

## Milestone 8: Kafka and Node.js

- Publish business events
- Create Kafka consumer
- Build notification read model
- Handle offsets and replay

## Milestone 9: Operations

- Prometheus
- Grafana
- OpenSearch
- Alertmanager
- Incident drill

## Milestone 10: Delivery

- Docker hardening
- Trivy scanning
- Kubernetes
- Helm
- Terraform
- Jenkins
- GitHub Actions
- Airflow

## Definition of done

A milestone is complete when:

- Code is implemented.
- Tests pass.
- Failure behavior is tested.
- Documentation is updated.
- Docker setup works.
- No secrets are committed.
- A Git commit is created.
- Interview notes are written.