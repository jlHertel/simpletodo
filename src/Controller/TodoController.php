<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\TodoDto;
use App\Entity\Todo;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/todos')]
#[OA\Info(version: "1.0", title: "Todo API")]
#[OA\Server(url: 'http://localhost:8080', description: 'Local development server')]
class TodoController extends AbstractController {

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface    $serializer,
        private readonly ValidatorInterface     $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/todos',
        operationId: 'listAll',
        description: 'Gets all Todos',
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of all Todos',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: Todo::class)
                )
            )
        ])]
    public function listAll(): Response {
        $allTodos = $this->entityManager->getRepository(Todo::class)->findAll();

        $serialized = $this->serializer->serialize($allTodos, 'json');

        return new JsonResponse($serialized, Response::HTTP_OK, [], true);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(path: '/todos',
        operationId: 'create',
        requestBody: new OA\RequestBody(
            description: 'The new todo',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: TodoDto::class)
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns an empty body with 200 code to indicate success'
            ),
            new OA\Response(
                response: 422,
                description: 'Returns a 422 code when validation fails together with a description of the fields'
            )
        ]
    )]
    public function create(Request $request): Response {
        $dto = $this->deserializeAndValidateTodo($request, 'create');

        $todo = new Todo();
        $todo->updateFrom($dto);

        $this->entityManager->persist($dto);
        $this->entityManager->flush();

        return new Response();
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Get(
        path: '/todos/{id}',
        operationId: 'list',
        description: 'Gets a single Todo by it\'s id',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A single Todo is returned',
                content: new OA\JsonContent(ref: Todo::class)
            ),
            new OA\Response(
                response: 404,
                description: 'Returns a 404 error if it cannot find the TODO with the specified id'
            )
        ])]
    public function list(int $id): Response {
        $todo = $this->fetchTodo($id);

        $serialized = $this->serializer->serialize($todo, 'json');

        return new JsonResponse($serialized, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[OA\Put(path: '/todos/{id}',
        operationId: 'update',
        requestBody: new OA\RequestBody(
            description: 'The new todo',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: TodoDto::class)
            )
        ),
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns an empty body with 200 code to indicate success'
            ),
            new OA\Response(
                response: 404,
                description: 'Returns a 404 error if it cannot find the TODO with the specified id'
            ),
            new OA\Response(
                response: 422,
                description: 'Returns a 422 code when validation fails together with a description of the fields'
            )
        ]
    )]
    public function update(Request $request, int $id): Response {
        $dto = $this->deserializeAndValidateTodo($request, 'update');
        $todo = $this->fetchTodo($id);
        $todo->updateFrom($dto);
        $this->entityManager->flush();
        return new Response();
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Delete(path: '/todos/{id}',
        operationId: 'delete',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns an empty body with 200 code to indicate success'
            ),
            new OA\Response(
                response: 404,
                description: 'Returns a 404 error if it cannot find the TODO with the specified id'
            )
        ]
    )]
    public function delete(int $id): Response {
        $todo = $this->fetchTodo($id);
        $this->entityManager->remove($todo);
        $this->entityManager->flush();
        return new Response();
    }

    private function deserializeAndValidateTodo(Request $request, string $serializationGroup): TodoDto {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups($serializationGroup)
            ->toArray();
        $deserialized = $this->serializer->deserialize($request->getContent(), TodoDto::class, 'json', $context);

        $errors = $this->validator->validate($deserialized);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            throw new UnprocessableEntityHttpException($errorsString);
        }

        return $deserialized;
    }

    private function fetchTodo(int $id): Todo {
        $todo = $this->entityManager->getRepository(Todo::class)->find($id);

        if (!$todo) {
            throw $this->createNotFoundException('Todo not found for id ' . $id);
        }

        return $todo;
    }
}
