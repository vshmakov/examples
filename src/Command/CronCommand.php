<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;
use App\Service\JsonLogger;
use App\Service\UserLoader;
use Doctrine\ORM\EntityManagerInterface;

class CronCommand extends Command
{
    protected static $defaultName = 'Cron';
    private $sessionRepository;
    private $userRepository;
    private $visitRepository;
    private $logger;
    private $userLoader;
    private $entityManager;

    public function __construct(SessionRepository $sessionRepository, JsonLogger $logger, UserLoader $userLoader, EntityManagerInterface $entityManager, UserRepository $userRepository, VisitRepository $visitRepository)
    {
        parent::__construct();
        $this->sessionRepository = $sessionRepository;
        $this->logger = $logger;
        $this->userLoader = $userLoader;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->visitRepository = $visitRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->userLoader->getGuest()->setIps([]);

        $dt = \DT::crateBySubDays(7)();
        $this->sessionRepository->clearSessions($dt);
        $this->visitRepository->cleareVisits($dt);
        $dt = \DT::createBySubDays(10);
        $this->userRepository->clearNotEnabledUsers($dt);

        $io->success('Cron command executed');
    }
}
