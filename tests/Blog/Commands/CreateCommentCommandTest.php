<?php

namespace Akseonov\Php2\UnitTests\Blog\Commands;

use Akseonov\Php2\Blog\Commands\Arguments;
use Akseonov\Php2\Blog\Commands\CreateCommentCommand;
use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\ArgumentsException;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Person\Name;
use PHPUnit\Framework\TestCase;

class CreateCommentCommandTest extends TestCase
{
    private function makeCommentRepositoryWithNotFoundException(): CommentsRepositoryInterface
    {
        return new class implements CommentsRepositoryInterface
        {
            public function save(Comment $comment): void
            {

            }

            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException("Not found");
            }
        };
    }

    private function makePostRepositoryWithPostObjectInReturn(): PostsRepositoryInterface
    {
        return new class implements PostsRepositoryInterface
        {
            public function save(Post $post): void
            {

            }

            public function get(UUID $uuid): Post
            {
                $user = new User(
                    new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                    'username',
                    new Name('firstname', 'lastname')
                );

                return new Post(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    $user,
                    'Мой дом',
                    'Это мой рандомнй текст'
                );
            }

            public function getByTitle(string $title): Post
            {
                $user = new User(
                    new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                    'username',
                    new Name('firstname', 'lastname')
                );

                return new Post(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    $user,
                    'Мой дом',
                    'Это мой рандомнй текст'
                );
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    private function makeUserRepositoryWithUserObjectInReturn(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface
        {
            public function save(User $user): void
            {

            }

            public function get(UUID $uuid): User
            {
                return new User(
                    new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                    'username',
                    new Name('firstname', 'lastname')
                );
            }

            public function getByUsername(string $title): User
            {
                return new User(
                    new UUID('9de6281b-6fa3-427b-b071-4ca519586e74'),
                    'username',
                    new Name('firstname', 'lastname')
                );
            }
        };
    }

    private function makeCommentRepositoryWithCommentObjectInReturn(): CommentsRepositoryInterface
    {
        return new class implements CommentsRepositoryInterface
        {
            public function save(Comment $comment): void
            {

            }

            public function get(UUID $uuid): Comment
            {
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

                return new Comment(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    $post,
                    $userComment,
                    'Это мой рандомнй коммент'
                );
            }
        };
    }

    public function testItRequiresText(): void
    {
        $command = new CreateCommentCommand(
            [
                'comments_repository' => $this->makeCommentRepositoryWithNotFoundException(),
                'posts_repository' => $this->makePostRepositoryWithPostObjectInReturn(),
                'users_repository' => $this->makeUserRepositoryWithUserObjectInReturn()
            ]
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: text');

        $command->handle(new Arguments([
            'post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
            'author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74'
        ]));
    }

    public function testItRequiresAuthorUuid(): void
    {
        $command = new CreateCommentCommand(
            [
                'comments_repository' => $this->makeCommentRepositoryWithNotFoundException(),
                'posts_repository' => $this->makePostRepositoryWithPostObjectInReturn(),
                'users_repository' => $this->makeUserRepositoryWithUserObjectInReturn()
            ]
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: author_uuid');

        $command->handle(new Arguments([
            'post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
        ]));
    }

    public function testItRequiresPostUuid(): void
    {
        $command = new CreateCommentCommand(
            [
                'comments_repository' => $this->makeCommentRepositoryWithNotFoundException(),
                'posts_repository' => $this->makePostRepositoryWithPostObjectInReturn(),
                'users_repository' => $this->makeUserRepositoryWithUserObjectInReturn()
            ]
        );

        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: post_uuid');

        $command->handle(new Arguments([

        ]));
    }

    public function testItSavesCommentToRepository(): void
    {
        $commentsRepository = new class implements CommentsRepositoryInterface {

            private bool $called = false;

            public function save(Comment $comment): void
            {
                $this->called = true;
            }
            public function get(UUID $uuid): Comment
            {
                throw new CommentNotFoundException("Not found");
            }

            public function wasCalled(): bool
            {
                return $this->called;
            }
        };

        $command = new CreateCommentCommand([
            'comments_repository' => $commentsRepository,
            'posts_repository' => $this->makePostRepositoryWithPostObjectInReturn(),
            'users_repository' => $this->makeUserRepositoryWithUserObjectInReturn()
        ]);

        $command->handle(new Arguments([
            'post_uuid' => 'b6d3c43b-d7ff-4b3c-95d4-f9afccf0c481',
            'author_uuid' => '9de6281b-6fa3-427b-b071-4ca519586e74',
            'text' => 'Это мой рандомнй коммент',
        ]));

        $this->assertTrue($commentsRepository->wasCalled());
    }
}