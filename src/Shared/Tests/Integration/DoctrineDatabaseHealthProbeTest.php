<?php

declare(strict_types=1);

namespace App\Shared\Tests\Integration;

use App\Shared\Infrastructure\Health\DoctrineDatabaseHealthProbe;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

final class DoctrineDatabaseHealthProbeTest extends TestCase
{
    public function testIsReachableReturnsTrueWithWorkingSqliteConnection(): void
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $probe = new DoctrineDatabaseHealthProbe($connection, new NullLogger());

        self::assertTrue($probe->isReachable());
    }

    public function testIsReachableLogsErrorAndReturnsFalseWhenConnectionFails(): void
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => '/nonexistent-dir/buildguard/invalid.sqlite',
        ]);

        $logger = new class extends AbstractLogger {
            /** @var list<array{level: mixed, message: string, context: array<mixed>}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [
                    'level' => $level,
                    'message' => (string) $message,
                    'context' => $context,
                ];
            }
        };

        $probe = new DoctrineDatabaseHealthProbe($connection, $logger);

        self::assertFalse($probe->isReachable());
        self::assertCount(1, $logger->records);
        self::assertSame('error', $logger->records[0]['level']);
        self::assertSame('Database health check failed.', $logger->records[0]['message']);
        self::assertArrayHasKey('exception_class', $logger->records[0]['context']);
        self::assertArrayHasKey('message', $logger->records[0]['context']);
    }
}
