<?php

declare(strict_types=1);

namespace App\Shared\Application\Health;

final readonly class HealthCheckResult
{
    public function __construct(
        public string $service,
        public bool $databaseUp,
        public \DateTimeImmutable $checkedAt,
    ) {
    }

    public function isHealthy(): bool
    {
        return $this->databaseUp;
    }

    /**
     * @return array{
     *     service: string,
     *     status: 'ok'|'degraded',
     *     checks: array{
     *         database: array{status: 'up'|'down'}
     *     },
     *     timestamp: string
     * }
     */
    public function toArray(): array
    {
        return [
            'service' => $this->service,
            'status' => $this->isHealthy() ? 'ok' : 'degraded',
            'checks' => [
                'database' => [
                    'status' => $this->databaseUp ? 'up' : 'down',
                ],
            ],
            'timestamp' => $this->checkedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
