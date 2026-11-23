<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Repository;

use App\Identity\Domain\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository
{
    // ManagerRegistry for 
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /** @return list<User> */
    public function findAllWithOrganization(?int $organizationId = null, ?string $role = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.organization', 'o')
            ->addSelect('o')
            ->orderBy('u.fullName', 'ASC');

        if ($organizationId !== null) {
            $qb->andWhere('o.id = :organizationId')
                ->setParameter('organizationId', $organizationId);
        }

        if ($role !== null && $role !== '') {
            $qb->andWhere('u.role = :role')
                ->setParameter('role', $role);
        }

        /** @var list<User> $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => strtolower(trim($email))]);
    }
}
