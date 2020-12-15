<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Serializer;

interface SerializerInterface
{
    public function serialize(array $data): object;
}