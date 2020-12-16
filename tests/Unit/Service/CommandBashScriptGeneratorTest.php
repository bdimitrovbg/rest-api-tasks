<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Tests\Unit\Service;

use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\RestApiTasksException;
use Dimitrov\RestApiTasks\Service\CommandBashScriptGenerator;
use PHPUnit\Framework\TestCase;
use stdClass;

class CommandBashScriptGeneratorTest extends TestCase
{
    private CommandBashScriptGenerator $commandBashScriptGenerator;

    public function setUp()
    {
        $this->commandBashScriptGenerator = new CommandBashScriptGenerator();
    }

    /**
     * @param string $expected
     * @param Task[] $tasks
     *
     * @dataProvider providerTestGenerate
     */
    public function testGenerate(string $expected, array $tasks)
    {
        $script = $this->commandBashScriptGenerator->generate($tasks);
        $this->assertSame($expected, $script);
    }

    public function providerTestGenerate(): array
    {
        return [
            'single line' => [
                'expected' => implode(
                        PHP_EOL,
                        [
                            '#!/usr/bin/env bash',
                            '',
                            'echo Test',
                        ]
                    ) . PHP_EOL,
                'tasks' => [
                    (new Task('Test'))->setCommand('echo Test'),
                ],
            ],
            'multi line' => [
                'expected' => implode(
                        PHP_EOL,
                        [
                            '#!/usr/bin/env bash',
                            '',
                            'echo Test',
                            'echo Test 2',
                        ]
                    ) . PHP_EOL,
                'tasks' => [
                    (new Task('Test'))->setCommand('echo Test'),
                    (new Task('Test 2'))->setCommand('echo Test 2'),
                ],
            ],
        ];
    }

    /**
     * @param string $expectedExceptionClass
     * @param array $tasks
     *
     * @dataProvider providerTestGenerateExceptions
     */
    public function testGenerateExceptions(string $expectedExceptionClass, array $tasks)
    {
        $this->expectException($expectedExceptionClass);
        $this->commandBashScriptGenerator->generate($tasks);
    }

    public function providerTestGenerateExceptions(): array
    {
        return [
            'invalid object' => [
                'expectedExceptionClass' => RestApiTasksException::class,
                'tasks' => [
                    (new Task('Test'))->setCommand('echo Test'),
                    new stdClass(),
                ],
            ]
        ];
    }
}
