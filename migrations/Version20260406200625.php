<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406200625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY `FK_E6C774959033212A`');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT FK_E6C774959033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rent_receipt DROP FOREIGN KEY `FK_B2B5CD359033212A`');
        $this->addSql('ALTER TABLE rent_receipt ADD CONSTRAINT FK_B2B5CD359033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lease DROP FOREIGN KEY FK_E6C774959033212A');
        $this->addSql('ALTER TABLE lease ADD CONSTRAINT `FK_E6C774959033212A` FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE rent_receipt DROP FOREIGN KEY FK_B2B5CD359033212A');
        $this->addSql('ALTER TABLE rent_receipt ADD CONSTRAINT `FK_B2B5CD359033212A` FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
