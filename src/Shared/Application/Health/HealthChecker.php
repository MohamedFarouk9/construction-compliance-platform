<?php

declare(strict_types=1);

namespace App\Shared\Application\Health;

use App\Shared\Domain\Health\DatabaseHealthProbeInterface;

final class HealthChecker
{
    public function __construct(
        private readonly DatabaseHealthProbeInterface $databaseProbe,
    ) {
    }

    public function check(): HealthCheckResult
    {
        $isDatabaseUp = $this->databaseProbe->isReachable();

        return new HealthCheckResult(
            'buildguard-api',
            $isDatabaseUp,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
