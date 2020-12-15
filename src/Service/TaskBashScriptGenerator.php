<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Service;

use Dimitrov\RestApiTasks\Entity\Task;

class TaskBashScriptGenerator implements TaskScriptGeneratorInterface
{
    const HEADER = '#!/usr/bin/env bash';

    /**
     * @param Task[] $tasks
     *
     * @return string
     */
    public function generate(array $tasks): string
    {
        $script = self::HEADER . PHP_EOL;
        foreach ($tasks as $task) {
            $script .= $task->getCommand() . PHP_EOL;
        }

        return $script;
    }
}