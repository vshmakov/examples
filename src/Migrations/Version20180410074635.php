<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180410074635 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile ADD author_id INT NOT NULL, ADD add_time DATETIME NOT NULL, ADD is_public TINYINT(1) NOT NULL, ADD duration SMALLINT NOT NULL, ADD examples_count SMALLINT NOT NULL, ADD add_min INT NOT NULL, ADD add_max INT NOT NULL, ADD sub_min INT NOT NULL, ADD sub_max INT NOT NULL, ADD min_sub INT NOT NULL, ADD mult_min INT NOT NULL, ADD mult_max INT NOT NULL, ADD div_min INT NOT NULL, ADD div_max INT NOT NULL, ADD min_div INT NOT NULL, ADD add_perc SMALLINT NOT NULL, ADD sub_perc SMALLINT NOT NULL, ADD mult_perc SMALLINT NOT NULL, ADD div_perc SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8157AA0FF675F31B ON profile (author_id)');
        $this->addSql('ALTER TABLE user ADD username VARCHAR(180) NOT NULL, ADD username_canonical VARCHAR(180) NOT NULL, ADD email VARCHAR(180) NOT NULL, ADD email_canonical VARCHAR(180) NOT NULL, ADD enabled TINYINT(1) NOT NULL, ADD salt VARCHAR(255) DEFAULT NULL, ADD password VARCHAR(255) NOT NULL, ADD last_login DATETIME DEFAULT NULL, ADD confirmation_token VARCHAR(180) DEFAULT NULL, ADD password_requested_at DATETIME DEFAULT NULL, ADD roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64992FC23A8 ON user (username_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A0D96FBF ON user (email_canonical)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C05FB297 ON user (confirmation_token)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FF675F31B');
        $this->addSql('DROP INDEX IDX_8157AA0FF675F31B ON profile');
        $this->addSql('ALTER TABLE profile DROP author_id, DROP add_time, DROP is_public, DROP duration, DROP examples_count, DROP add_min, DROP add_max, DROP sub_min, DROP sub_max, DROP min_sub, DROP mult_min, DROP mult_max, DROP div_min, DROP div_max, DROP min_div, DROP add_perc, DROP sub_perc, DROP mult_perc, DROP div_perc');
        $this->addSql('DROP INDEX UNIQ_8D93D64992FC23A8 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649A0D96FBF ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649C05FB297 ON user');
        $this->addSql('ALTER TABLE user DROP username, DROP username_canonical, DROP email, DROP email_canonical, DROP enabled, DROP salt, DROP password, DROP last_login, DROP confirmation_token, DROP password_requested_at, DROP roles');
    }
}
