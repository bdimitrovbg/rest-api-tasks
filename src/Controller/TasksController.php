<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Controller;

use Dimitrov\RestApiTasks\Exception\RestApiTasksException;
use Dimitrov\RestApiTasks\Serializer\ListSerializerInterface;
use Dimitrov\RestApiTasks\Service\TaskBashScriptGenerator;
use Dimitrov\RestApiTasks\Service\TaskDependencyGraphManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class TasksController extends AbstractFOSRestController
{
    private TaskDependencyGraphManager $taskDependencyGraphManager;
    private TaskBashScriptGenerator $bashScriptGenerator;
    private ListSerializerInterface $taskListMapper;

    public function __construct(
        TaskDependencyGraphManager $taskDependencyGraphManager,
        TaskBashScriptGenerator $bashScriptGenerator,
        ListSerializerInterface $taskListMapper

    )
    {
        $this->taskDependencyGraphManager = $taskDependencyGraphManager;
        $this->bashScriptGenerator = $bashScriptGenerator;
        $this->taskListMapper = $taskListMapper;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function resolveDependencies(Request $request): Response
    {
        $tasks = $this->normalizeTaskRequestData($request);
        $dependencyResolvedTasks = $this->taskDependencyGraphManager
            ->build($tasks)
            ->resolve();

        return new Response(
            $this->normalizeTaskResponseData($dependencyResolvedTasks),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function generateBashScript(Request $request): Response
    {
        $tasks = $this->normalizeTaskRequestData($request);
        $dependencyResolvedTasks = $this->taskDependencyGraphManager
            ->build($tasks)
            ->resolve();

        $script = $this->bashScriptGenerator->generate($dependencyResolvedTasks);

        return new Response(
            $script,
            200,
            ['Content-Type' => 'text/plain']
        );
    }

    private function normalizeTaskRequestData(Request $request): array
    {
        try {
            $tasks = $this->taskListMapper->serialize($this->getRequestData($request));
        } catch (RestApiTasksException $exception) {
            throw new HttpException(400, sprintf('Invalid data. %s', $exception->getMessage()));
        }

        return $tasks;
    }

    private function getRequestData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, "Invalid data");
        }

        return $data;
    }

    private function normalizeTaskResponseData(array $entities)
    {
        $data = $this->taskListMapper->deserialize($entities);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}