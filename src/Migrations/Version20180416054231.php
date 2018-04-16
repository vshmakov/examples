<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180416054231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt DROP FOREIGN KEY FK_18EC0266A76ED395');
        $this->addSql('DROP INDEX IDX_18EC0266A76ED395 ON attempt');
        $this->addSql('ALTER TABLE attempt CHANGE user_id session_id INT NOT NULL');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_18EC0266613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('CREATE INDEX IDX_18EC0266613FECDF ON attempt (session_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt DROP FOREIGN KEY FK_18EC0266613FECDF');
        $this->addSql('DROP INDEX IDX_18EC0266613FECDF ON attempt');
        $this->addSql('ALTER TABLE attempt CHANGE session_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_18EC0266A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_18EC0266A76ED395 ON attempt (user_id)');
    }
}
