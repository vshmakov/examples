<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Repository\UserRepository as UR;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180904163039 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        dump(class_exists(UR::class));
        $em = $this->container
->get('doctrine.orm.default_entity_manager')
;

        $users = $em->createQuery('select u from App:User u')
->getResult()
;

        foreach ($users as $u) {
            if ($u->isSocial()) {
                $reg = preg_match('#^([a-z]+)\-(\d+)$#', $u->getUsername(), $arr);
                $u->setUsername(
'^'.$u->getUsername()
);

                if ($reg) {
                    $u->setNetwork($arr[1])
->setNetworkId($arr[2]);
                }
            }
        }

        $em->flush();
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
