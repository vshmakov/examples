<?php

namespace App\Deploy;

final class AsyncProcessCollection implements WaitableInterface
{
    /** @var AsyncProcess[] */
    private $collection = [];

    public function wait(): string
    {
        $outputCollection = [];

        foreach ($this->collection as $asyncProcess) {
            $outputCollection[] = $asyncProcess->wait();
        }

        return implode("\n\n", $outputCollection);
    }

    public function add(AsyncProcess $asyncProcess): void
    {
        $this->collection[] = $asyncProcess;
    }
}
