<?php

declare(strict_types=1);

namespace App\Contractors\Infrastructure\Repository;

use App\Contractors\Domain\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
final class CompanyRepository extends ServiceEntityRepository
{
    // ServiceEntityRepository is a ready-made Doctrine repository class. It gives you useful methods such as:
    // find() findAll() findBy() createQueryBuilder() findOneBy() via ServiceEntityRepositoryProxy
    public function __construct(ManagerRegistry $registry)
    {
        // This repository uses Doctrine’s database connection and works with Company entities
        parent::__construct($registry, Company::class);
    }

    /** @return list<Company> */
    public function findFiltered(?string $trade = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC');

        if ($trade !== null && $trade !== '') {
            $qb->andWhere('c.trade = :trade')
                ->setParameter('trade', strtolower(trim($trade)));
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        /** @var list<Company> $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findOneByLicenseNumber(string $licenseNumber): ?Company
    {
        return $this->findOneBy(['licenseNumber' => strtoupper(trim($licenseNumber))]);
    }
}
