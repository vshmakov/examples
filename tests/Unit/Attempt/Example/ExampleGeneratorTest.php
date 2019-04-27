<?php

namespace App\Tests\Unit\Attempt\Example;

use App\Attempt\Example\ExampleCoefficientGenerator;
use App\Attempt\Example\ExampleGeneratorInterface;
use App\Attempt\Example\Solver;
use App\Entity\Example;
use App\Entity\Settings;
use App\Object\ObjectAccessor;
use App\Service\ExampleManager;
use App\Tests\Unit\BaseTestCase;
use App\Tests\Unit\ServiceInitializerTrait;

final class ExampleGeneratorTest extends BaseTestCase
{
    use ServiceInitializerTrait;
    /** @var ExampleGeneratorInterface */
    private $exampleGenerator;

    protected function setUp()
    {
        $exampleCoefficientGenerator = new ExampleCoefficientGenerator(new Solver());

        $this->exampleGenerator = new ExampleManager(
            $this->initializeNormalizer(),
            $exampleCoefficientGenerator
        );
    }

    public function actionsProvider(): array
    {
        return $this->wrapItemsInArray([
            'addPerc',
            'subPerc',
            'multPerc',
            'divPerc',
        ]);
    }

    /**
     * @test
     * @dataProvider  actionsProvider
     */
    public function serviceGeneratesAdditionExamples(string $action): void
    {
        $settings = $this->createSettings($action);
        $examples = [];

        for ($i = 1; $i <= 30; ++$i) {
            $example = $this->exampleGenerator->generate($settings, $examples);
            $examples[] = $example;
            $this->assertInstanceOf(Example::class, $example);
        }
    }

    private function createSettings(string $action): Settings
    {
        $settings = ObjectAccessor::initialize(Settings::class, [
            'addPerc' => 0,
            'subperc' => 0,
            'multPerc' => 0,
            'divPerc' => 100,
        ]);
        ObjectAccessor::setValue($settings, $action, 100);

        return $settings;
    }
}
