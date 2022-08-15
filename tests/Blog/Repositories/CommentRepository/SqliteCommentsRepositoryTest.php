<?php

namespace Akseonov\Php2\UnitTests\Blog\Repositories\CommentRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Person\Name;
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
                'comments_uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'comments_post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                'comments_author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'comments_text' => 'Это мой рандомнй коммент',
                'posts_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
                'posts_author_uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'posts_title' => 'мой рандомный заголовок',
                'posts_text' => 'мой рандомный текст',
                'comment_user_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
                'comment_user_username' => 'admin',
                'comment_user_first_name' => 'Peter',
                'comment_user_last_name' => 'Romanov',
                'post_user_uuid' => '6159f29f-9f6d-4b01-a022-cb0519a11ddd',
                'post_user_username' => 'ivan',
                'post_user_first_name' => 'Ivan',
                'post_user_last_name' => 'Nikitin'
            ]);

        $repository = new SqliteCommentsRepository($connectionMock);

        $result = $repository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $userComment = new User(
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

        $this->assertEquals(new Comment(
            new UUID('123e4567-e89b-12d3-a456-426614174000'),
            $post,
            $userComment,
            'Это мой рандомнй коммент'
        ), $result);

        $this->assertEquals(
            'admin пишет комментарий: Это мой рандомнй коммент' . PHP_EOL,
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

        $userComment = new User(
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
            new Comment(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $post,
                $userComment,
                'Это мой рандомнй коммент'
            )
        );
    }
}