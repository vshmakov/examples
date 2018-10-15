<?php

namespace App\Utils;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Container\ContainerInterface;

class PerformanceMeter
{
    private $data;
    private $isDevelopmentEnvironment;

    public function __construct(ContainerInterface $container)
    {
        $this->data = new ArrayCollection();
        $environment = $container->getParameter('app_env');
        $this->isDevelopmentEnvironment = 'dev' == $environment;
    }

    public function start(string $key): self
    {
        if (!$this->isDevelopmentEnvironment) {
            return $this;
        }

        if (!$this->data->offsetExists($key)) {
            $this->data->set($key, new ArrayCollection());
        }

        $moments = new ArrayCollection(['start' => $this->getTime()]);
        $this->data->get($key)
            ->add($moments);

        return $this;
    }

    public function finish(string $key): self
    {
        if (!$this->isDevelopmentEnvironment) {
            return $this;
        }

        if (!($momentsList = $this->data->get($key))
            or !($moments = $momentsList->last())
            or !$moments->offsetExists('start')
            or $moments->offsetExists('finish')) {
            throw new \Exception(sprintf(
                'Attempted finished %1$s key, but it has not started',
                $key
            ));
        }

        $moments->set('finish', $this->getTime());

        return $this;
    }

    public function getData(): array
    {
        $result = array_reduce(
            $this->data->toArray(),
            function ($result, $momentsList) {
                $key = $this->data->indexOf($momentsList);

                $result[$key] = [
                    'calledCount' => $momentsList->count(),
                    'totalExecutionTime' => (int) array_reduce(
                        $momentsList->toArray(),
                        function ($totalExecutionTime, $moments) use ($key) {
                            if (!$moments->offsetExists('finish')) {
                                throw new \Exception(sprintf(
                                    'The %s key das not finished',
                                    $key
                                ));
                            }

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
