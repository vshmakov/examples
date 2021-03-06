<?php

namespace App\DataCollector;

use App\Utils\PerformanceMeter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class PerformanceCollector extends DataCollector
{
    private $performanceMeter;

    public function __construct(
        PerformanceMeter $performanceMeter
    ) {
        $this->performanceMeter = $performanceMeter;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = $this->performanceMeter->getData();
    }

    public function reset()
    {
        $this->data = [];
    }

    public function getCalledCount(): int
    {
        return array_reduce(
            $this->data,
            function ($count, $element) {
                return $count + $element['calledCount'];
            },
            0
        );
    }

    public function getTotalExecutionTime(): int
    {
        return array_reduce(
            $this->data,
            function ($totalExecutionTime, $element) {
                return $totalExecutionTime + $element['totalExecutionTime'];
            },
            0
        );
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getName()
    {
        return 'app.performance_collector';
    }
}
