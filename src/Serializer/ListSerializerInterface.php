<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

interface ListSerializerInterface
{
    public function serialize(array $data): array;
}
