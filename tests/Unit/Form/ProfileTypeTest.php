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

    /**
     * @test
     */
    public function eventListenerNormalisesMultiplicationSolveSettings(): void
    {
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'multFMin' => 3,
            'multFMax' => 5,
            'multSMin' => 3,
            'multSMax' => 5,
            'multMin' => 12,
            'multMax' => 11,
        ]);
        $this->normalizeSolveSettings($this->profileType, $profile);

        $this->assertSame(12, $profile->getMultMin());
        $this->assertSame(12, $profile->getMultMax());
    }

    /**
     * @test
     */
    public function eventListenerNormalisesDivisionSolveSettings(): void
    {
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'divFMin' => 15,
            'divFMax' => 20,
            'divSMin' => 2,
            'divSMax' => 5,
            'divMin' => 0,
            'divMax' => 50,
        ]);
        $this->normalizeSolveSettings($this->profileType, $profile);

        $this->assertSame(3, $profile->getDivMin());
        $this->assertSame(10, $profile->getDivMax());
    }

    /**
     * @test
     */
    public function eventListenerNormalisesPercentSettings(): void
    {
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'addPerc' => 1,
            'subPerc' => 2,
            'multPerc' => 0,
            'divPerc' => 1,
        ]);
        $this->profileType->normalizePercentData($this->createFormEvent($profile));

        $this->assertSame(25, $profile->getAddPerc());
        $this->assertSame(50, $profile->getSubPerc());
        $this->assertSame(0, $profile->getMultPerc());
        $this->assertSame(25, $profile->getDivPerc());
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
