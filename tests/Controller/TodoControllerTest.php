<?php

namespace Controller;

use App\Controller\TodoController;
use App\Entity\Todo;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\assertEquals;

class TodoControllerTest extends TestCase {

    /**
     * @var ObjectProphecy<EntityManagerInterface>
     */
    private ObjectProphecy $entityManager;

    /**
     * @var MockObject<TodoRepository>
     */
    private MockObject $repository;

    /**
     * @var ObjectProphecy<SerializerInterface>
     */
    private ObjectProphecy $serializer;
    /**
     * @var ObjectProphecy<ValidatorInterface>
     */
    private ObjectProphecy $validator;

    private Prophet $prophecy;

    private TodoController $controller;

    protected function setUp(): void {
        $this->prophecy = new \Prophecy\Prophet;
        $this->validator = $this->prophecy->prophesize(ValidatorInterface::class);
        $this->serializer = $this->prophecy->prophesize(SerializerInterface::class);
        $this->entityManager = $this->prophecy->prophesize(EntityManagerInterface::class);
        /**
         * Note for reviewers: It seems Prophecy has issues dealing with mocks returning mocks
         * Because of this, for this single class I'm using PhpUnit's mocks (which are far inferior, but get the job done)
         * See issue: https://github.com/phpspec/prophecy/issues/535
         */
        $this->repository = $this->createMock(TodoRepository::class);

        $this->entityManager->getRepository(Todo::class)->willReturn($this->repository);

        $this->controller = new TodoController(
          $this->entityManager->reveal(),
          $this->serializer->reveal(),
          $this->validator->reveal()
        );
    }

    public function testItReturnsProperJsonResponseForListAll(): void {
        $this->repository->expects($this->any())
            ->method('findAll')
            ->willReturn([]);
        $this->serializer->serialize(Argument::exact([]), Argument::exact('json'), Argument::any())->willReturn('[]');

        $response = $this->controller->listAll();

        assertEquals('[]', $response->getContent());
        assertEquals(200, $response->getStatusCode());
    }

    public function testItCreatesSuccessfully(): void {
        $this->serializer->deserialize(Argument::cetera())->willReturn(new Todo());
        $validations = new ConstraintViolationList();
        $this->validator->validate(Argument::cetera())->willReturn($validations);
        $this->entityManager->persist(Argument::cetera())->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $response = $this->controller->create(new Request());

        assertEquals('', $response->getContent());
        assertEquals(200, $response->getStatusCode());

        $this->prophecy->checkPredictions();
    }

    public function testItFailsWhenValidationFailsOnCreation(): void {
        $this->serializer->deserialize(Argument::cetera())->willReturn(new Todo());
        $violation = new ConstraintViolation('Fail', null, [], '1', 'some.path', '1');
        $validations = new ConstraintViolationList([$violation]);
        $this->validator->validate(Argument::cetera())->willReturn($validations);
        $this->entityManager->persist(Argument::cetera())->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->controller->create(new Request());
    }

    public function testItReturnsProperJsonResponseForList(): void {
        $todo = new Todo();
        $this->repository->expects($this->any())
            ->method('find')
            ->willReturn($todo);
        $this->serializer->serialize(Argument::exact($todo), Argument::exact('json'), Argument::any())->willReturn('{\'someProp\':\'1\'}');

        $response = $this->controller->list(2);

        assertEquals('{\'someProp\':\'1\'}', $response->getContent());
        assertEquals(200, $response->getStatusCode());
    }

    public function testItThrowsErrorWhenTodoNotFoundWhenList(): void {
        $this->repository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->controller->list(2);
    }

    public function testItCallsDeleteProperly(): void {
        $todo = new Todo();
        $this->repository->expects($this->any())
            ->method('find')
            ->willReturn($todo);

        $this->entityManager->remove($todo)->shouldBeCalled();
        $this->entityManager->flush()->shouldBeCalled();

        $response = $this->controller->delete(2);

        assertEquals(200, $response->getStatusCode());
        $this->prophecy->checkPredictions();
    }

}