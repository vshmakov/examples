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
    /** @var ProfileType */
    private $profileType;

    public function setUp(): void
    {
        $normalizer = new Serializer([new ObjectNormalizer()], []);
        $this->profileType = new ProfileType(
            $this->createMock(AuthorizationCheckerInterface::class),
            $normalizer
        );
    }

    /**
     * @test
     */
    public function eventListenerNormalisesAdditionSolveSettings(): void
    {
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'addFMin' => 5,
            'addFMax' => 5,
            'addSMin' => 5,
            'addSMax' => 5,
            'addMin' => 8,
            'addMax' => 12,
        ]);
        $this->normalizeSolveSettings($this->profileType, $profile);

        $this->assertSame(10, $profile->getAddMin());
        $this->assertSame(10, $profile->getAddMax());

        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'addFMin' => 4,
            'addFMax' => 6,
            'addSMin' => 4,
            'addSMax' => 6,
            'addMin' => 20,
            'addMax' => 1,
        ]);
        $this->normalizeSolveSettings($this->profileType, $profile);

        $this->assertSame(12, $profile->getAddMin());
        $this->assertSame(12, $profile->getAddMax());
    }

    /**
     * @test
     */
    public function eventListenerNormalisesSubtractionSolveSettings(): void
    {
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'subFMin' => 10,
            'subFMax' => 10,
            'subSMin' => 5,
            'subSMax' => 5,
            'subMin' => 7,
            'subMax' => 1,
            ]);
        $this->normalizeSolveSettings($this->profileType, $profile);

        $this->assertSame(5, $profile->getSubMin());
        $this->assertSame(5, $profile->getSubMax());
    }

    private function normalizeSolveSettings(ProfileType $profileType, Profile $profile): void
    {
        $profileType->normalizeSolveSettings($this->createFormEvent($profile));
    }

    private function createFormEvent(Profile $profile): FormEvent
    {
        return new  FormEvent($this->createMock(FormInterface::class), $profile);
    }
}
