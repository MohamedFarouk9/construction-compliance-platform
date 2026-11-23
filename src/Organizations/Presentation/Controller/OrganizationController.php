<?php

declare(strict_types=1);

namespace App\Organizations\Presentation\Controller;

use App\Organizations\Domain\Entity\Organization;
use App\Organizations\Infrastructure\Repository\OrganizationRepository;
use App\Organizations\Presentation\Dto\CreateOrganizationRequest;
use App\Shared\Presentation\Http\JsonRequestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/organizations')]
final class OrganizationController
{
    public function __construct(
        private readonly OrganizationRepository $organizations,
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonRequestHelper $requestHelper,
    ) {
    }

    #[Route('', name: 'api_organizations_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = array_map(
            static fn (Organization $org): array => $org->toArray(),
            $this->organizations->findAllOrdered(),
        );

        return new JsonResponse(['data' => $items, 'count' => \count($items)]);
    }

    #[Route('/{id<\d+>}', name: 'api_organizations_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $organization = $this->organizations->find($id);
        if ($organization === null) {
            return new JsonResponse(['error' => 'Organization not found.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $organization->toArray()]);
    }

    #[Route('', name: 'api_organizations_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateOrganizationRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        if ($this->organizations->findOneByCode($dto->code) !== null) {
            return new JsonResponse(
                ['error' => 'An organization with this code already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        $organization = new Organization($dto->name, $dto->code, $dto->status);
        $this->entityManager->persist($organization);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $organization->toArray()], Response::HTTP_CREATED);
    }
}
