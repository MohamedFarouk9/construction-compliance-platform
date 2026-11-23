<?php

declare(strict_types=1);

namespace App\Permits\Presentation\Controller;

use App\Permits\Domain\Entity\Permit;
use App\Permits\Infrastructure\Repository\PermitRepository;
use App\Permits\Presentation\Dto\CreatePermitRequest;
use App\Projects\Infrastructure\Repository\ProjectRepository;
use App\Shared\Presentation\Http\JsonRequestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/permits')]
final class PermitController
{
    public function __construct(
        private readonly PermitRepository $permits,
        private readonly ProjectRepository $projects,
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonRequestHelper $requestHelper,
    ) {
    }

    #[Route('', name: 'api_permits_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $projectParam = $request->query->get('project_id');
        $projectId = \is_string($projectParam) && ctype_digit($projectParam) ? (int) $projectParam : null;
        $status = $request->query->getString('status') ?: null;

        $items = array_map(
            static fn (Permit $permit): array => $permit->toArray(),
            $this->permits->findFiltered($projectId, $status),
        );

        return new JsonResponse(['data' => $items, 'count' => \count($items)]);
    }

    #[Route('', name: 'api_permits_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreatePermitRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        $project = $this->projects->find($dto->projectId);
        if ($project === null) {
            return new JsonResponse(['error' => 'Project not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($this->permits->findOneByPermitNumber($dto->permitNumber) !== null) {
            return new JsonResponse(
                ['error' => 'A permit with this permit number already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        $issuedAt = $dto->issuedAt !== null ? new \DateTimeImmutable($dto->issuedAt) : null;
        $expiresAt = $dto->expiresAt !== null ? new \DateTimeImmutable($dto->expiresAt) : null;

        if ($issuedAt !== null && $expiresAt !== null && $expiresAt < $issuedAt) {
            return new JsonResponse(
                [
                    'error' => 'Validation failed.',
                    'details' => ['expiresAt' => ['Expiration date must be on or after issue date.']],
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $permit = new Permit(
            $project,
            $dto->permitNumber,
            $dto->permitType,
            $dto->issuingAuthority,
            $dto->status,
            $issuedAt,
            $expiresAt,
        );
        $this->entityManager->persist($permit);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $permit->toArray()], Response::HTTP_CREATED);
    }
}
