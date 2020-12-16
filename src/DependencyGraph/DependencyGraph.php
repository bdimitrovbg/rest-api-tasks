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

    private function processNode(DependencyNode $node)
    {
        if (!isset($this->dependencies[$node->getHash()])) {
            $this->dependencies[$node->getHash()] = [];
            $this->nodes[$node->getHash()] = $node;

            foreach ($node->getDependencies() as $nodeDependency) {
                $this->addNode($node, $nodeDependency);
            }
        }
    }

    public function addNode(DependencyNode $node, DependencyNode $nodeDependency = null)
    {
        if (!isset($this->dependencies[$node->getHash()])) {
            $this->processNode($node);
        }
        if($nodeDependency !== null) {
            if (!isset($this->dependencies[$nodeDependency->getHash()])) {
                $this->processNode($nodeDependency);
            }
            if (!in_array($nodeDependency->getHash(), $this->dependencies[$node->getHash()], true)) {
                $this->dependencies[$node->getHash()][] = $nodeDependency->getHash();
            }

            $node->addDependency($nodeDependency);
        }
    }

    /**
     * @return DependencyNode[]
     */
    private function findRootNodes(): array
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

        $resolved = [];
        $seen = [];
        foreach ($this->findRootNodes() as $rootNode) {
            $this->innerResolve($rootNode, $resolved, $seen);
        }

        if (count($resolved) !== count($this->nodes)) {
            throw new DependencyGraphException('Resolved nodes do not match original nodes.');
        }

        return array_map(
            function (string $nodeHash) {
                return $this->nodes[$nodeHash]->getElement();
            },
            $resolved
        );
    }

    /**
     * @param DependencyNode $rootNode
     * @param ArrayObject|DependencyNode[] $resolved
     * @param ArrayObject|DependencyNode[] $seen
     *
     * @throws DependencyGraphException
     */
    private function innerResolve(DependencyNode $rootNode, array &$resolved, array &$seen)
    {
        $seen[] = $rootNode->getHash();
        foreach ($rootNode->getDependencies() as $edge) {
            if (!in_array($edge->getHash(), $resolved, true)) {
                if (in_array($edge->getHash(), $seen, true)) {
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

        $resolved[] = $rootNode->getHash();
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
}
