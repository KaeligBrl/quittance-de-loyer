<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309210020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY `FK_6D28840DD3CA542C`');
        $this->addSql('DROP INDEX IDX_6D28840DD3CA542C ON payment');
        $this->addSql('ALTER TABLE payment CHANGE lease_id rent_receipt_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D81BE0CF9 FOREIGN KEY (rent_receipt_id) REFERENCES rent_receipt (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D81BE0CF9 ON payment (rent_receipt_id)');
        $this->addSql('ALTER TABLE rent_receipt DROP FOREIGN KEY `FK_B2B5CD35D3CA542C`');
        $this->addSql('DROP INDEX IDX_B2B5CD35D3CA542C ON rent_receipt');
        $this->addSql('ALTER TABLE rent_receipt CHANGE lease_id tenant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rent_receipt ADD CONSTRAINT FK_B2B5CD359033212A FOREIGN KEY (tenant_id) REFERENCES tenant (id)');
        $this->addSql('CREATE INDEX IDX_B2B5CD359033212A ON rent_receipt (tenant_id)');
        $this->addSql('ALTER TABLE tenant ADD property_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tenant ADD CONSTRAINT FK_4E59C462549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
        $this->addSql('CREATE INDEX IDX_4E59C462549213EC ON tenant (property_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D81BE0CF9');
        $this->addSql('DROP INDEX IDX_6D28840D81BE0CF9 ON payment');
        $this->addSql('ALTER TABLE payment CHANGE rent_receipt_id lease_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT `FK_6D28840DD3CA542C` FOREIGN KEY (lease_id) REFERENCES lease (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_6D28840DD3CA542C ON payment (lease_id)');
        $this->addSql('ALTER TABLE rent_receipt DROP FOREIGN KEY FK_B2B5CD359033212A');
        $this->addSql('DROP INDEX IDX_B2B5CD359033212A ON rent_receipt');
        $this->addSql('ALTER TABLE rent_receipt CHANGE tenant_id lease_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rent_receipt ADD CONSTRAINT `FK_B2B5CD35D3CA542C` FOREIGN KEY (lease_id) REFERENCES lease (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_B2B5CD35D3CA542C ON rent_receipt (lease_id)');
        $this->addSql('ALTER TABLE tenant DROP FOREIGN KEY FK_4E59C462549213EC');
        $this->addSql('DROP INDEX IDX_4E59C462549213EC ON tenant');
        $this->addSql('ALTER TABLE tenant DROP property_id');
    }
}
