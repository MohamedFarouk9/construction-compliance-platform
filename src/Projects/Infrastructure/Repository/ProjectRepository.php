<?php

declare(strict_types=1);

namespace App\Projects\Infrastructure\Repository;

use App\Projects\Domain\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
final class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * Eagerly loads organization and related collections in constant O(1) queries
     * (multi-step hydration avoids both N+1 queries and SQL Cartesian products).
     *
     * @return list<Project>
     */
    public function findAllWithDetails(?int $organizationId = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.organization', 'o')
            ->addSelect('o')
            ->orderBy('p.name', 'ASC');

        if ($organizationId !== null) {
            $qb->andWhere('o.id = :organizationId')
                ->setParameter('organizationId', $organizationId);
        }

        if ($status !== null && $status !== '') {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        /** @var list<Project> $projects */
        $projects = $qb->getQuery()->getResult();

        if ($projects === []) {
            return [];
        }

        $ids = array_values(array_filter(array_map(
            static fn (Project $project): ?int => $project->getId(),
            $projects,
        )));

        $this->hydrateCollections($ids);

        return $projects;
    }

    public function findOneWithDetails(int $id): ?Project
    {
        /** @var ?Project $project */
        $project = $this->createQueryBuilder('p')
            ->innerJoin('p.organization', 'o')
            ->addSelect('o')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if ($project === null) {
            return null;
        }

        $this->hydrateCollections([$id]);

        return $project;
    }

    public function findOneByCode(string $code): ?Project
    {
        return $this->findOneBy(['code' => strtoupper(trim($code))]);
    }

    /** @param list<int> $projectIds */
    private function hydrateCollections(array $projectIds): void
    {
        if ($projectIds === []) {
            return;
        }

        $this->createQueryBuilder('p')
            ->leftJoin('p.jobSites', 'js')
            ->addSelect('js')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $projectIds)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('p')
            ->leftJoin('p.contractorAssignments', 'ca')
            ->addSelect('ca')
            ->leftJoin('ca.company', 'c')
            ->addSelect('c')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $projectIds)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('p')
            ->leftJoin('p.permits', 'pm')
            ->addSelect('pm')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $projectIds)
            ->getQuery()
            ->getResult();
    }
}
