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

## Architecture

```text
Symfony API
    |
    ├── MySQL: transactional data
    ├── MongoDB: raw external data
    ├── S3/LocalStack: uploaded documents
    ├── RabbitMQ: background jobs
    ├── Kafka: business events
    ├── Neo4j: entity relationships
    ├── Python: document processing
    ├── Node.js: event consumer
    ├── Airflow: scheduled imports
    ├── Prometheus/Grafana: metrics
    └── OpenSearch: logs