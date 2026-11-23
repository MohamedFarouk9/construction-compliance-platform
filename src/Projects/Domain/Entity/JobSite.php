<?php

declare(strict_types=1);

namespace App\Projects\Domain\Entity;

use App\Projects\Infrastructure\Repository\JobSiteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobSiteRepository::class)]
#[ORM\Table(name: 'job_sites')]
#[ORM\Index(name: 'idx_job_sites_jurisdiction', columns: ['jurisdiction'])]
#[ORM\Index(name: 'idx_job_sites_city', columns: ['city'])]
class JobSite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'jobSites')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Project $project;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $addressLine1;

    #[ORM\Column(length: 100)]
    private string $city;

    #[ORM\Column(length: 50)]
    private string $state;

    #[ORM\Column(length: 20)]
    private string $postalCode;

    #[ORM\Column(length: 120)]
    private string $jurisdiction;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Project $project,
        string $name,
        string $addressLine1,
        string $city,
        string $state,
        string $postalCode,
        string $jurisdiction,
    ) {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->project = $project;
        $this->name = trim($name);
        $this->addressLine1 = trim($addressLine1);
        $this->city = trim($city);
        $this->state = strtoupper(trim($state));
        $this->postalCode = trim($postalCode);
        $this->jurisdiction = trim($jurisdiction);
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getJurisdiction(): string
    {
        return $this->jurisdiction;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project->getId(),
            'name' => $this->name,
            'address_line_1' => $this->addressLine1,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'jurisdiction' => $this->jurisdiction,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
