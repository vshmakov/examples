<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Tests\Functional\BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;

final class UserSignsInBySocialNetworkTest extends BaseWebTestCase
{
    use SecurityAssertsTrait;

    /** @var Client */
    private static $userClient;

    public static function setUpBeforeClass(): void
    {
        self::$userClient = self::createClient();
    }

    /**
     * @test
     */
    public function userRegisters(): void
    {
        self::$userClient->request(Request::METHOD_POST, '/security/ulogin/register/', ['token' => '123']);
        $this->assertTrue(self::$userClient->getResponse()->isRedirect('/security/login/'));

        self::$userClient->followRedirect();
        $this->assertSignedIn(self::$userClient);
    }

    /**
     * @test
     * @depends  userRegisters
     */
    public function userSignsIn(): void
    {
        $this->userRegisters();
    }
}
