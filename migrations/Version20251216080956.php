<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216080956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discussion_post DROP CONSTRAINT fk_7fe4c0bb9eea759');
        $this->addSql('ALTER TABLE discussion_post ADD CONSTRAINT FK_7FE4C0BB9EEA759 FOREIGN KEY (inventory_id) REFERENCES inventories (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER INDEX idx_discussion_post_author RENAME TO IDX_7FE4C0BBF675F31B');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discussion_post DROP CONSTRAINT FK_7FE4C0BB9EEA759');
        $this->addSql('ALTER TABLE discussion_post ADD CONSTRAINT fk_7fe4c0bb9eea759 FOREIGN KEY (inventory_id) REFERENCES inventories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_7fe4c0bbf675f31b RENAME TO idx_discussion_post_author');
    }
}
