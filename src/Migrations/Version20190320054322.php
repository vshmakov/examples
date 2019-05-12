<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320054322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE attempt_settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE attempt_settings (id INT AUTO_INCREMENT NOT NULL, minimum_first_addend DOUBLE PRECISION NOT NULL, maximum_first_addend DOUBLE PRECISION NOT NULL, minimum_second_addend DOUBLE PRECISION NOT NULL, maximum_second_addend DOUBLE PRECISION NOT NULL, minimum_sum DOUBLE PRECISION NOT NULL, maximum_sum DOUBLE PRECISION NOT NULL, minimum_minuend DOUBLE PRECISION NOT NULL, maximum_minuend DOUBLE PRECISION NOT NULL, minimum_subtrahend DOUBLE PRECISION NOT NULL, maximum_subtrahend DOUBLE PRECISION NOT NULL, minimum_difference DOUBLE PRECISION NOT NULL, maximum_difference DOUBLE PRECISION NOT NULL, minimum_multiplicands DOUBLE PRECISION NOT NULL, maximum_multiplicands DOUBLE PRECISION NOT NULL, minimum_multiplier DOUBLE PRECISION NOT NULL, maximum_multiplier DOUBLE PRECISION NOT NULL, minimum_product DOUBLE PRECISION NOT NULL, maximum_product DOUBLE PRECISION NOT NULL, minimum_dividend DOUBLE PRECISION NOT NULL, maximum_dividend DOUBLE PRECISION NOT NULL, minimum_divisor DOUBLE PRECISION NOT NULL, maximum_divisor DOUBLE PRECISION NOT NULL, minimum_quotient DOUBLE PRECISION NOT NULL, maximum_quotient DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
    }
}
