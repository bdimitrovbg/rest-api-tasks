<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Tests\Unit\Service;

use Dimitrov\RestApiTasks\DependencyGraph\DependencyGraph;
use Dimitrov\RestApiTasks\DependencyGraph\DependencyNode;
use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\RestApiTasksException;
use Dimitrov\RestApiTasks\Service\TaskDependencyGraphManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskDependencyGraphManagerTest extends TestCase
{
    private TaskDependencyGraphManager $dependencyGraphManager;
    /** @var DependencyGraph|MockObject */
    private $dependencyGraph;

    public function setUp()  {
        $this->dependencyGraph = $this->createMock(DependencyGraph::class);
        $this->dependencyGraphManager = new TaskDependencyGraphManager(
            $this->dependencyGraph
        );
    }

    /**
     * @param DependencyNode[] $expectedNodes
     * @param Task[] $tasks
     * @param DependencyNode[] $mockedResolvedNodes
     *
     * @dataProvider providerTestResolve
     */
    public function testResolve(array $expectedNodes, array $tasks, array $mockedResolvedNodes)
    {
        $this->dependencyGraph
            ->expects($this->exactly(count($tasks)))
            ->method('addNode')
        ;
        $this->dependencyGraph
            ->expects($this->once())
            ->method('resolve')
            ->willReturn($mockedResolvedNodes)
        ;

        $resolvedNodes = $this->dependencyGraphManager->resolve($tasks);

        $this->assertEquals($this->dependencyGraphManager->getNodes(), $expectedNodes);
        $this->assertSame($resolvedNodes, $mockedResolvedNodes);
    }

    public function providerTestResolve(): array
    {
        $taskA = new Task('TaskA');
        $taskB = (new Task('TaskB'))->setDependencies(['TaskA']);

        $nodeA = new DependencyNode($taskA);
        $nodeB = new DependencyNode($taskB);

        return [
            'multiple nodes' => [
                'expectedNodes' => [
                    'TaskB' => $nodeB,
                    'TaskA' => $nodeA,
                ],
                'tasks' => [$taskB, $taskA],
                'mockedResolvedNodes' => [$nodeA, $nodeB],
            ],
        ];
    }

    /**
     * @param string $expectedExceptionClass
     * @param Task[] $tasks
     * @dataProvider providerTestResolveExceptions
     */
    public function testResolveExceptions(string $expectedExceptionClass, array $tasks)
    {
        $this->expectException($expectedExceptionClass);
        $this->dependencyGraphManager->resolve($tasks);
    }

    public function providerTestResolveExceptions(): array
    {
        $taskA = new Task('TaskA');
        $taskB = (new Task('TaskB'))->setDependencies(['TaskC']);
        return [
            'duplicate node' => [
                'expectedExceptionClass' => RestApiTasksException::class,
                'tasks' => [$taskA, $taskA],
            ],
            'dependency node does not exist' => [
                'expectedExceptionClass' => RestApiTasksException::class,
                'tasks' => [$taskB],
            ]
        ];
    }

}
