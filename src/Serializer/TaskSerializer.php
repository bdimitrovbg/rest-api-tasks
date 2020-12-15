<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

use Dimitrov\RestApiTasks\Entity\Task;

class TaskSerializer extends BaseSerializer implements SerializerInterface, DeserializerInterface
{
    const KEY_NAME = 'name';
    const KEY_COMMAND = 'command';
    const KEY_DEPENDENCIES = 'requires';
    const REQUIRED_KEYS = [
        self::KEY_NAME,
        self::KEY_COMMAND,
    ];

    public function serialize(array $data): Task
    {
        $this->checkRequiredKeys($data, self::REQUIRED_KEYS);
        $task = (new Task($data[self::KEY_NAME]))
            ->setCommand($data[self::KEY_COMMAND]);
        if (isset($data['requires']) and count($data['requires'])) {
            $task->setDependencies($data[self::KEY_DEPENDENCIES]);
        }

        return $task;
    }

    public function deserialize(object $entity): array
    {
        return [
            self::KEY_NAME => $entity->getName(),
            self::KEY_COMMAND => $entity->getCommand(),
        ];
    }
}