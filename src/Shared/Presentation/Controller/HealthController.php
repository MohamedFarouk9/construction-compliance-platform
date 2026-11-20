<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Controller;

use App\Shared\Application\Health\HealthChecker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/health', name: 'shared_health', methods: ['GET'])]
final class HealthController
{
    public function __construct(
        private readonly HealthChecker $healthChecker,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $result = $this->healthChecker->check();

        return new JsonResponse(
            $result->toArray(),
            $result->isHealthy() ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
