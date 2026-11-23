<?php

declare(strict_types=1);

namespace App\Projects\Tests\Functional;

use App\Contractors\Domain\Entity\Company;
use App\Contractors\Domain\Entity\ContractorAssignment;
use App\Organizations\Domain\Entity\Organization;
use App\Permits\Domain\Entity\Permit;
use App\Projects\Domain\Entity\JobSite;
use App\Projects\Domain\Entity\Project;
use App\Projects\Infrastructure\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CoreDomainApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $em;

        $this->purgeTables();
    }

    public function testFullCoreDomainWorkflowCreateAndList(): void
    {
        // 1. Create Organization
        $orgPayload = $this->postJson('/api/organizations', [
            'name' => 'Apex Builders',
            'code' => 'APEX-01',
            'status' => 'active',
        ], Response::HTTP_CREATED);
        $orgId = $orgPayload['data']['id'];
        self::assertSame('APEX-01', $orgPayload['data']['code']);

        // Duplicate Organization code -> 409 Conflict
        $this->postJson('/api/organizations', [
            'name' => 'Duplicate Apex',
            'code' => 'apex-01',
        ], Response::HTTP_CONFLICT);

        // 2. Create User
        $userPayload = $this->postJson('/api/users', [
            'organization_id' => $orgId,
            'email' => 'pm@apex.example',
            'full_name' => 'Marcus Vance',
            'role' => 'project_manager',
        ], Response::HTTP_CREATED);
        self::assertSame('pm@apex.example', $userPayload['data']['email']);

        // 3. Create Project
        $projectPayload = $this->postJson('/api/projects', [
            'organization_id' => $orgId,
            'name' => 'Downtown Tower',
            'code' => 'DT-100',
            'status' => 'active',
            'starts_at' => '2026-01-15',
            'ends_at' => '2027-12-31',
        ], Response::HTTP_CREATED);
        $projectId = $projectPayload['data']['id'];
        self::assertSame('DT-100', $projectPayload['data']['code']);

        // 4. Add JobSite
        $sitePayload = $this->postJson("/api/projects/{$projectId}/job-sites", [
            'name' => 'North Core Parcel',
            'address_line_1' => '450 Main St',
            'city' => 'Austin',
            'state' => 'tx',
            'postal_code' => '78701',
            'jurisdiction' => 'City of Austin',
        ], Response::HTTP_CREATED);
        self::assertSame('TX', $sitePayload['data']['state']);

        // 5. Create Contractor Company
        $contractorPayload = $this->postJson('/api/contractors', [
            'name' => 'ABC Electrical LLC',
            'trade' => 'Electrical',
            'license_number' => 'TX-EL-1001',
            'status' => 'non_compliant',
        ], Response::HTTP_CREATED);
        $companyId = $contractorPayload['data']['id'];
        self::assertSame('abc electrical', $contractorPayload['data']['normalized_name']);

        // 6. Assign Contractor to Project
        $assignmentPayload = $this->postJson("/api/projects/{$projectId}/contractors", [
            'company_id' => $companyId,
            'role_on_project' => 'Primary Electrical',
            'status' => 'active',
        ], Response::HTTP_CREATED);
        self::assertSame('ABC Electrical LLC', $assignmentPayload['data']['company_name']);

        // Duplicate assignment -> 409 Conflict
        $this->postJson("/api/projects/{$projectId}/contractors", [
            'company_id' => $companyId,
            'role_on_project' => 'Duplicate Electrical',
        ], Response::HTTP_CONFLICT);

        // 7. Create Permit
        $permitPayload = $this->postJson('/api/permits', [
            'project_id' => $projectId,
            'permit_number' => 'AUS-BLD-001',
            'permit_type' => 'building',
            'issuing_authority' => 'City of Austin',
            'status' => 'approved',
            'issued_at' => '2026-01-10',
            'expires_at' => '2027-07-10',
        ], Response::HTTP_CREATED);
        self::assertSame('AUS-BLD-001', $permitPayload['data']['permit_number']);

        // 8. Fetch Project Detail with Eager-Loaded Relations
        $this->client->request('GET', "/api/projects/{$projectId}");
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $detail = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertCount(1, $detail['data']['job_sites']);
        self::assertCount(1, $detail['data']['contractors']);
        self::assertCount(1, $detail['data']['permits']);

        // 9. Verify List Endpoints
        foreach (['/api/organizations', '/api/users', '/api/projects', '/api/contractors', '/api/permits'] as $uri) {
            $this->client->request('GET', $uri);
            self::assertResponseStatusCodeSame(Response::HTTP_OK);
            $list = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
            self::assertSame(1, $list['count']);
        }
    }

    public function testValidationAndErrorResponses(): void
    {
        // Malformed JSON -> 400
        $this->client->request('POST', '/api/organizations', [], [], ['CONTENT_TYPE' => 'application/json'], '{bad-json');
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // Invalid DTO fields -> 422
        $invalidUser = $this->postJson('/api/users', [
            'organization_id' => 1,
            'email' => 'not-an-email',
            'full_name' => 'A',
            'role' => 'super_hacker',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('details', $invalidUser);
        self::assertArrayHasKey('email', $invalidUser['details']);
        self::assertArrayHasKey('role', $invalidUser['details']);

        // Missing parent organization -> 404
        $this->postJson('/api/projects', [
            'organization_id' => 999999,
            'name' => 'Ghost Tower',
            'code' => 'GHOST-1',
        ], Response::HTTP_NOT_FOUND);
    }

    public function testProjectRepositoryEagerLoadingInitializesAllRelationsWithoutNPlusOne(): void
    {
        $org = new Organization('Eager Corp', 'EAGER');
        $company = new Company('Steel Pro LLC', 'steel', 'LIC-999', null, 'compliant');
        $this->entityManager->persist($org);
        $this->entityManager->persist($company);

        for ($i = 1; $i <= 4; ++$i) {
            $project = new Project($org, "Tower {$i}", "TWR-{$i}");
            $this->entityManager->persist($project);
            $this->entityManager->persist(new JobSite($project, "Site {$i}", "{$i}00 Oak St", 'Austin', 'TX', '78701', 'City of Austin'));
            $this->entityManager->persist(new ContractorAssignment($project, $company, 'Steel Erection'));
            $this->entityManager->persist(new Permit($project, "PRM-{$i}", 'building', 'City of Austin', 'approved'));
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var ProjectRepository $repository */
        $repository = static::getContainer()->get(ProjectRepository::class);
        $projects = $repository->findAllWithDetails();

        self::assertCount(4, $projects);

        foreach ($projects as $project) {
            self::assertFalse($this->entityManager->isUninitializedObject($project->getOrganization()));

            $jobSites = $project->getJobSites();
            $assignments = $project->getContractorAssignments();
            $permits = $project->getPermits();

            self::assertInstanceOf(PersistentCollection::class, $jobSites);
            self::assertInstanceOf(PersistentCollection::class, $assignments);
            self::assertInstanceOf(PersistentCollection::class, $permits);
            self::assertTrue($jobSites->isInitialized());
            self::assertTrue($assignments->isInitialized());
            self::assertTrue($permits->isInitialized());

            foreach ($assignments as $assignment) {
                self::assertFalse($this->entityManager->isUninitializedObject($assignment->getCompany()));
            }
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function postJson(string $uri, array $body, int $expectedStatus): array
    {
        $this->client->request(
            'POST',
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame($expectedStatus);
        self::assertResponseHeaderSame('content-type', 'application/json');

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    private function purgeTables(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['permits', 'contractor_assignments', 'job_sites', 'projects', 'companies', 'users', 'organizations'] as $table) {
            $connection->executeStatement("DELETE FROM {$table}");
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        $this->entityManager->clear();
    }
}
