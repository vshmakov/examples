<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class BaseController extends Controller
{
    public function getNormalizer(): NormalizerInterface
    {
        return $this->container->get('serializer');
    }
}
