<?php

namespace DoctrineMigrations;

use App\Attempt\AttemptResultProviderInterface;
use App\Entity\Attempt;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version20190505165121 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'Generate attempt results.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        return;

        /** @var AttemptResultProviderInterface $attemptResultProvider */
        $attemptResultProvider = $this->container->get(AttemptResultProviderInterface::class);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        foreach ($entityManager->getRepository(Attempt::class)->findAll() as $attempt) {
            $attemptResultProvider->updateAttemptResult($attempt);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
