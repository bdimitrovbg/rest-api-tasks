<?php


namespace Dimitrov\RestApiTasks\Service;


use Dimitrov\RestApiTasks\Entity\Task;

interface CommandScriptGeneratorInterface
{
    /**
     * @param CommandInterface[] $commands
     *
     * @return string
     */
    public function generate(array $commands): string;
}
