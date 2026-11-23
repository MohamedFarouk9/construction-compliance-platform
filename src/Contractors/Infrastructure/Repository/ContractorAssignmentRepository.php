<?php

declare(strict_types=1);

namespace App\Contractors\Infrastructure\Repository;

use App\Contractors\Domain\Entity\ContractorAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractorAssignment>
 */
final class ContractorAssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractorAssignment::class);
    }
}
