<?php


declare(strict_types=1);

namespace Dimitrov\RestApiTasks\DependencyGraph;

interface DependencyNodeElementInterface
{
    public function getName(): string;
    public function getDependencies(): array;
}
