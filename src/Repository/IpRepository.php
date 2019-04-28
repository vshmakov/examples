<?php

namespace App\Repository;

use App\Entity\Ip;
use App\Object\ObjectAccessor;
use App\Request\Ip\IpInfoRequestType;
use App\Service\IpInformer;
use App\User\Visit\Ip\IpProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class IpRepository extends ServiceEntityRepository implements IpProviderInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(RegistryInterface $registry, RequestStack $requestStack, FormFactoryInterface $formFactory)
    {
        parent::__construct($registry, Ip::class);

        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
    }

    public function getCurrentRequestIp(): ?Ip
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        $clientIp = $request->getClientIp();

        if (null === $clientIp or !IpInformer::isIp($clientIp)) {
            return null;
        }

        if ($ip = $this->findOneByIp($clientIp)) {
            return $ip;
        }

        $ip = ObjectAccessor::initialize(Ip::class, [
            'ip' => $clientIp,
        ]);
        $this->fillIpInfo($ip);
        $this->getEntityManager()->persist($ip);
        $this->getEntityManager()->flush($ip);

        return $ip;
    }

    private function fillIpInfo(Ip $ip): void
    {
        $ipInfo = json_decode(
            file_get_contents(sprintf('http://api.db-ip.com/v2/free/%s', urlencode($ip->getIp()))),
            true
        );
        $form = $this->formFactory->create(IpInfoRequestType::class, $ip);
        Assert::isArray($ipInfo);
        $form->submit($ipInfo);
    }
}
