<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Service;

interface CommandInterface
{
    public function getCommand(): ?string;
}
