<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216055403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discussion_post_likes DROP CONSTRAINT fk_5c5a508ea76ed395');
        $this->addSql('DROP INDEX idx_5c5a508ea76ed395');
        $this->addSql('ALTER TABLE discussion_post_likes ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE discussion_post_likes RENAME COLUMN user_id TO liked_by_id');
        $this->addSql('ALTER TABLE discussion_post_likes ADD CONSTRAINT FK_5C5A508EB4622EC2 FOREIGN KEY (liked_by_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_5C5A508EB4622EC2 ON discussion_post_likes (liked_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discussion_post_likes DROP CONSTRAINT FK_5C5A508EB4622EC2');
        $this->addSql('DROP INDEX IDX_5C5A508EB4622EC2');
        $this->addSql('ALTER TABLE discussion_post_likes DROP created_at');
        $this->addSql('ALTER TABLE discussion_post_likes RENAME COLUMN liked_by_id TO user_id');
        $this->addSql('ALTER TABLE discussion_post_likes ADD CONSTRAINT fk_5c5a508ea76ed395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5c5a508ea76ed395 ON discussion_post_likes (user_id)');
    }
}
