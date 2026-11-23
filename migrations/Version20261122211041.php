<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261122211041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE companies (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, normalized_name VARCHAR(180) NOT NULL, trade VARCHAR(80) NOT NULL, license_number VARCHAR(80) DEFAULT NULL, tax_id VARCHAR(50) DEFAULT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8244AA3AEC7E7152 (license_number), INDEX idx_companies_normalized_name (normalized_name), INDEX idx_companies_trade (trade), INDEX idx_companies_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contractor_assignments (id INT AUTO_INCREMENT NOT NULL, role_on_project VARCHAR(100) NOT NULL, status VARCHAR(30) NOT NULL, assigned_at DATETIME NOT NULL, project_id INT NOT NULL, company_id INT NOT NULL, INDEX idx_contractor_assignments_status (status), UNIQUE INDEX uniq_project_company_assignment (project_id, company_id), INDEX IDX_EAB36E6D166D1F9C (project_id), INDEX IDX_EAB36E6D979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_sites (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, address_line1 VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, state VARCHAR(50) NOT NULL, postal_code VARCHAR(20) NOT NULL, jurisdiction VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, project_id INT NOT NULL, INDEX idx_job_sites_jurisdiction (jurisdiction), INDEX idx_job_sites_city (city), INDEX IDX_BD5C1713166D1F9C (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE organizations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, code VARCHAR(50) NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_427C1C7F77153098 (code), INDEX idx_organizations_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE permits (id INT AUTO_INCREMENT NOT NULL, permit_number VARCHAR(80) NOT NULL, permit_type VARCHAR(50) NOT NULL, issuing_authority VARCHAR(150) NOT NULL, status VARCHAR(30) NOT NULL, issued_at DATE DEFAULT NULL, expires_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, project_id INT NOT NULL, UNIQUE INDEX UNIQ_A63A6116AD33CAD0 (permit_number), INDEX idx_permits_status (status), INDEX idx_permits_type (permit_type), INDEX idx_permits_expires_at (expires_at), INDEX idx_permits_project_status (project_id, status), INDEX IDX_A63A6116166D1F9C (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE projects (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, code VARCHAR(50) NOT NULL, status VARCHAR(30) NOT NULL, starts_at DATE DEFAULT NULL, ends_at DATE DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, organization_id INT NOT NULL, UNIQUE INDEX UNIQ_5C93B3A477153098 (code), INDEX idx_projects_status (status), INDEX idx_projects_org_status (organization_id, status), INDEX IDX_5C93B3A432C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, full_name VARCHAR(150) NOT NULL, role VARCHAR(40) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, organization_id INT NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX idx_users_role (role), INDEX idx_users_org_role (organization_id, role), INDEX IDX_1483A5E932C8A3DE (organization_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contractor_assignments ADD CONSTRAINT FK_EAB36E6D166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contractor_assignments ADD CONSTRAINT FK_EAB36E6D979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_sites ADD CONSTRAINT FK_BD5C1713166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permits ADD CONSTRAINT FK_A63A6116166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A432C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E932C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contractor_assignments DROP FOREIGN KEY FK_EAB36E6D166D1F9C');
        $this->addSql('ALTER TABLE contractor_assignments DROP FOREIGN KEY FK_EAB36E6D979B1AD6');
        $this->addSql('ALTER TABLE job_sites DROP FOREIGN KEY FK_BD5C1713166D1F9C');
        $this->addSql('ALTER TABLE permits DROP FOREIGN KEY FK_A63A6116166D1F9C');
        $this->addSql('ALTER TABLE projects DROP FOREIGN KEY FK_5C93B3A432C8A3DE');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E932C8A3DE');
        $this->addSql('DROP TABLE companies');
        $this->addSql('DROP TABLE contractor_assignments');
        $this->addSql('DROP TABLE job_sites');
        $this->addSql('DROP TABLE organizations');
        $this->addSql('DROP TABLE permits');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE users');
    }
}
