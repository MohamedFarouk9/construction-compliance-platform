<?php

declare(strict_types=1);

namespace App\Projects\Presentation\Dto;

use App\Projects\Domain\Entity\Project;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateProjectRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $organizationId = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 180)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9_-]+$/', message: 'Code may contain only letters, numbers, hyphens, and underscores.')]
    public string $code = '';

    #[Assert\Choice(choices: Project::ALLOWED_STATUSES)]
    public string $status = 'active';

    #[Assert\Date]
    public ?string $startsAt = null;

    #[Assert\Date]
    public ?string $endsAt = null;

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $orgId = $payload['organization_id'] ?? null;
        $dto->organizationId = \is_int($orgId) ? $orgId : (\is_string($orgId) && ctype_digit($orgId) ? (int) $orgId : null);
        $dto->name = \is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
        $dto->code = \is_string($payload['code'] ?? null) ? trim($payload['code']) : '';
        $dto->status = \is_string($payload['status'] ?? null) ? trim($payload['status']) : 'active';
        $dto->startsAt = \is_string($payload['starts_at'] ?? null) && trim($payload['starts_at']) !== '' ? trim($payload['starts_at']) : null;
        $dto->endsAt = \is_string($payload['ends_at'] ?? null) && trim($payload['ends_at']) !== '' ? trim($payload['ends_at']) : null;

        return $dto;
    }
}
