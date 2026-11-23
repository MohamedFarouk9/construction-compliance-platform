<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Dto;

use App\Identity\Domain\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateUserRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $organizationId = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 150)]
    public string $fullName = '';

    #[Assert\Choice(choices: User::ALLOWED_ROLES)]
    public string $role = 'project_manager';

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $orgId = $payload['organization_id'] ?? null;
        $dto->organizationId = \is_int($orgId) ? $orgId : (\is_string($orgId) && ctype_digit($orgId) ? (int) $orgId : null);
        $dto->email = \is_string($payload['email'] ?? null) ? trim($payload['email']) : '';
        $dto->fullName = \is_string($payload['full_name'] ?? null) ? trim($payload['full_name']) : '';
        $dto->role = \is_string($payload['role'] ?? null) ? trim($payload['role']) : 'project_manager';

        return $dto;
    }
}
