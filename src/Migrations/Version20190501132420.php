<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190501132420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt DROP settings');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_18EC026659949888 FOREIGN KEY (settings_id) REFERENCES settings (id)');
        $this->addSql('CREATE INDEX IDX_18EC026659949888 ON attempt (settings_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt DROP FOREIGN KEY FK_18EC026659949888');
        $this->addSql('DROP INDEX IDX_18EC026659949888 ON attempt');
        $this->addSql('ALTER TABLE attempt ADD settings LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:object)\'');
    }
}
