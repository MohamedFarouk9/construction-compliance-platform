<?php

declare(strict_types=1);

namespace App\Shared\Domain\Health;

interface DatabaseHealthProbeInterface
{
    public function isReachable(): bool;
}
