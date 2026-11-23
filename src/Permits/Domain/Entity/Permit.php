<?php

declare(strict_types=1);

namespace App\Permits\Domain\Entity;

use App\Permits\Infrastructure\Repository\PermitRepository;
use App\Projects\Domain\Entity\Project;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermitRepository::class)]
#[ORM\Table(name: 'permits')]
#[ORM\Index(name: 'idx_permits_status', columns: ['status'])]
#[ORM\Index(name: 'idx_permits_type', columns: ['permit_type'])]
#[ORM\Index(name: 'idx_permits_expires_at', columns: ['expires_at'])]
#[ORM\Index(name: 'idx_permits_project_status', columns: ['project_id', 'status'])]
class Permit
{
    public const ALLOWED_TYPES = [
        'building',
        'electrical',
        'plumbing',
        'mechanical',
        'environmental',
    ];

    public const ALLOWED_STATUSES = [
        'draft',
        'submitted',
        'under_review',
        'approved',
        'expired',
        'closed',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'permits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\Column(length: 80, unique: true)]
    private string $permitNumber;

    #[ORM\Column(length: 50)]
    private string $permitType;

    #[ORM\Column(length: 150)]
    private string $issuingAuthority;

    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $issuedAt;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Project $project,
        string $permitNumber,
        string $permitType,
        string $issuingAuthority,
        string $status = 'submitted',
        ?\DateTimeImmutable $issuedAt = null,
        ?\DateTimeImmutable $expiresAt = null,
    ) {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->project = $project;
        $this->permitNumber = strtoupper(trim($permitNumber));
        $this->permitType = strtolower(trim($permitType));
        $this->issuingAuthority = trim($issuingAuthority);
        $this->status = $status;
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getPermitNumber(): string
    {
        return $this->permitNumber;
    }

    public function getPermitType(): string
    {
        return $this->permitType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project->getId(),
            'project_name' => $this->project->getName(),
            'permit_number' => $this->permitNumber,
            'permit_type' => $this->permitType,
            'issuing_authority' => $this->issuingAuthority,
            'status' => $this->status,
            'issued_at' => $this->issuedAt?->format('Y-m-d'),
            'expires_at' => $this->expiresAt?->format('Y-m-d'),
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
