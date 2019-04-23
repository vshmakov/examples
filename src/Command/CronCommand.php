<?php

namespace App\Command;

use  App\DateTime\DateTime as DT;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Repository\VisitRepository;
use App\Service\UserLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronCommand extends Command
{
    protected static $defaultName = 'Cron';
    private $sessionRepository;
    private $userRepository;
    private $visitRepository;
    private $userLoader;
    private $entityManager;

    public function __construct(SessionRepository $sessionRepository, UserLoader $userLoader, EntityManagerInterface $entityManager, UserRepository $userRepository, VisitRepository $visitRepository)
    {
        parent::__construct();
        $this->sessionRepository = $sessionRepository;
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

        $dt = DT::createBySubDays(7);
        $removedSessionsCount = $this->sessionRepository->clearSessions($dt);
        $dt = DT::createBySubDays(2);
        $removedVisitsCount = $this->visitRepository->cleareVisits($dt);
        $dt = DT::createBySubDays(10);
        $removedUsersCount = $this->userRepository->clearNotEnabledUsers($dt);

        $io->success(sprintf(
            "Cron command executed\n
            Removed %s users, %s sessions and %s visits",
            $removedUsersCount,
            $removedSessionsCount,
            $removedVisitsCount
        ));
    }
}
