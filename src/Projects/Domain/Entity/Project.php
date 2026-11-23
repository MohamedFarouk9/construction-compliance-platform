<?php

declare(strict_types=1);

namespace App\Projects\Domain\Entity;

use App\Contractors\Domain\Entity\ContractorAssignment;
use App\Organizations\Domain\Entity\Organization;
use App\Permits\Domain\Entity\Permit;
use App\Projects\Infrastructure\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
#[ORM\Index(name: 'idx_projects_status', columns: ['status'])]
#[ORM\Index(name: 'idx_projects_org_status', columns: ['organization_id', 'status'])]
class Project
{
    public const ALLOWED_STATUSES = [
        'planning',
        'active',
        'on_hold',
        'completed',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Organization $organization;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 50, unique: true)]
    private string $code;

    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startsAt;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endsAt;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, JobSite> */
    #[ORM\OneToMany(targetEntity: JobSite::class, mappedBy: 'project')]
    private Collection $jobSites;

    /** @var Collection<int, ContractorAssignment> */
    #[ORM\OneToMany(targetEntity: ContractorAssignment::class, mappedBy: 'project')]
    private Collection $contractorAssignments;

    /** @var Collection<int, Permit> */
    #[ORM\OneToMany(targetEntity: Permit::class, mappedBy: 'project')]
    private Collection $permits;

    public function __construct(
        Organization $organization,
        string $name,
        string $code,
        string $status = 'active',
        ?\DateTimeImmutable $startsAt = null,
        ?\DateTimeImmutable $endsAt = null,
    ) {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->organization = $organization;
        $this->name = trim($name);
        $this->code = strtoupper(trim($code));
        $this->status = $status;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->jobSites = new ArrayCollection();
        $this->contractorAssignments = new ArrayCollection();
        $this->permits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    /** @return Collection<int, JobSite> */
    public function getJobSites(): Collection
    {
        return $this->jobSites;
    }

    /** @return Collection<int, ContractorAssignment> */
    public function getContractorAssignments(): Collection
    {
        return $this->contractorAssignments;
    }

    /** @return Collection<int, Permit> */
    public function getPermits(): Collection
    {
        return $this->permits;
    }

    /** @return array<string, mixed> */
    public function toArray(bool $includeRelations = false): array
    {
        $data = [
            'id' => $this->id,
            'organization_id' => $this->organization->getId(),
            'organization_name' => $this->organization->getName(),
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'starts_at' => $this->startsAt?->format('Y-m-d'),
            'ends_at' => $this->endsAt?->format('Y-m-d'),
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];

        if ($includeRelations) {
            $data['job_sites'] = array_map(
                static fn (JobSite $site): array => $site->toArray(),
                $this->jobSites->toArray(),
            );
            $data['contractors'] = array_map(
                static fn (ContractorAssignment $assignment): array => $assignment->toArray(),
                $this->contractorAssignments->toArray(),
            );
            $data['permits'] = array_map(
                static fn (Permit $permit): array => $permit->toArray(),
                $this->permits->toArray(),
            );
        }

        return $data;
    }
}
