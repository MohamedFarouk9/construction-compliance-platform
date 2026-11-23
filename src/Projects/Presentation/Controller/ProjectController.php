<?php

declare(strict_types=1);

namespace App\Projects\Presentation\Controller;

use App\Contractors\Domain\Entity\ContractorAssignment;
use App\Contractors\Infrastructure\Repository\CompanyRepository;
use App\Contractors\Infrastructure\Repository\ContractorAssignmentRepository;
use App\Organizations\Infrastructure\Repository\OrganizationRepository;
use App\Projects\Domain\Entity\JobSite;
use App\Projects\Domain\Entity\Project;
use App\Projects\Infrastructure\Repository\ProjectRepository;
use App\Projects\Presentation\Dto\AssignContractorRequest;
use App\Projects\Presentation\Dto\CreateJobSiteRequest;
use App\Projects\Presentation\Dto\CreateProjectRequest;
use App\Shared\Presentation\Http\JsonRequestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projects')]
final class ProjectController
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly OrganizationRepository $organizations,
        private readonly CompanyRepository $companies,
        private readonly ContractorAssignmentRepository $assignments,
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonRequestHelper $requestHelper,
    ) {
    }

    #[Route('', name: 'api_projects_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $orgParam = $request->query->get('organization_id');
        $organizationId = \is_string($orgParam) && ctype_digit($orgParam) ? (int) $orgParam : null;
        $status = $request->query->getString('status') ?: null;

        $items = array_map(
            static fn (Project $project): array => $project->toArray(includeRelations: true),
            $this->projects->findAllWithDetails($organizationId, $status),
        );

        return new JsonResponse(['data' => $items, 'count' => \count($items)]);
    }

    #[Route('/{id<\d+>}', name: 'api_projects_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $project = $this->projects->findOneWithDetails($id);
        if ($project === null) {
            return new JsonResponse(['error' => 'Project not found.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $project->toArray(includeRelations: true)]);
    }

    #[Route('', name: 'api_projects_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateProjectRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        $organization = $this->organizations->find($dto->organizationId);
        if ($organization === null) {
            return new JsonResponse(['error' => 'Organization not found.'], Response::HTTP_NOT_FOUND);
        }

        if ($this->projects->findOneByCode($dto->code) !== null) {
            return new JsonResponse(
                ['error' => 'A project with this code already exists.'],
                Response::HTTP_CONFLICT,
            );
        }

        $startsAt = $dto->startsAt !== null ? new \DateTimeImmutable($dto->startsAt) : null;
        $endsAt = $dto->endsAt !== null ? new \DateTimeImmutable($dto->endsAt) : null;

        if ($startsAt !== null && $endsAt !== null && $endsAt < $startsAt) {
            return new JsonResponse(
                [
                    'error' => 'Validation failed.',
                    'details' => ['endsAt' => ['End date must be on or after start date.']],
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $project = new Project($organization, $dto->name, $dto->code, $dto->status, $startsAt, $endsAt);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $project->toArray()], Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}/job-sites', name: 'api_projects_add_job_site', methods: ['POST'])]
    public function addJobSite(int $id, Request $request): JsonResponse
    {
        $project = $this->projects->find($id);
        if ($project === null) {
            return new JsonResponse(['error' => 'Project not found.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = CreateJobSiteRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        $jobSite = new JobSite(
            $project,
            $dto->name,
            $dto->addressLine1,
            $dto->city,
            $dto->state,
            $dto->postalCode,
            $dto->jurisdiction,
        );
        $this->entityManager->persist($jobSite);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $jobSite->toArray()], Response::HTTP_CREATED);
    }

    #[Route('/{id<\d+>}/contractors', name: 'api_projects_assign_contractor', methods: ['POST'])]
    public function assignContractor(int $id, Request $request): JsonResponse
    {
        $project = $this->projects->find($id);
        if ($project === null) {
            return new JsonResponse(['error' => 'Project not found.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $this->requestHelper->decodeBody($request);
        if ($payload instanceof JsonResponse) {
            return $payload;
        }

        $dto = AssignContractorRequest::fromArray($payload);
        if ($errorResponse = $this->requestHelper->validate($dto)) {
            return $errorResponse;
        }

        $company = $this->companies->find($dto->companyId);
        if ($company === null) {
            return new JsonResponse(['error' => 'Contractor company not found.'], Response::HTTP_NOT_FOUND);
        }

        $existing = $this->assignments->findOneBy(['project' => $project, 'company' => $company]);
        if ($existing !== null) {
            return new JsonResponse(
                ['error' => 'This contractor is already assigned to the project.'],
                Response::HTTP_CONFLICT,
            );
        }

        $assignment = new ContractorAssignment($project, $company, $dto->roleOnProject, $dto->status);
        $this->entityManager->persist($assignment);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $assignment->toArray()], Response::HTTP_CREATED);
    }
}
