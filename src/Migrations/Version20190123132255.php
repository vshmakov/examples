<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190123132255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt_settings ADD minimum_minuend DOUBLE PRECISION NOT NULL, ADD maximum_minuend DOUBLE PRECISION NOT NULL, ADD minimum_subtrahend DOUBLE PRECISION NOT NULL, ADD maximum_subtrahend DOUBLE PRECISION NOT NULL, ADD minimum_difference DOUBLE PRECISION NOT NULL, ADD maximum_difference DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt_settings DROP minimum_minuend, DROP maximum_minuend, DROP minimum_subtrahend, DROP maximum_subtrahend, DROP minimum_difference, DROP maximum_difference');
    }
}
