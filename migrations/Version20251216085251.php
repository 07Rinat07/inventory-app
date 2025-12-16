<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216085251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory_fields DROP description');
        $this->addSql('ALTER TABLE inventory_fields DROP show_in_table');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory_fields ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE inventory_fields ADD show_in_table BOOLEAN NOT NULL');
    }
}
