<?php

namespace App\User\Visit\Ip;

use App\Entity\Ip;

interface IpProviderInterface
{
    public function getCurrentRequestIp(): ?Ip;
}
