<?php

namespace App\Deploy;

use Deployer\Deployer;
use Deployer\Task\Context;
use Symfony\Component\Process\Process;

/**
 * @author  staabm
 *
 * @see  https://github.com/deployphp/deployer/issues/1518
 */
final class AsyncProcess
{
    /** @var Process */
    private $process;

    public function __construct(string $command)
    {
        $process = new Process($command);
        $process->setTimeout(null);

        $host = Context::get()->getHost();
        $hostname = $host->getHostname();
        $pop = Deployer::get()->pop;

        $pop->command($hostname, '<comment>async start:</comment> '.$command);
        $popCallback = $pop->callback($hostname);
        $process->start(function ($type, $buffer) use ($pop, $popCallback, $hostname, $command) {
            static $once = true;

            if ($once) {
                $pop->command($hostname, '<comment>async result:</comment> '.$command);
                $once = false;
            }

            return $popCallback($type, $buffer);
        });

        $this->process = $process;
    }

    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    public function wait(): string
    {
        $process = $this->process;
        $process->wait();

        if ($process->isSuccessful()) {
            return $process->getOutput();
        }
        throw new \Exception($process->getErrorOutput());
    }
}
