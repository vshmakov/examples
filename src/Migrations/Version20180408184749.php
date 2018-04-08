<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180408184749 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE attempt (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, add_time DATETIME NOT NULL, settings LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', INDEX IDX_18EC0266A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE example (id INT AUTO_INCREMENT NOT NULL, attempt_id INT NOT NULL, first DOUBLE PRECISION NOT NULL, sign INT NOT NULL, second DOUBLE PRECISION NOT NULL, answer DOUBLE PRECISION DEFAULT NULL, is_right TINYINT(1) DEFAULT NULL, add_time DATETIME NOT NULL, answer_time DATETIME NOT NULL, INDEX IDX_6EEC9B9FB191BE6B (attempt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, add_time DATETIME NOT NULL, sid VARCHAR(32) NOT NULL, INDEX IDX_D044D5D4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_18EC0266A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9FB191BE6B FOREIGN KEY (attempt_id) REFERENCES attempt (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9FB191BE6B');
        $this->addSql('ALTER TABLE attempt DROP FOREIGN KEY FK_18EC0266A76ED395');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('DROP TABLE attempt');
        $this->addSql('DROP TABLE example');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE user');
    }
}
