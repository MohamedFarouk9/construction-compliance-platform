<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Controller;

use App\Identity\Domain\Entity\User;
use App\Identity\Infrastructure\Repository\UserRepository;
use App\Identity\Presentation\Dto\CreateUserRequest;
use App\Organizations\Infrastructure\Repository\OrganizationRepository;
use App\Shared\Presentation\Http\JsonRequestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
final class UserController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly OrganizationRepository $organizations,
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonRequestHelper $requestHelper,
    ) {
    }

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $orgParam = $request->query->get('organization_id');
        $organizationId = \is_string($orgParam) && ctype_digit($orgParam) ? (int) $orgParam : null;
        $role = $request->query->getString('role') ?: null;

        $items = array_map(
            static fn (User $user): array => $user->toArray(),
            $this->users->findAllWithOrganization($organizationId, $role),
        );

        return new JsonResponse(['data' => $items, 'count' => \count($items)]);
    }

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateUserRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        $organization = $this->organizations->find($dto->organizationId);
        if ($organization === null) {
            return new JsonResponse(['error' => 'Organization not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($this->users->findOneByEmail($dto->email) !== null) {
            return new JsonResponse(
                ['error' => 'A user with this email already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        $user = new User($organization, $dto->email, $dto->fullName, $dto->role);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $user->toArray()], Response::HTTP_CREATED);
    }
}
