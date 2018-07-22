<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180722084509 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) DEFAULT NULL, CHANGE username_canonical username_canonical VARCHAR(180) DEFAULT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL, CHANGE email_canonical email_canonical VARCHAR(180) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE username_canonical username_canonical VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE email email VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE email_canonical email_canonical VARCHAR(180) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
