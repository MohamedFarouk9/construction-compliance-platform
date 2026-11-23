<?php

declare(strict_types=1);

namespace App\Organizations\Presentation\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateOrganizationRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 150)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9_-]+$/', message: 'Code may contain only letters, numbers, hyphens, and underscores.')]
    public string $code = '';

    #[Assert\Choice(choices: ['active', 'inactive'])]
    public string $status = 'active';

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $dto->name = \is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
        $dto->code = \is_string($payload['code'] ?? null) ? trim($payload['code']) : '';
        $dto->status = \is_string($payload['status'] ?? null) ? trim($payload['status']) : 'active';

        return $dto;
    }
}
