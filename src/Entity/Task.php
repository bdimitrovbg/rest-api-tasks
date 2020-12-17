<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Entity;

use Dimitrov\RestApiTasks\DependencyGraph\DependencyNodeElementInterface;
use Dimitrov\RestApiTasks\Service\CommandInterface;

class Task implements DependencyNodeElementInterface, CommandInterface
{
    private string $name;
    private ?string $command = null;

    /**
     * @var string[]
     */
    private array $dependencies;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->dependencies = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): Task
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param string[] $dependencies
     * @return Task
     */
    public function setDependencies(array $dependencies): Task
    {
        $this->dependencies = $dependencies;

        return $this;
    }
}
