<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Tests\Unit\DependencyGraph;

use Dimitrov\RestApiTasks\DependencyGraph\DependencyGraph;
use Dimitrov\RestApiTasks\DependencyGraph\DependencyNode;
use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\DependencyGraphException;
use PHPUnit\Framework\TestCase;

class DependencyGraphTest extends TestCase
{
    private DependencyGraph $dependencyGraph;

    public function setUp()
    {
        $this->dependencyGraph = new DependencyGraph();
    }

    /**
     * @param DependencyNode[] $expected
     * @param DependencyNode[] $nodes
     *
     * @dataProvider providerTestAddNode
     */
    public function testAddNode(array $expected, array $nodes)
    {
        foreach ($nodes as $node) {
            $this->dependencyGraph->addNode($node);
        }

        $this->assertEquals($expected, $this->dependencyGraph->getNodes());
    }

    public function providerTestAddNode(): array
    {
        $nameA = 'TestA';
        $elementA = new Task($nameA);
        $nodeA = new DependencyNode($elementA);

        $nameB = 'TestB';
        $elementB = new Task($nameB);
        $nodeB = new DependencyNode($elementB);

        return [
            'multiple node' => [
                'expected' => [
                    hash('sha1', $nameA) => $nodeA,
                    hash('sha1', $nameB) => $nodeB,
                ],
                'nodes' => [
                    $nodeA,
                    $nodeB,
                ],
            ],
        ];
    }

    /**
     * @param DependencyNode[] $expected
     * @param DependencyNode[] $nodes
     *
     * @dataProvider providerTestResolve
     */
    public function testResolve(array $expected, array $nodes)
    {
        foreach ($nodes as $node) {
            $this->dependencyGraph->addNode($node);
        }

        $this->assertEquals($expected, $this->dependencyGraph->resolve());
    }

    public function providerTestResolve(): array
    {
        $nameA = 'TestA';
        $elementA = new Task($nameA);
        $nodeA = new DependencyNode($elementA);

        $nameC = 'TestC';
        $elementC = (new Task($nameC))->setDependencies(['TaskA']);
        $nodeC = (new DependencyNode($elementC))->addDependency($nodeA);

        $nameB = 'TestB';
        $elementB = (new Task($nameB))->setDependencies(['TaskC']);
        $nodeB = (new DependencyNode($elementB))->addDependency($nodeC);


        $nameD = 'TestD';
        $elementD = (new Task($nameD))->setDependencies(['TaskB', 'TaskC']);
        $nodeD = (new DependencyNode($elementD))
            ->addDependency($nodeB)
            ->addDependency($nodeC);

        return [
            'single node' => [
                'expected' => [
                    $nodeA->getElement(),
                ],
                'nodes' => [
                    $nodeA,
                ],
            ],
            'dependant nodes' => [
                'expected' => [
                    $nodeA->getElement(),
                    $nodeC->getElement(),
                    $nodeB->getElement(),
                    $nodeD->getElement(),
                ],
                'nodes' => [
                    $nodeD,
                    $nodeC,
                    $nodeB,
                    $nodeA,
                ],
            ],
        ];
    }

    /**
     * @param string $expectedExceptionClass
     * @param DependencyNode[] $nodes
     *
     * @dataProvider providerTestResolveExceptions
     */
    public function testResolveExceptions(string $expectedExceptionClass, array $nodes)
    {
        foreach ($nodes as $node) {
            $this->dependencyGraph->addNode($node);
        }
        $this->expectException($expectedExceptionClass);

        $this->dependencyGraph->resolve();
    }

    public function providerTestResolveExceptions(): array
    {
        $nameA = 'TestA';
        $elementA = (new Task($nameA))->setDependencies(['TaskB']);
        $nodeA = new DependencyNode($elementA);

        $nameB = 'TestB';
        $elementB = (new Task($nameB))->setDependencies(['TaskA']);
        $nodeB = new DependencyNode($elementB);

        $nodeA->addDependency($nodeB);
        $nodeB->addDependency($nodeA);

        return [
            'circular dependency' => [
                'expected' => DependencyGraphException::class,
                'nodes' => [
                    $nodeA,
                    $nodeB,
                ],
            ],
        ];
    }
}
