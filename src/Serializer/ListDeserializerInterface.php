<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

interface ListDeserializerInterface
{
    public function deserialize(array $entities): array;
}
