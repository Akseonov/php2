<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\PostRepository;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionMock, new DummyLogger());

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Cannot get post: Мой дом');

        $repository->getByTitle('Мой дом');
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function testItReturnPostObjectByTitle(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive([
                [
                    ':title' => 'Мой дом',
                ]
            ], [
                [
                    ':uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ]
            ]);

        $connectionMock->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'title' => 'Мой дом',
                'text' => 'Это мой рандомнй текст',
            ], [
                'uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'username' => 'username',
                'password' => '12345',
                'first_name' => 'firstname',
                'last_name' => 'lastname',
            ]);

        $repository = new SqlitePostsRepository($connectionMock, new DummyLogger());

        $result = $repository->getByTitle('Мой дом');

        $user = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'username',
            '12345',
            new Name('firstname', 'lastname')
        );

        $this->assertEquals(new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            $user,
            'Мой дом',
            'Это мой рандомнй текст'
        ), $result);

        $this->assertEquals(
            'username пишет: Мой дом' . PHP_EOL . 'Это мой рандомнй текст',
            (string)$result
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function testItReturnPostObjectByUuid(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive([
                [
                    ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ]
            ], [
                [
                    ':uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ]
            ]);

        $connectionMock->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->exactly(2))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'title' => 'Мой дом',
                'text' => 'Это мой рандомнй текст',
            ], [
                'uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'username' => 'username',
                'password' => '12345',
                'first_name' => 'firstname',
                'last_name' => 'lastname',
            ]);

        $repository = new SqlitePostsRepository($connectionMock, new DummyLogger());

        $result = $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $user = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'username',
            '12345',
            new Name('firstname', 'lastname')
        );

        $this->assertEquals(new Post(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            $user,
            'Мой дом',
            'Это мой рандомнй текст'
        ), $result);

        $this->assertEquals(
            'username пишет: Мой дом' . PHP_EOL . 'Это мой рандомнй текст',
            (string)$result
        );
    }

    public function testItSavesPostToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ':title' => 'Мой дом',
                ':text' => 'Это мой рандомнй текст',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

        $user = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'username',
            '12345',
            new Name('firstName', 'lastName')
        );

        $repository->save(
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $user,
                'Мой дом',
                'Это мой рандомнй текст'
            )
        );
    }

    public function testItDeletePostFromDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsRepository($connectionStub, new DummyLogger());

        $repository->delete(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74')
        );
    }
}