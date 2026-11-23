<?php

declare(strict_types=1);

namespace App\Shared\DataFixtures;

use App\Contractors\Domain\Entity\Company;
use App\Contractors\Domain\Entity\ContractorAssignment;
use App\Identity\Domain\Entity\User;
use App\Organizations\Domain\Entity\Organization;
use App\Permits\Domain\Entity\Permit;
use App\Projects\Domain\Entity\JobSite;
use App\Projects\Domain\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    private const BATCH_SIZE = 10;

    public function load(ObjectManager $manager): void
    {
        $apex = new Organization('Apex Builders Group', 'APEX', 'active');
        $summit = new Organization('Summit Civil Construction', 'SUMMIT', 'active');
        $manager->persist($apex);
        $manager->persist($summit);

        $manager->persist(new User($apex, 'admin@apexbuilders.example', 'Sarah Jenkins', 'admin'));
        $manager->persist(new User($apex, 'pm@apexbuilders.example', 'Marcus Vance', 'project_manager'));
        $manager->persist(new User($apex, 'safety@apexbuilders.example', 'Elena Rostova', 'safety_officer'));
        $manager->persist(new User($summit, 'auditor@summitcivil.example', 'David Kim', 'auditor'));

        $downtownTower = new Project(
            $apex,
            'Downtown Tower',
            'DT-2026',
            'active',
            new \DateTimeImmutable('2026-01-15'),
            new \DateTimeImmutable('2028-06-30'),
        );
        $harborBridge = new Project(
            $apex,
            'Harbor Bridge Expansion',
            'HB-2026',
            'active',
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2027-12-31'),
        );
        $metroClinic = new Project(
            $summit,
            'Metro Outpatient Clinic',
            'MC-2026',
            'planning',
            new \DateTimeImmutable('2026-09-01'),
            new \DateTimeImmutable('2028-02-28'),
        );
        $manager->persist($downtownTower);
        $manager->persist($harborBridge);
        $manager->persist($metroClinic);

        $manager->persist(new JobSite($downtownTower, 'North Core Parcel', '450 Main St', 'Austin', 'TX', '78701', 'City of Austin'));
        $manager->persist(new JobSite($downtownTower, 'South Staging Yard', '480 Main St', 'Austin', 'TX', '78701', 'City of Austin'));
        $manager->persist(new JobSite($harborBridge, 'East Pier Abutment', '1200 Port Rd', 'Houston', 'TX', '77012', 'Harris County'));
        $manager->persist(new JobSite($metroClinic, 'Medical Campus Lot B', '88 Health Way', 'Denver', 'CO', '80202', 'City of Denver'));

        $abcElectrical = new Company('ABC Electrical LLC', 'electrical', 'TX-EL-9001', '74-1000001', 'non_compliant');
        $steelWorks = new Company('Ironclad Steel Corp.', 'structural_steel', 'TX-ST-4410', '74-1000002', 'compliant');
        $clearPlumbing = new Company('ClearFlow Plumbing Inc', 'plumbing', 'TX-PL-7782', '74-1000003', 'compliant');
        $peakHvac = new Company('Peak Mechanical Co.', 'hvac', 'CO-MC-3319', '84-1000004', 'conditional');
        $manager->persist($abcElectrical);
        $manager->persist($steelWorks);
        $manager->persist($clearPlumbing);
        $manager->persist($peakHvac);

        $manager->persist(new ContractorAssignment($downtownTower, $abcElectrical, 'High-Voltage Electrical Subcontractor', 'active'));
        $manager->persist(new ContractorAssignment($downtownTower, $steelWorks, 'Structural Steel Erection', 'active'));
        $manager->persist(new ContractorAssignment($downtownTower, $clearPlumbing, 'Riser & Core Plumbing', 'active'));
        $manager->persist(new ContractorAssignment($harborBridge, $steelWorks, 'Bridge Deck Steelwork', 'active'));
        $manager->persist(new ContractorAssignment($metroClinic, $peakHvac, 'HVAC & Cleanroom Mechanical', 'assigned'));

        $manager->persist(new Permit(
            $downtownTower,
            'AUS-BLD-2026-001',
            'building',
            'City of Austin Development Services',
            'approved',
            new \DateTimeImmutable('2026-01-10'),
            new \DateTimeImmutable('2027-07-10'),
        ));
        $manager->persist(new Permit(
            $downtownTower,
            'AUS-ELC-2026-014',
            'electrical',
            'City of Austin Development Services',
            'under_review',
            null,
            new \DateTimeImmutable('2027-05-30'),
        ));
        $manager->persist(new Permit(
            $harborBridge,
            'HOU-ENV-2026-088',
            'environmental',
            'Texas Commission on Environmental Quality',
            'approved',
            new \DateTimeImmutable('2026-02-20'),
            new \DateTimeImmutable('2027-02-20'),
        ));
        $manager->persist(new Permit(
            $metroClinic,
            'DEN-BLD-2026-205',
            'building',
            'Denver Community Planning & Development',
            'submitted',
            null,
            null,
        ));

        $manager->flush();
        $manager->clear();

        // Practice Doctrine batching: insert additional trade contractors in chunks
        // using flush() + clear() to keep UnitOfWork memory usage bounded.
        $trades = ['concrete', 'roofing', 'glazing', 'fire_protection', 'excavation'];
        for ($i = 1; $i <= 25; ++$i) {
            $trade = $trades[($i - 1) % \count($trades)];
            $company = new Company(
                \sprintf('Regional Trade Partner %02d LLC', $i),
                $trade,
                \sprintf('REG-%04d', $i),
                null,
                $i % 3 === 0 ? 'compliant' : 'pending',
            );
            $manager->persist($company);

            if ($i % self::BATCH_SIZE === 0) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
