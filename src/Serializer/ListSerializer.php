<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\RestApiTasksException;

class ListSerializer implements ListSerializerInterface, ListDeserializerInterface
{
    private SerializerInterface $mapper;
    private ?string $containerKey;

    public function __construct(SerializerInterface $mapper, string $containerKey = null)
    {
        $this->mapper = $mapper;
        $this->containerKey = $containerKey;
    }

    /**
     * @param mixed[] $data
     * @return Task[]
     *
     * @throws RestApiTasksException
     */
    public function serialize(array $data): array
    {
        if ($this->containerKey !== null) {
            if (!(isset($data[$this->containerKey]) && count($data[$this->containerKey]))) {
                throw new RestApiTasksException(sprintf('Invalid container key: %s', $this->containerKey));
            }
            $data = $data[$this->containerKey];
        }

        $list = [];
        foreach ($data as $itemData) {
            $list[] = $this->mapper->serialize($itemData);
        }

        return $list;
    }

    /**
     * @param Task[] $entities
     * @return mixed[]
     */
    public function deserialize(array $entities): array
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->mapper->deserialize($entity);
        }

        return $result;
    }
}