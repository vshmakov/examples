<?php

namespace App\User\SocialAccount\Credentials;

use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class SocialAccountUloginCredentialsProvider implements SocialAccountCredentialsProviderInterface
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getSocialAccountCredentials(string $token): array
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

        return $credentials;
    }
}
