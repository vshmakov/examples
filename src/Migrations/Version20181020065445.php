<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181020065445 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, add_time DATETIME NOT NULL, duration SMALLINT NOT NULL, examples_count SMALLINT NOT NULL, add_fmin INT NOT NULL, add_fmax INT NOT NULL, add_smin INT NOT NULL, add_smax INT NOT NULL, add_min INT NOT NULL, add_max INT NOT NULL, sub_fmin INT NOT NULL, sub_fmax INT NOT NULL, sub_smin INT NOT NULL, sub_smax INT NOT NULL, sub_min INT NOT NULL, sub_max INT NOT NULL, mult_fmin INT NOT NULL, mult_fmax INT NOT NULL, mult_smin INT NOT NULL, mult_smax INT NOT NULL, mult_min INT NOT NULL, mult_max INT NOT NULL, div_fmin INT NOT NULL, div_fmax INT NOT NULL, div_smin INT NOT NULL, div_smax INT NOT NULL, div_min INT NOT NULL, div_max INT NOT NULL, add_perc SMALLINT NOT NULL, sub_perc SMALLINT NOT NULL, mult_perc SMALLINT NOT NULL, div_perc SMALLINT NOT NULL, description VARCHAR(255) NOT NULL, is_demanding TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE settings');
    }
}
