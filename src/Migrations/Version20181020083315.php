<?php declare (strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Attempt;
use App\Entity\Settings;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181020083315 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $dbalConnection = $this->container
            ->get('doctrine.dbal.default_connection');
        $entityManager = $this->container
            ->get('doctrine.orm.default_entity_manager');
        $result = $dbalConnection->fetchAll('select a.id, a.settings from attempt a');
        $attemptsSettings = [];

        foreach ($result as $row) {
            $profileInstance = unserialize($row['settings']);

            if (!$profileInstance->getDescription()) {
                $profileInstance->setDescription('');
            }

            $settings = new Settings();
            Settings::copySettings($profileInstance, $settings);
            $entityManager->persist($settings);
            $entityManager->flush();
            $queries[] = sprintf('update attempt set settings_id = %d where id = %d', $settings->getId(), $row['id']);
        }

        //$this->abortIf(true);
        $this->addSql('ALTER TABLE attempt ADD settings_id INT NOT NULL DEFAULT 1 , DROP settings');
        $this->addSql('ALTER TABLE attempt ADD CONSTRAINT FK_18EC026659949888 FOREIGN KEY (settings_id) REFERENCES settings (id)');

        foreach ($queries as $query) {
            $this->addSql($query);
        }

        $this->addSql('CREATE UNIQUE INDEX UNIQ_18EC026659949888 ON attempt (settings_id)');

       $entityManager->flush();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attempt DROP FOREIGN KEY FK_18EC026659949888');
        $this->addSql('DROP INDEX UNIQ_18EC026659949888 ON attempt');
        $this->addSql('ALTER TABLE attempt ADD settings LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:object)\', DROP settings_id');
    }
}
