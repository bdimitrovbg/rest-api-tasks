<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Tests\Unit\Serializer;

use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\RestApiTasksException;
use Dimitrov\RestApiTasks\Serializer\TaskSerializer;
use PHPUnit\Framework\TestCase;

class TaskSerializerTest extends TestCase
{
    private TaskSerializer $taskSrializer;

    public function setUp()
    {
        $this->taskSrializer = new TaskSerializer();
    }

    /**
     * @param Task $expected
     * @param array $data
     *
     * @dataProvider providerTestSerialize
     */
    public function testSerialize(Task $expected, array $data)
    {
        $this->assertEquals($expected, $this->taskSrializer->serialize($data));
    }

    public function providerTestSerialize(): array
    {
        return [
            'single entity correct data' => [
                'expected' => (new Task('testCommandA'))
                    ->setCommand('echo TestA')
                    ->setDependencies(['testCommandB']),
                'data' => [
                    'name' => 'testCommandA',
                    'command' => 'echo TestA',
                    'requires' => ['testCommandB'],
                ]
            ],
            'mixed entities correct data' => [
                'expected' => (new Task('testCommandA'))
                    ->setCommand('echo TestA')
                    ->setDependencies(['testCommandB']),
                'data' => [
                    'name' => 'testCommandA',
                    'command' => 'echo TestA',
                    'requires' => ['testCommandB'],
                    'otherDataKeyA' => 'SomeValue',
                    'otherDataKeyB' => 15,
                ],
            ],
        ];
    }

    /**
     * @param string $expectedExceptionClass
     * @param array $data
     *
     * @dataProvider providerTestSerializeExceptions
     */
    public function testSerializeExceptions(string $expectedExceptionClass, array $data)
    {
        $this->expectException($expectedExceptionClass);
        $this->taskSrializer->serialize($data);
    }

    public function providerTestSerializeExceptions(): array
    {
        return [
            'missing key name' => [
                'expectedExceptionClass' => RestApiTasksException::class,
                'data' => [
                    'command' => 'echo TestA',
                    'requires' => ['testCommandB'],
                ],
            ],
            'missing key command' => [
                'expectedExceptionClass' => RestApiTasksException::class,
                'data' => [
                    'name' => 'testCommandA',
                    'requires' => ['testCommandB'],
                ],
            ],
        ];
    }

    /**
     * @param array $expected
     * @param Task $entity
     *
     * @dataProvider providerTestDeserialize
     */
    public function testDeserialize(array $expected, Task $entity)
    {
        $this->assertEquals($expected, $this->taskSrializer->deserialize($entity));
    }

    public function providerTestDeserialize(): array
    {
        return [
            'valid data' => [
                'expected' => [
                    'name' => 'Test',
                    'command' => 'echo Test',
                ],
                'entity' => (new Task('Test'))
                    ->setCommand('echo Test'),
            ],
        ];
    }
}
