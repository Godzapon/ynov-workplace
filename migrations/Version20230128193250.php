<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230128193250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_request ADD COLUMN status INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__group_request AS SELECT id, target_user_id, target_group_id FROM group_request');
        $this->addSql('DROP TABLE group_request');
        $this->addSql('CREATE TABLE group_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, target_user_id INTEGER NOT NULL, target_group_id INTEGER NOT NULL, CONSTRAINT FK_BD97DB936C066AFE FOREIGN KEY (target_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BD97DB9324FF092E FOREIGN KEY (target_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO group_request (id, target_user_id, target_group_id) SELECT id, target_user_id, target_group_id FROM __temp__group_request');
        $this->addSql('DROP TABLE __temp__group_request');
        $this->addSql('CREATE INDEX IDX_BD97DB936C066AFE ON group_request (target_user_id)');
        $this->addSql('CREATE INDEX IDX_BD97DB9324FF092E ON group_request (target_group_id)');
    }
}
