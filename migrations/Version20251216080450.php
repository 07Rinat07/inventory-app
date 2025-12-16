<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216080450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add message, author and createdAt to discussion_post';
    }

    public function up(Schema $schema): void
    {
        // message
        $this->addSql('ALTER TABLE discussion_post ADD message TEXT NOT NULL');

        // created_at
        $this->addSql('ALTER TABLE discussion_post ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');

        // author
        $this->addSql('ALTER TABLE discussion_post ADD author_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_DISCUSSION_POST_AUTHOR ON discussion_post (author_id)');
        $this->addSql(
            'ALTER TABLE discussion_post 
             ADD CONSTRAINT FK_DISCUSSION_POST_AUTHOR 
             FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE discussion_post DROP CONSTRAINT FK_DISCUSSION_POST_AUTHOR');
        $this->addSql('DROP INDEX IDX_DISCUSSION_POST_AUTHOR');
        $this->addSql('ALTER TABLE discussion_post DROP author_id');
        $this->addSql('ALTER TABLE discussion_post DROP message');
        $this->addSql('ALTER TABLE discussion_post DROP created_at');
    }
}
