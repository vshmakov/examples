<?php

namespace App\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Container\ContainerInterface;

class PerformanceMeter
{
    private $data;
    private $states;
    private $isDevelopmentEnvironment;

    public function __construct(ContainerInterface $container)
    {
        $this->data = new ArrayCollection();
        $this->states = new ArrayCollection();
        $environment = $container->getParameter('app_env');
        $this->isDevelopmentEnvironment = 'dev' === $environment;
    }

    public function start(string $key): self
    {
        if (!$this->isDevelopmentEnvironment) {
            return $this;
        }

        if (!$this->data->offsetExists($key)) {
            $this->data->set($key, new ArrayCollection());
            $this->states->set($key, 'finished');
        }

        if ('finished' !== $this->states->get($key)) {
            throw new \LogicException(sprintf(
                'The %s key das not finished',
                $key
            ));
        }

        $moments = new ArrayCollection(['start' => $this->getTime()]);
        $this->data->get($key)
            ->add($moments);
        $this->states->set($key, 'started');

        return $this;
    }

    public function finish(string $key): self
    {
        if (!$this->isDevelopmentEnvironment) {
            return $this;
        }

        if ('started' !== $this->states->get($key)) {
            throw new \LogicException(sprintf(
                'Attempted finished %1$s key, but it has not started',
                $key
            ));
        }

        $this->data->get($key)->last()->set('finish', $this->getTime());
        $this->states->set($key, 'finished');

        return $this;
    }

    public function getData(): array
    {
        $result = array_reduce(
            $this->data->toArray(),
            function ($result, $momentsList) {
                $key = $this->data->indexOf($momentsList);
                $momentsListState = $this->states->get($key);

                if ($momentsListState && 'finished' !== $momentsListState) {
                    throw new \LogicException(sprintf(
                        'The %s key das not finished',
                        $key
                    ));
                }

                $result[$key] = [
                    'key' => $key,
                    'calledCount' => $momentsList->count(),
                    'totalExecutionTime' => (int) array_reduce(
                        $momentsList->toArray(),
                        function ($totalExecutionTime, $moments) use ($key) {
                            return $totalExecutionTime + ($moments->get('finish') - $moments->get('start')) * 1000;
                        },
                        0
                    ),
                ];

                return $result;
            },
            []
        );

        return $result;
    }

    private function getTime()
    {
        return (float) time() + (float) microtime();
    }
}
