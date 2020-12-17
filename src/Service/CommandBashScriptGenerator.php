<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Service;

use Dimitrov\RestApiTasks\Exception\RestApiTasksException;

class CommandBashScriptGenerator implements CommandScriptGeneratorInterface
{
    const HEADER = '#!/usr/bin/env bash';

    /**
     * @param CommandInterface[] $commands
     *
     * @return string
     *
     * @throws RestApiTasksException
     */
    public function generate(array $commands): string
    {
        $script = sprintf('%s%s%s', self::HEADER, PHP_EOL, PHP_EOL);
        foreach ($commands as $command) {
            if (!$command instanceof CommandInterface) {
                throw new RestApiTasksException('Command expected');
            }

            $script .= sprintf(
                '%s%s',
                $command->getCommand() !== null ? $command->getCommand() : '',
                PHP_EOL
            );
        }

        return $script;
    }
}
