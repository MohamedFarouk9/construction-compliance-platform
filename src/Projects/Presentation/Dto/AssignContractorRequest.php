<?php

declare(strict_types=1);

namespace App\Projects\Presentation\Dto;

use App\Contractors\Domain\Entity\ContractorAssignment;
use Symfony\Component\Validator\Constraints as Assert;

final class AssignContractorRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $companyId = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $roleOnProject = '';

    #[Assert\Choice(choices: ContractorAssignment::ALLOWED_STATUSES)]
    public string $status = 'active';

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $companyId = $payload['company_id'] ?? null;
        $dto->companyId = \is_int($companyId) ? $companyId : (\is_string($companyId) && ctype_digit($companyId) ? (int) $companyId : null);
        $dto->roleOnProject = \is_string($payload['role_on_project'] ?? null) ? trim($payload['role_on_project']) : '';
        $dto->status = \is_string($payload['status'] ?? null) ? trim($payload['status']) : 'active';

        return $dto;
    }
}
