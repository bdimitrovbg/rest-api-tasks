<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\DependencyGraph;

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

    public function dependsOn(DependencyNode $node): self
    {
        if (!in_array($node->getHash(), $this->dependencies, true)) {
            $this->dependencies[$node->getHash()] = $node;
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
