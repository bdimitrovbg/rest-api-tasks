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

    /** @var DependencyNode[]  */
    private array $nodes;

    public function __construct(DependencyGraph $dependencyGraph)
    {
        $this->dependencyGraph = $dependencyGraph;
        $this->nodes = [];
    }

    /**
     * @param Task[] $tasks
     *
     * @return Task[]
     * @throws RestApiTasksException
     */
    public function resolve(array $tasks): array
    {
        foreach ($tasks as $task) {
            if (isset($this->nodes[$task->getName()])) {
                throw new RestApiTasksException(sprintf('Duplicate Node: %s', $task->getName()));
            }

            $this->nodes[$task->getName()] = new DependencyNode($task);
        }

        foreach ($this->nodes as $node) {
            if(count($node->getElement()->getDependencies()) === 0){
                $this->dependencyGraph->addNode($node);
            } else {
                foreach ($node->getElement()->getDependencies() as $dependencyName) {
                    if (!isset($this->nodes[$dependencyName])) {
                        throw new RestApiTasksException('Dependency node does not exist.');
                    }
                    $this->dependencyGraph->addNode($node, $this->nodes[$dependencyName]);
                }
            }
        }

        return $this->dependencyGraph->resolve();
    }

    public function getNodes()
    {
        return $this->nodes;
    }
}
