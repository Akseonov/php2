<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\CommentRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\UUID;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentsRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     */
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository($connectionMock);

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Cannot get comment: 123e4567-e89b-12d3-a456-426614174000');

        $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     */
    public function testItReturnCommentObjectByUuid(): void
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
                'post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                'author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'text' => 'Это мой рандомнй коммент',
            ]);

        $repository = new SqliteCommentsRepository($connectionMock);

        $result = $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $this->assertEquals(new Comment(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
            'Это мой рандомнй коммент'
        ), $result);

        $this->assertEquals(
            'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481 пишет комментарий: Это мой рандомнй коммент' . PHP_EOL,
            (string)$result
        );
    }

    public function testItSavesCommentToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                ':author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ':text' => 'Это мой рандомнй коммент',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository($connectionStub);

        $repository->save(
            new Comment(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
                new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                'Это мой рандомнй коммент'
            )
        );
    }
}