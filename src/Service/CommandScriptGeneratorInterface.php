<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Service;

interface CommandScriptGeneratorInterface
{
    /**
     * @param CommandInterface[] $commands
     *
     * @return string
     */
    public function generate(array $commands): string;
}
