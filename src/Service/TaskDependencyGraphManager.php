<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Service;

use Dimitrov\RestApiTasks\DependencyGraph\DependencyGraph;
use Dimitrov\RestApiTasks\DependencyGraph\DependencyNode;
use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\RestApiTasksException;

class TaskDependencyGraphManager
{
    private DependencyGraph $dependencyGraph;

    public function __construct(DependencyGraph $dependencyGraph)
    {
        $this->dependencyGraph = $dependencyGraph;
    }

    /**
     * @param Task[] $tasks
     * @return self
     * @throws RestApiTasksException
     */
    public function build(array $tasks): self
    {
//        usort(
//            $tasks,
//            fn(Task $taskA,Task $taskB) => count($taskA->getDependencies()) <=> count($taskB->getDependencies())
//        );
        $nodes = [];
        foreach ($tasks as $task) {
            if (isset($nodes[$task->getName()])) {
                throw new RestApiTasksException('Duplicate Node');
            }

            $nodes[$task->getName()] = new DependencyNode($task);
        }

        foreach ($nodes as $node) {
            foreach ($node->getElement()->getDependencies() as $dependencyName) {
                if (!isset($nodes[$dependencyName])) {
                    throw new RestApiTasksException('Dependancy node does not exist.');
                }
                $node->dependsOn($nodes[$dependencyName]);
            }
            $this->dependencyGraph->addNode($node);
        }

        return $this;
    }

    /**
     * @return Task[]
     */
    public function resolve(): array
    {
        return $this->dependencyGraph->resolve();
    }
}