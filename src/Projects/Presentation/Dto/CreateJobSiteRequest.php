<?php

declare(strict_types=1);

namespace App\Projects\Presentation\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateJobSiteRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 150)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $addressLine1 = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $city = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    public string $state = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 20)]
    public string $postalCode = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public string $jurisdiction = '';

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        $dto = new self();
        $dto->name = \is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
        $dto->addressLine1 = \is_string($payload['address_line_1'] ?? null) ? trim($payload['address_line_1']) : '';
        $dto->city = \is_string($payload['city'] ?? null) ? trim($payload['city']) : '';
        $dto->state = \is_string($payload['state'] ?? null) ? trim($payload['state']) : '';
        $dto->postalCode = \is_string($payload['postal_code'] ?? null) ? trim($payload['postal_code']) : '';
        $dto->jurisdiction = \is_string($payload['jurisdiction'] ?? null) ? trim($payload['jurisdiction']) : '';

        return $dto;
    }
}
