<?php

namespace App\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Utils\PerformanceMeter;

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
        $this->data = array();
    }

    public function getName()
    {
        return 'app.performance_collector';
    }
}
