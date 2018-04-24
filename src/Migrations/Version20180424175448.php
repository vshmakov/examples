<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180424175448 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE code (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, string VARCHAR(255) NOT NULL, add_time DATETIME NOT NULL, activate_time DATETIME DEFAULT NULL, activated TINYINT(1) NOT NULL, INDEX IDX_77153098A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE profile CHANGE is_demanding is_demanding TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE money money SMALLINT NOT NULL, CHANGE limit_time limit_time DATETIME NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE code');
        $this->addSql('ALTER TABLE profile CHANGE is_demanding is_demanding TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE money money SMALLINT DEFAULT 0 NOT NULL, CHANGE limit_time limit_time DATETIME DEFAULT \'2018-01-01 00:00:00\' NOT NULL');
    }
}
