<?php

declare(strict_types=1);

namespace App\Identity\Domain\Entity;

use App\Identity\Infrastructure\Repository\UserRepository;
use App\Organizations\Domain\Entity\Organization;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'idx_users_role', columns: ['role'])]
#[ORM\Index(name: 'idx_users_org_role', columns: ['organization_id', 'role'])]
class User
{
    public const ALLOWED_ROLES = [
        'admin',
        'project_manager',
        'safety_officer',
        'contractor_admin',
        'auditor',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Organization $organization;

    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 150)]
    private string $fullName;

    #[ORM\Column(length: 40)]
    private string $role;

    #[ORM\Column]
    private bool $isActive;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Organization $organization,
        string $email,
        string $fullName,
        string $role = 'project_manager',
        bool $isActive = true,
    ) {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->organization = $organization;
        $this->email = strtolower(trim($email));
        $this->fullName = trim($fullName);
        $this->role = $role;
        $this->isActive = $isActive;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization->getId(),
            'organization_name' => $this->organization->getName(),
            'email' => $this->email,
            'full_name' => $this->fullName,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
