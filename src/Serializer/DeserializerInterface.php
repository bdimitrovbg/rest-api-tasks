<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

interface DeserializerInterface
{
    public function deserialize(object $entity): array;
}
