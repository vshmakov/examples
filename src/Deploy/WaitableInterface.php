<?php

namespace App\Deploy;

interface WaitableInterface
{
    public function wait(): string;
}
