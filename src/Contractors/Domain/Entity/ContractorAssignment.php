<?php

declare(strict_types=1);

namespace App\Contractors\Domain\Entity;

use App\Contractors\Infrastructure\Repository\ContractorAssignmentRepository;
use App\Projects\Domain\Entity\Project;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContractorAssignmentRepository::class)]
#[ORM\Table(name: 'contractor_assignments')]
#[ORM\UniqueConstraint(name: 'uniq_project_company_assignment', columns: ['project_id', 'company_id'])]
#[ORM\Index(name: 'idx_contractor_assignments_status', columns: ['status'])]
class ContractorAssignment
{
    public const ALLOWED_STATUSES = [
        'assigned',
        'active',
        'completed',
        'suspended',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'contractorAssignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Company $company;

    #[ORM\Column(length: 100)]
    private string $roleOnProject;

    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column]
    private \DateTimeImmutable $assignedAt;

    public function __construct(
        Project $project,
        Company $company,
        string $roleOnProject,
        string $status = 'active',
    ) {
        $this->project = $project;
        $this->company = $company;
        $this->roleOnProject = trim($roleOnProject);
        $this->status = $status;
        $this->assignedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getRoleOnProject(): string
    {
        return $this->roleOnProject;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project->getId(),
            'company_id' => $this->company->getId(),
            'company_name' => $this->company->getName(),
            'trade' => $this->company->getTrade(),
            'company_status' => $this->company->getStatus(),
            'role_on_project' => $this->roleOnProject,
            'status' => $this->status,
            'assigned_at' => $this->assignedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
