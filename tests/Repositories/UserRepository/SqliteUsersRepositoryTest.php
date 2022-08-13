<?php

namespace Akseonov\Php2\Blog\UnitTests\Repositories\UserRepository;

use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Exceptions\UserNotFoundException;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
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