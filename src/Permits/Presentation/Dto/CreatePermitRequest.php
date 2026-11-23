<?php

declare(strict_types=1);

namespace App\Permits\Presentation\Dto;

use App\Permits\Domain\Entity\Permit;
use Symfony\Component\Validator\Constraints as Assert;

final class CreatePermitRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $projectId = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 80)]
    public string $permitNumber = '';

    #[Assert\Choice(choices: Permit::ALLOWED_TYPES)]
    public string $permitType = 'building';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 150)]
    public string $issuingAuthority = '';

    #[Assert\Choice(choices: Permit::ALLOWED_STATUSES)]
    public string $status = 'submitted';

    #[Assert\Date]
    public ?string $issuedAt = null;

    #[Assert\Date]
    public ?string $expiresAt = null;

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $projectId = $payload['project_id'] ?? null;
        $dto->projectId = \is_int($projectId) ? $projectId : (\is_string($projectId) && ctype_digit($projectId) ? (int) $projectId : null);
        $dto->permitNumber = \is_string($payload['permit_number'] ?? null) ? trim($payload['permit_number']) : '';
        $dto->permitType = \is_string($payload['permit_type'] ?? null) ? trim($payload['permit_type']) : 'building';
        $dto->issuingAuthority = \is_string($payload['issuing_authority'] ?? null) ? trim($payload['issuing_authority']) : '';
        $dto->status = \is_string($payload['status'] ?? null) ? trim($payload['status']) : 'submitted';
        $dto->issuedAt = \is_string($payload['issued_at'] ?? null) && trim($payload['issued_at']) !== '' ? trim($payload['issued_at']) : null;
        $dto->expiresAt = \is_string($payload['expires_at'] ?? null) && trim($payload['expires_at']) !== '' ? trim($payload['expires_at']) : null;

        return $dto;
    }
}
