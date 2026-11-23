<?php

declare(strict_types=1);

namespace App\Contractors\Domain\Entity;

use App\Contractors\Infrastructure\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: 'companies')]
#[ORM\Index(name: 'idx_companies_normalized_name', columns: ['normalized_name'])]
#[ORM\Index(name: 'idx_companies_trade', columns: ['trade'])]
#[ORM\Index(name: 'idx_companies_status', columns: ['status'])]
class Company
{
    public const ALLOWED_STATUSES = [
        'pending',
        'compliant',
        'conditional',
        'non_compliant',
        'suspended',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $normalizedName;

    #[ORM\Column(length: 80)]
    private string $trade;

    #[ORM\Column(length: 80, unique: true, nullable: true)]
    private ?string $licenseNumber;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $taxId;

    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ContractorAssignment> */
    #[ORM\OneToMany(targetEntity: ContractorAssignment::class, mappedBy: 'company')]
    private Collection $assignments;

    public function __construct(
        string $name,
        string $trade,
        ?string $licenseNumber = null,
        ?string $taxId = null,
        string $status = 'pending',
    ) {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->name = trim($name);
        $this->normalizedName = self::normalizeCompanyName($this->name);
        $this->trade = strtolower(trim($trade));
        $this->licenseNumber = $licenseNumber !== null && trim($licenseNumber) !== '' ? strtoupper(trim($licenseNumber)) : null;
        $this->taxId = $taxId !== null && trim($taxId) !== '' ? trim($taxId) : null;
        $this->status = $status;
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->assignments = new ArrayCollection();
    }

    public static function normalizeCompanyName(string $name): string
    {
        $normalized = strtolower(trim($name));
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b(llc|inc|corp|corporation|ltd|co)\b/', '', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNormalizedName(): string
    {
        return $this->normalizedName;
    }

    public function getTrade(): string
    {
        return $this->trade;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
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
            'name' => $this->name,
            'normalized_name' => $this->normalizedName,
            'trade' => $this->trade,
            'license_number' => $this->licenseNumber,
            'tax_id' => $this->taxId,
            'status' => $this->status,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
