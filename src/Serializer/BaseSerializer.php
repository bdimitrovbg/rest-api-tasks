<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

use Dimitrov\RestApiTasks\Exception\RestApiTasksException;

class BaseSerializer
{
    /**
     * @param mixed[] $data
     * @param string[] $keys
     *
     * @throws RestApiTasksException
     */
    protected function checkRequiredKeys(array $data, array $keys)
    {
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                throw new RestApiTasksException(sprintf('Missing key: %s', $key));
            }
        }
    }
}
