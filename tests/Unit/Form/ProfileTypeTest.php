<?php

namespace App\Tests\Unit\Form;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Object\ObjectAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ProfileTypeTest extends TestCase
{
    /**
     * @test
     */
    public function testSomething(): void
    {
        $normalizer = new Serializer([new ObjectNormalizer()], []);
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'addFMin' => 5,
            'addFMax' => 5,
            'addSMin' => 5,
            'addSMax' => 5,
            'addMin' => 8,
            'addMax' => 10,
        ]);
        $profileType = new ProfileType(
            $this->createMock(AuthorizationCheckerInterface::class),
            $normalizer
        );

        $profileType->normalizeSolveSettings($this->createFormEvent($profile));
        $this->assertSame(10, $profile->getAddMin());
    }

    private function createFormEvent(Profile $profile): FormEvent
    {
        return new  FormEvent($this->createMock(FormInterface::class), $profile);
    }
}
