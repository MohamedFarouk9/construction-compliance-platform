<?php

declare(strict_types=1);

namespace App\Contractors\Presentation\Controller;

use App\Contractors\Domain\Entity\Company;
use App\Contractors\Infrastructure\Repository\CompanyRepository;
use App\Contractors\Presentation\Dto\CreateContractorRequest;
use App\Shared\Presentation\Http\JsonRequestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contractors')]
final class ContractorController
{
    public function __construct(
        private readonly CompanyRepository $companies,
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonRequestHelper $requestHelper,
    ) {
    }

    #[Route('', name: 'api_contractors_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $trade = $request->query->getString('trade') ?: null;
        $status = $request->query->getString('status') ?: null;

        $items = array_map(
            static fn (Company $company): array => $company->toArray(),
            $this->companies->findFiltered($trade, $status),
        );

        return new JsonResponse(['data' => $items, 'count' => \count($items)]);
    }

    #[Route('/{id<\d+>}', name: 'api_contractors_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $company = $this->companies->find($id);
        if ($company === null) {
            return new JsonResponse(['error' => 'Contractor not found.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $company->toArray()]);
    }

    #[Route('', name: 'api_contractors_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateContractorRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        if ($dto->licenseNumber !== null && $this->companies->findOneByLicenseNumber($dto->licenseNumber) !== null) {
            return new JsonResponse(
                ['error' => 'A contractor with this license number already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        $company = new Company($dto->name, $dto->trade, $dto->licenseNumber, $dto->taxId, $dto->status);
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $company->toArray()], Response::HTTP_CREATED);
    }
}
