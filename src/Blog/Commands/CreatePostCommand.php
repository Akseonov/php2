<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\CommandException;
use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Exceptions\PostNotFoundException;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;

class CreatePostCommand
{
    public function __construct(
        private array $repositories,
    )
    {

    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     * @throws InvalidArgumentException
     */
    public function handle(Arguments $arguments): void
    {
        $title = $arguments->get('title');

        // Проверяем, существует ли пользователь в репозитории
        if ($this->postExists($title)) {
            // Бросаем исключение, если пользователь уже существует
            throw new CommandException("Post already exists: $title");
        }

        $user = $this->repositories['users_repository']->get(
            new UUID($arguments->get('author_uuid'))
        );

        // Сохраняем пользователя в репозиторий
        $this->repositories['posts_repository']->save(new Post(
            UUID::random(),
            $user,
            $arguments->get('title'),
            $arguments->get('text')
        ));
    }

    private function postExists(string $title): bool
    {
        try {
            // Пытаемся получить пользователя из репозитория
            $this->repositories['posts_repository']->getByTitle($title);
        } catch (PostNotFoundException) {
            return false;
        }
        return true;
    }
}