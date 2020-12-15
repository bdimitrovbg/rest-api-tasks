<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

use Dimitrov\RestApiTasks\Exception\RestApiTasksException;

class BaseSerializer
{
    /**
     * @param array $data
     * @param array $keys
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