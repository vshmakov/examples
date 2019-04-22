<?php

declare(strict_types=1);

namespace App\User\SocialAccount;

use App\Entity\User\SocialAccount;
use App\Request\Ulogin\UloginRequestType;
use App\User\SocialAccount\Credentials\SocialAccountCredentialsProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

final class UloginAccountProvider implements SocialAccountProviderInterface
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SocialAccountCredentialsProviderInterface */
    private $socialAccountCredentialsProvider;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, SocialAccountCredentialsProviderInterface $socialAccountCredentialsProvider)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->socialAccountCredentialsProvider = $socialAccountCredentialsProvider;
    }

    public function getSocialAccount(string $token): ?SocialAccount
    {
        $socialAccount = new SocialAccount();
        $form = $this->formFactory->create(UloginRequestType::class, $socialAccount);
        $form->submit($this->socialAccountCredentialsProvider->getSocialAccountCredentials($token));

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
