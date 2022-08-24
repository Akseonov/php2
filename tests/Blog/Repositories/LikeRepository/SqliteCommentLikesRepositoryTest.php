<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\LikeRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\LikesRepository\SqliteCommentLikesRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use Akseonov\Php2\UnitTests\DummyLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentLikesRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws CommentNotFoundException
     */
    public function testItThrowsAnExceptionWhenCommentLikesNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetchAll')->willReturn([]);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentLikesRepository($connectionMock, new DummyLogger());

        $this->expectException(LikesCommentNotFoundException::class);
        $this->expectExceptionMessage('Cannot get likes for comment: 123e4567-e89b-12d3-a456-426614174000');

        $repository->getByCommentUuid(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws LikesCommentNotFoundException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws CommentNotFoundException
     */
    public function testItReturnCommentLikesArrayByUuid(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->exactly(6))
            ->method('execute')
            ->withConsecutive([
                [
                    ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ]
            ], [
                [
                    ':uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ]
            ], [
                [
                    ':uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                ]
            ], [
                [
                    ':uuid' => '621d15cb-b267-45ad-be5b-9f8e393bde46',
                ]
            ], [
                [
                    ':uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                ]
            ], [
                [
                    ':uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                ]
            ]);

        $connectionMock->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->exactly(1))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls([
                [
                    'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                    'comment_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                    'user_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ]
            ]);

        $statementMock
            ->expects($this->exactly(5))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls([
                'uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'username' => 'admin',
                'first_name' => 'Peter',
                'last_name' => 'Romanov',
            ], [
                'uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                'post_uuid' => '621d15cb-b267-45ad-be5b-9f8e393bde46',
                'author_uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'text' => 'мой рандомный текст',
            ], [
                'uuid' => '621d15cb-b267-45ad-be5b-9f8e393bde46',
                'author_uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'title' => 'мой рандомнй заголовок',
                'text' => 'мой рандомный текст',
            ], [
                'uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'username' => 'username',
                'first_name' => 'firstName',
                'last_name' => 'lastName',
            ], [
                'uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'username' => 'ivan',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin'
            ]);

        $repository = new SqliteCommentLikesRepository($connectionMock, new DummyLogger());

        $result = $repository->getByCommentUuid(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $userLike = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'admin',
            new Name(
                'Peter',
                'Romanov'
            )
        );

        $userComment = new User(
            new UUID('6159f29f-9f6d-4b01-a022-cb0519a11ddd'),
            'ivan',
            new Name(
                'Ivan',
                'Nikitin'
            )
        );

        $userPost = new User(
            new UUID('6159f29f-9f6d-4b01-a022-cb0519a11ddd'),
            'username',
            new Name('firstName', 'lastName')
        );

        $postComment = new Post(
            new UUID('621d15cb-b267-45ad-be5b-9f8e393bde46'),
            $userPost,
            'мой рандомнй заголовок',
            'мой рандомный текст'
        );

        $comment = new Comment(
            new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
            $postComment,
            $userComment,
            'мой рандомный текст',
        );

        $this->assertEquals([
            new CommentLike(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $comment,
                $userLike,
            )
        ], $result);

        $resultString = [];

        foreach ($result as $like) {
            $resultString[] = (string)$like;
        }

        $this->assertEquals(
            [
                'admin поставил лайк: мой рандомный текст',
            ],
            $resultString
        );
    }

    public function testItSavesCommentLikeToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':comment_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                ':user_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentLikesRepository($connectionStub, new DummyLogger());

        $userLike = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'admin',
            new Name(
                'Peter',
                'Romanov'
            )
        );

        $userComment = new User(
            new UUID('6159f29f-9f6d-4b01-a022-cb0519a11ddd'),
            'ivan',
            new Name(
                'Ivan',
                'Nikitin'
            )
        );

        $userPost = new User(
            new UUID('6159f29f-9f6d-4b01-a022-cb0519a11ddd'),
            'username',
            new Name('firstName', 'lastName')
        );

        $postComment = new Post(
            new UUID('621d15cb-b267-45ad-be5b-9f8e393bde46'),
            $userPost,
            'мой рандомнй заголовок',
            'мой рандомный текст'
        );

        $comment = new Comment(
            new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
            $postComment,
            $userComment,
            'мой рандомный текст',
        );

        $repository->save(
            new CommentLike(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $comment,
                $userLike,
            )
        );
    }
}