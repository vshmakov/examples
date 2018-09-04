<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\SessionRepository as SR;
use App\Service\JsonLogger as JL;
use App\Service\UserLoader as UL;

class CronCommand extends Command
{
    protected static $defaultName = 'Cron';
    private $sR;
    private $l;
    private $ul;

    public function __construct(SR $sR, JL $l, UL $ul)
    {
        parent::__construct();
        $this->sR = $sR;
        $this->l = $l;
        $this->ul = $ul;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->ul->getGuest()->setIps([]);

        $i = function ($d = 7) {
            return (new \DateTime())->sub(new \DateInterval("P{$d}D"));
        };
        $dt = $i();
        $sR = $this->sR;
        $sR->clearSessions($dt);
        $em = $sR->em();
        $vs = $em->createQuery('select v from App:Visit v
where v.addTime < :dt')
->setParameter('dt', $dt)
->getResult();

        $users = $em->createQuery('select u from App:User u
where u.enabled = false and u.addTime < :dt')
->setParameter('dt', $i(10))
->getResult();

        foreach (array_merge($vs, $users) as $v) {
            $em->remove($v);
        }

        $em->flush();
        $io->success('Cron command executed');
    }
}
