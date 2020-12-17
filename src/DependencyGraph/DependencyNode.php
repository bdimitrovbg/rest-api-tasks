<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\DependencyGraph;

use Dimitrov\RestApiTasks\Exception\DependencyGraphException;

class DependencyNode
{
    private string $hash;
    private DependencyNodeElementInterface $element;

    /**
     * @var DependencyNode[]
     */
    private array $dependencies;

    public function __construct(DependencyNodeElementInterface $element)
    {
        $this->element = $element;
        $this->hash = hash('sha1', $element->getName());
        $this->dependencies = [];
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getElement(): DependencyNodeElementInterface
    {
        return $this->element;
    }

    /**
     * @param DependencyNode $dependencyNode
     *
     * @return $this
     *
     * @throws DependencyGraphException
     */
    public function addDependency(DependencyNode $dependencyNode): self
    {
        if ($dependencyNode->getHash() === $this->getHash()) {
            throw new DependencyGraphException('Can\'t add dependency to self.');
        }

        if (!in_array($dependencyNode->getHash(), $this->dependencies, true)) {
            $this->dependencies[$dependencyNode->getHash()] = $dependencyNode;
        }

        return $this;
    }

    /**
     * @return DependencyNode[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
