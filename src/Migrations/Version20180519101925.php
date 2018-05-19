<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180519101925 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile ADD add_fmin INT NOT NULL, ADD add_fmax INT NOT NULL, ADD add_smin INT NOT NULL, ADD add_smax INT NOT NULL, ADD sub_fmin INT NOT NULL, ADD sub_fmax INT NOT NULL, ADD sub_smin INT NOT NULL, ADD sub_smax INT NOT NULL, ADD mult_fmin INT NOT NULL, ADD mult_fmax INT NOT NULL, ADD mult_smin INT NOT NULL, ADD mult_smax INT NOT NULL, ADD div_fmin INT NOT NULL, ADD div_fmax INT NOT NULL, ADD div_smin INT NOT NULL, ADD div_smax INT NOT NULL, DROP min_sub, DROP min_div');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile ADD min_sub INT NOT NULL, ADD min_div INT NOT NULL, DROP add_fmin, DROP add_fmax, DROP add_smin, DROP add_smax, DROP sub_fmin, DROP sub_fmax, DROP sub_smin, DROP sub_smax, DROP mult_fmin, DROP mult_fmax, DROP mult_smin, DROP mult_smax, DROP div_fmin, DROP div_fmax, DROP div_smin, DROP div_smax');
    }
}
