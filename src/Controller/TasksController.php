<?php

declare(strict_types=1);

namespace Dimitrov\RestApiTasks\Controller;

use Dimitrov\RestApiTasks\Entity\Task;
use Dimitrov\RestApiTasks\Exception\RestApiTasksException;
use Dimitrov\RestApiTasks\Serializer\ListSerializerInterface;
use Dimitrov\RestApiTasks\Service\CommandBashScriptGenerator;
use Dimitrov\RestApiTasks\Service\CommandScriptGeneratorInterface;
use Dimitrov\RestApiTasks\Service\TaskDependencyGraphManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class TasksController extends AbstractFOSRestController
{
    private TaskDependencyGraphManager $taskDependencyGraphManager;
    private CommandScriptGeneratorInterface $bashScriptGenerator;
    private ListSerializerInterface $taskListMapper;

    public function __construct(
        TaskDependencyGraphManager $taskDependencyGraphManager,
        CommandScriptGeneratorInterface $bashScriptGenerator,
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
        $dependencyResolvedTasks = $this->processDependencies($request);

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
        $dependencyResolvedTasks = $this->processDependencies($request);

        $script = $this->bashScriptGenerator->generate($dependencyResolvedTasks);
        return new Response(
            $script,
            200,
            ['Content-Type' => 'text/plain']
        );
    }

    /**
     * @param Request $request
     * @return Task[]
     */
    private function processDependencies(Request $request): array
    {
        try {
            $tasks = $this->taskListMapper->serialize($this->normalizeTaskRequestData($request));
            $processedTasks = $this->taskDependencyGraphManager->resolve($tasks);
        } catch (RestApiTasksException $restApiTasksException) {
            throw new HttpException(400, sprintf('API Error: %s', $restApiTasksException->getMessage()));
        } catch (Exception $exception) {
            throw new HttpException(400, sprintf('Generic error: %s', $exception->getMessage()));
        }

        return $processedTasks;
    }

    /**
     * @param Request $request
     * @return mixed[]
     * @throws RestApiTasksException
     */
    private function normalizeTaskRequestData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
            throw new RestApiTasksException("Invalid data");
        }

        return $data;
    }

    private function normalizeTaskResponseData(array $entities)
    {
        $data = $this->taskListMapper->deserialize($entities);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}