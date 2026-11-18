# BuildGuard Architecture

## System overview

```text
Users
  |
  v
Symfony REST API
  |
  ├── MySQL
  ├── MongoDB
  ├── Object Storage
  ├── RabbitMQ
  ├── Kafka
  └── Neo4j