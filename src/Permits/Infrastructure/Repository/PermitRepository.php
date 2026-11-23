<?php

declare(strict_types=1);

namespace App\Permits\Infrastructure\Repository;

use App\Permits\Domain\Entity\Permit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Permit>
 */
final class PermitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permit::class);
    }

    /** @return list<Permit> */
    public function findFiltered(?int $projectId = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('pm')
            ->innerJoin('pm.project', 'p')
            ->addSelect('p')
            ->orderBy('pm.expiresAt', 'ASC');

        if ($projectId !== null) {
            $qb->andWhere('p.id = :projectId')
                ->setParameter('projectId', $projectId);
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('pm.status = :status')
                ->setParameter('status', $status);
        }

        /** @var list<Permit> $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findOneByPermitNumber(string $permitNumber): ?Permit
    {
        return $this->findOneBy(['permitNumber' => strtoupper(trim($permitNumber))]);
    }
}
