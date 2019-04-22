<?php

declare(strict_types=1);

namespace App\Security\Ulogin;

use App\Entity\User\SocialAccount;
use App\Request\Ulogin\UloginRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class UloginAccountProvider implements SocialAccountProviderInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(RequestStack $requestStack, FormFactoryInterface $formFactory, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    public function getSocialAccount(string $token): ?SocialAccount
    {
        $request = $this->requestStack->getCurrentRequest();
        Assert::notNull($request);

        $json = file_get_contents(sprintf(
            'http://ulogin.ru/token.php?token=%s&host=%s',
            $token,
            $request->server->get('HTTP_HOST')
        ));

        $credentials = json_decode($json, true);
        Assert::isArray($credentials);
        $socialAccount = new SocialAccount();
        $form = $this->formFactory->create(UloginRequestType::class, $socialAccount);
        $form->submit($credentials);

        if (!$form->isSubmitted() or !$form->isValid()) {
            return null;
        }

        $existsSocialAccount = $this->entityManager
            ->getRepository(SocialAccount::class)
            ->findOneBy(['network' => $socialAccount->getNetwork(), 'networkId' => $socialAccount->getNetworkId()]);

        if (null !== $existsSocialAccount) {
            $socialAccount = $existsSocialAccount;
        }

        $this->entityManager->persist($socialAccount);

        return $socialAccount;
    }
}
