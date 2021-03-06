<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180608185533 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE attempt (id BIGINT AUTO_INCREMENT NOT NULL, session_id BIGINT NOT NULL, add_time DATETIME NOT NULL, settings LONGTEXT NOT NULL COMMENT \'(DC2Type:object)\', INDEX IDX_18EC0266613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE code (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, string VARCHAR(255) NOT NULL, add_time DATETIME NOT NULL, activate_time DATETIME DEFAULT NULL, activated TINYINT(1) NOT NULL, money SMALLINT NOT NULL, INDEX IDX_77153098A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE example (id BIGINT AUTO_INCREMENT NOT NULL, attempt_id BIGINT NOT NULL, first DOUBLE PRECISION NOT NULL, sign SMALLINT NOT NULL, second DOUBLE PRECISION NOT NULL, answer DOUBLE PRECISION DEFAULT NULL, is_right TINYINT(1) DEFAULT NULL, add_time DATETIME NOT NULL, answer_time DATETIME DEFAULT NULL, INDEX IDX_6EEC9B9FB191BE6B (attempt_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, add_time DATETIME NOT NULL, is_public TINYINT(1) NOT NULL, duration SMALLINT NOT NULL, examples_count SMALLINT NOT NULL, add_fmin INT NOT NULL, add_fmax INT NOT NULL, add_smin INT NOT NULL, add_smax INT NOT NULL, add_min INT NOT NULL, add_max INT NOT NULL, sub_fmin INT NOT NULL, sub_fmax INT NOT NULL, sub_smin INT NOT NULL, sub_smax INT NOT NULL, sub_min INT NOT NULL, sub_max INT NOT NULL, mult_fmin INT NOT NULL, mult_fmax INT NOT NULL, mult_smin INT NOT NULL, mult_smax INT NOT NULL, mult_min INT NOT NULL, mult_max INT NOT NULL, div_fmin INT NOT NULL, div_fmax INT NOT NULL, div_smin INT NOT NULL, div_smax INT NOT NULL, div_min INT NOT NULL, div_max INT NOT NULL, add_perc SMALLINT NOT NULL, sub_perc SMALLINT NOT NULL, mult_perc SMALLINT NOT NULL, div_perc SMALLINT NOT NULL, description VARCHAR(255) NOT NULL, is_demanding TINYINT(1) NOT NULL, INDEX IDX_8157AA0FF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id BIGINT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, add_time DATETIME NOT NULL, last_time DATETIME NOT NULL, sid VARCHAR(32) NOT NULL, INDEX IDX_D044D5D4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', money SMALLINT NOT NULL, all_money INT NOT NULL, add_time DATETIME NOT NULL, limit_time DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), INDEX IDX_8D93D649CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_18EC0266613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE example ADD CONSTRAINT FK_6EEC9B9FB191BE6B FOREIGN KEY (attempt_id) REFERENCES attempt (id)');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE example DROP FOREIGN KEY FK_6EEC9B9FB191BE6B');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CCFA12B8');
        $this->addSql('ALTER TABLE attempt DROP FOREIGN KEY FK_18EC0266613FECDF');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_77153098A76ED395');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FF675F31B');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('DROP TABLE attempt');
        $this->addSql('DROP TABLE code');
        $this->addSql('DROP TABLE example');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE user');
    }
}
