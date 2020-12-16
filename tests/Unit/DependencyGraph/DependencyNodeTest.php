<?php

declare(strict_types=1);


namespace Dimitrov\RestApiTasks\Tests\Unit\DependencyGraph;

use Dimitrov\RestApiTasks\DependencyGraph\DependencyNode;
use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\DependencyGraphException;
use PHPUnit\Framework\TestCase;

class DependencyNodeTest extends TestCase
{
    private DependencyNode $dependencyNode;

    public function setUp()
    {
        $element = new Task('BaseNode');
        $this->dependencyNode = new DependencyNode($element);
    }

    /**
     * @param string[] $expected
     * @param DependencyNode[] $dependencyNodes
     *
     * @dataProvider providerTestAddDependency
     */
    public function testAddDependency(array $expected, array $dependencyNodes)
    {
        foreach ($dependencyNodes as $dependency) {
            $this->dependencyNode->addDependency($dependency);
        }
        $this->assertSame($expected, $this->dependencyNode->getDependencies());
    }

    public function providerTestAddDependency(): array
    {
        $nameA = 'Dependency A';
        $dependencyA = new DependencyNode(new Task($nameA));
        $nameB = 'Dependency B';
        $dependencyB = new DependencyNode(new Task($nameB));

        return [
            'single dependency' => [
                'expected' => [
                    hash('sha1', $nameA) => $dependencyA,
                ],
                'dependencyNodes' => [$dependencyA],
            ],
            'multiple dependency' => [
                'expected' => [
                    hash('sha1', $nameA) => $dependencyA,
                    hash('sha1', $nameB) => $dependencyB,
                ],
                'dependencyNodes' => [$dependencyA, $dependencyB],
            ],
        ];
    }

    /**
     * @param string $expectedExceptionClass
     * @param DependencyNode $dependency
     *
     * @dataProvider providerTestAddDependencyExceptions
     */
    public function testAddDependencyExceptions(string $expectedExceptionClass, DependencyNode $dependency)
    {
        $this->expectException($expectedExceptionClass);
        $this->dependencyNode->addDependency($dependency);
    }

    public function providerTestAddDependencyExceptions(): array
    {
        return [
            'dependency to self' => [
                'expectedExceptionClass' => DependencyGraphException::class,
                'dependency' => new DependencyNode(new Task('BaseNode')),
            ]
        ];
    }
}
