<?php


namespace Dimitrov\RestApiTasks\Service;


use Dimitrov\RestApiTasks\Entity\Task;

interface TaskScriptGeneratorInterface
{
    /**
     * @param Task[] $tasks
     *
     * @return string
     */
    public function generate(array $tasks): string;
}