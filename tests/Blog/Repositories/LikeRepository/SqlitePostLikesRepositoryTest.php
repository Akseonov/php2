<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\LikeRepository;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\PostLike;
use Akseonov\Php2\Blog\Repositories\LikesRepository\SqlitePostLikesRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostLikesRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetchAll')->willReturn([]);
        $connectionMock->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostLikesRepository($connectionMock);

        $this->expectException(LikesPostNotFoundException::class);
        $this->expectExceptionMessage('Cannot get likes for post: 123e4567-e89b-12d3-a456-426614174000');

        $repository->getByPostUuid(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }

    /**
     * @throws InvalidArgumentException
     * @throws LikesPostNotFoundException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function testItReturnCommentObjectByUuid(): void
    {
        $connectionMock = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->at(0))
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
            ]);

        $statementMock
            ->expects($this->at(2))
            ->method('execute')
            ->with([
                ':uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
            ]);

        $statementMock
            ->expects($this->at(4))
            ->method('execute')
            ->with([
                ':uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
            ]);

        $statementMock
            ->expects($this->at(6))
            ->method('execute')
            ->with([
                ':uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
            ]);

        $connectionMock->method('prepare')->willReturn($statementMock);

        $statementMock
            ->expects($this->at(1))
            ->method('fetchAll')
            ->willReturn([
                [
                    'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                    'post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                    'user_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                ]
            ]);

        $statementMock
            ->expects($this->at(3))
            ->method('fetch')
            ->willReturn([
                'uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'username' => 'admin',
                'first_name' => 'Peter',
                'last_name' => 'Romanov',
            ]);

        $statementMock
            ->expects($this->at(5))
            ->method('fetch')
            ->willReturn([
                'uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                'author_uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'title' => 'мой рандомный заголовок',
                'text' => 'мой рандомный текст',
            ]);

        $statementMock
            ->expects($this->at(7))
            ->method('fetch')
            ->willReturn([
                'uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'username' => 'ivan',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin'
            ]);

        $repository = new SqlitePostLikesRepository($connectionMock);

        $result = $repository->getByPostUuid(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $userLike = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'admin',
            new Name(
                'Peter',
                'Romanov'
            )
        );

        $userPost = new User(
            new UUID('6159f29f-9f6d-4b01-a022-cb0519a11ddd'),
            'ivan',
            new Name(
                'Ivan',
                'Nikitin'
            )
        );

        $post = new Post(
            new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
            $userPost,
            'мой рандомный заголовок',
            'мой рандомный текст'
        );

        $this->assertEquals([
            new PostLike(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $post,
                $userLike,
            )
        ], $result);

        $resultString = [];

        foreach ($result as $like) {
            $resultString[] = (string)$like;
        }

        $this->assertEquals(
            [
                'admin поставил лайк: мой рандомный заголовок',
            ],
            $resultString
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
                ':user_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostLikesRepository($connectionStub);

        $userLike = new User(
            new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
            'usernameComment',
            new Name('firstNameComment', 'lastNameComment')
        );

        $userPost = new User(
            new UUID('6159f29f-9f6d-4b01-a022-cb0519a11ddd'),
            'usernamePost',
            new Name('firstNamePost', 'lastNamePost')
        );

        $post = new Post(
            new UUID('b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481'),
            $userPost,
            'мой рандомный заголовок',
            'мой рандомный текст поста'
        );

        $repository->save(
            new PostLike(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $post,
                $userLike,
            )
        );
    }
}