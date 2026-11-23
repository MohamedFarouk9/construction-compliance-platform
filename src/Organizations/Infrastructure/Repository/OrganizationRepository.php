<?php

declare(strict_types=1);

namespace App\Organizations\Infrastructure\Repository;

use App\Organizations\Domain\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
final class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    /** @return list<Organization> */
    public function findAllOrdered(): array
    {
        /** @var list<Organization> $results */
        $results = $this->createQueryBuilder('o')
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function findOneByCode(string $code): ?Organization
    {
        return $this->findOneBy(['code' => strtoupper(trim($code))]);
    }
}
