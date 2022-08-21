<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\UserRepository;

use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteUsersRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testItThrowsAnExceptionWhenUserNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqliteUsersRepository($connectionMock);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Cannot get user: Ivan');

        $repository->getByUsername('Ivan');
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function testItReturnUserObjectByUsername(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':username' => 'ivan123',
            ]);

        $connectionMock->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'username' => 'ivan123',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]);

        $repository = new SqliteUsersRepository($connectionMock);

        $result = $repository->getByUsername('ivan123');

        $this->assertEquals(new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            new Name('Ivan', 'Nikitin')
        ), $result);

        $this->assertEquals(
            'Юзер 123e4567-e89b-12d3-a456-426614174000 с именем Ivan Nikitin и логином ivan123.' . PHP_EOL,
            (string)$result
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function testItReturnUserObjectByUuid(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
            ]);

        $connectionMock->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'username' => 'ivan123',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]);

        $repository = new SqliteUsersRepository($connectionMock);

        $result = $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $this->assertEquals(new User(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            'ivan123',
            new Name('Ivan', 'Nikitin')
        ), $result);

        $this->assertEquals(
            'Юзер 123e4567-e89b-12d3-a456-426614174000 с именем Ivan Nikitin и логином ivan123.' . PHP_EOL,
            (string)$result
        );
    }

    public function testItSavesUserToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':username' => 'ivan123',
                ':first_name' => 'Ivan',
                ':last_name' => 'Nikitin',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteUsersRepository($connectionStub);

        $repository->save(
            new User(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                'ivan123',
                new Name('Ivan', 'Nikitin')
            )
        );
    }
}