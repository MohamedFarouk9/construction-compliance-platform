<?php

declare(strict_types=1);

namespace App\Shared\Tests\Unit;

use App\Shared\Application\Health\HealthChecker;
use App\Shared\Domain\Health\DatabaseHealthProbeInterface;
use PHPUnit\Framework\TestCase;

final class HealthCheckerTest extends TestCase
{
    public function testReturnsHealthyResultWhenDatabaseIsReachable(): void
    {
        $probe = new class implements DatabaseHealthProbeInterface {
            public function isReachable(): bool
            {
                return true;
            }
        };

        $checker = new HealthChecker($probe);
        $result = $checker->check();
        $payload = $result->toArray();

        self::assertTrue($result->isHealthy());
        self::assertSame('buildguard-api', $payload['service']);
        self::assertSame('ok', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertNotFalse(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $payload['timestamp']));
        self::assertSame('UTC', $result->checkedAt->getTimezone()->getName());
    }

    public function testReturnsDegradedResultWhenDatabaseIsUnreachable(): void
    {
        $probe = new class implements DatabaseHealthProbeInterface {
            public function isReachable(): bool
            {
                return false;
            }
        };

        $checker = new HealthChecker($probe);
        $result = $checker->check();
        $payload = $result->toArray();

        self::assertFalse($result->isHealthy());
        self::assertSame('buildguard-api', $payload['service']);
        self::assertSame('degraded', $payload['status']);
        self::assertSame('down', $payload['checks']['database']['status']);
    }
}
