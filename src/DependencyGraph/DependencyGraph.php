<?php

namespace Dimitrov\RestApiTasks\DependencyGraph;

use ArrayObject;
use Dimitrov\RestApiTasks\Exception\DependencyGraphException;

class DependencyGraph
{
    /**
     * @var DependencyNode[]
     */
    private array $nodes;

    /**
     * @var string[]
     */
    private array $dependencies;

    public function __construct()
    {
        $this->dependencies = [];
        $this->nodes = [];
    }

    public function addNode(DependencyNode $node)
    {
        if (!isset($this->dependencies[$node->getHash()])) {
            $this->dependencies[$node->getHash()] = [];
            $this->nodes[$node->getHash()] = $node;

            foreach ($node->getDependencies() as $nodeDependency) {
                $this->addDependency($node, $nodeDependency);
            }
        }
    }

    public function addDependency(DependencyNode $node, DependencyNode $dependsOn)
    {
        if (!isset($this->dependencies[$node->getHash()])) {
            $this->addNode($node);
        }
        if (!isset($this->dependencies[$dependsOn->getHash()])) {
            $this->addNode($dependsOn);
        }
        if (!in_array($dependsOn->getHash(), $this->dependencies[$node->getHash()], true)) {
            $this->dependencies[$node->getHash()][] = $dependsOn->getHash();
        }

        $node->dependsOn($dependsOn);
    }

    public function findRootNodes(): array
    {
        $rootNodes = $this->nodes;
        foreach ($this->dependencies as $nodeHash => $nodeDependencyHashes) {
            foreach ($nodeDependencyHashes as $nodeDependencyHash) {
                unset($rootNodes[$nodeDependencyHash]);
            }
        }
        return $rootNodes;
    }

    /**
     *
     * @return DependencyNode[]
     * @throws DependencyGraphException
     */
    public function resolve(): array
    {
        if (count($this->dependencies) === 0) {
            return [];
        }

        $resolved = new ArrayObject();
        $seen = new ArrayObject();
        foreach ($this->findRootNodes() as $rootNode) {
            $this->innerResolve($rootNode, $resolved, $seen);
        }

        if ($resolved->count() !== count($this->nodes)) {
            throw new DependencyGraphException('Resolved nodes do not match original nodes.');
        }

        return array_map(
            function (DependencyNode $node) { return $node->getElement(); },
            $resolved->getArrayCopy()
        );
    }

    /**
     * @param DependencyNode $rootNode
     * @param ArrayObject|DependencyNode[] $resolved
     * @param ArrayObject|DependencyNode[] $seen
     *
     * @throws DependencyGraphException
     */
    private function innerResolve(DependencyNode $rootNode, ArrayObject $resolved, ArrayObject $seen)
    {
        $seen->append($rootNode);
        foreach ($rootNode->getDependencies() as $edge) {
            if (!$this->arrayObjectContains($edge, $resolved)) {
                if ($this->arrayObjectContains($edge, $seen)) {
                    throw new DependencyGraphException(
                        sprintf(
                            'Circular dependency detected: %s depends on %s',
                            $rootNode->getElement()->getName(),
                            $edge->getElement()->getName())
                    );
                }

                $this->innerResolve($edge, $resolved, $seen);
            }
        }

        $resolved->append($rootNode);
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @return DependencyNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    private function arrayObjectContains(DependencyNode $needle, ArrayObject $haystack): bool
    {
        foreach ($haystack as $node) {
            if ($node === $needle) {
                return true;
            }
        }

        return false;
    }
}
