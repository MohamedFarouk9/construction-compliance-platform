<?php

declare(strict_types=1);

namespace App\Contractors\Presentation\Dto;

use App\Contractors\Domain\Entity\Company;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateContractorRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 180)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 80)]
    public string $trade = '';

    #[Assert\Length(max: 80)]
    public ?string $licenseNumber = null;

    #[Assert\Length(max: 50)]
    public ?string $taxId = null;

    #[Assert\Choice(choices: Company::ALLOWED_STATUSES)]
    public string $status = 'pending';

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $dto->name = \is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
        $dto->trade = \is_string($payload['trade'] ?? null) ? trim($payload['trade']) : '';
        $dto->licenseNumber = \is_string($payload['license_number'] ?? null) && trim($payload['license_number']) !== '' ? trim($payload['license_number']) : null;
        $dto->taxId = \is_string($payload['tax_id'] ?? null) && trim($payload['tax_id']) !== '' ? trim($payload['tax_id']) : null;
        $dto->status = \is_string($payload['status'] ?? null) ? trim($payload['status']) : 'pending';

        return $dto;
    }
}
