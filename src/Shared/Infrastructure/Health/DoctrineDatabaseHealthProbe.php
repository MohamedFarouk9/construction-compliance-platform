<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Health;

use App\Shared\Domain\Health\DatabaseHealthProbeInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

final class DoctrineDatabaseHealthProbe implements DatabaseHealthProbeInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isReachable(): bool
    {
        try {
            $this->connection->executeQuery('SELECT 1')->fetchOne();

            return true;
        } catch (\Throwable $exception) {
            $this->logger->error('Database health check failed.', [
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            $this->connection->close();

            return false;
        }
    }
}
