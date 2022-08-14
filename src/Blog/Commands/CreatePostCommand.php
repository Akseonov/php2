<?php

namespace Akseonov\Php2\Blog\Commands;

use Akseonov\Php2\Blog\Exceptions\ArgumentsException;
use Akseonov\Php2\Blog\Exceptions\CommandException;
use Akseonov\Php2\Blog\Exceptions\PostNotFoundException;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;

class CreatePostCommand
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository
    )
    {

    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     */
    public function handle(Arguments $arguments): void
    {
        $title = $arguments->get('title');

        // Проверяем, существует ли пользователь в репозитории
        if ($this->postExists($title)) {
            // Бросаем исключение, если пользователь уже существует
            throw new CommandException("Post already exists: $title");
        }
        // Сохраняем пользователя в репозиторий
        $this->postsRepository->save(new Post(
            UUID::random(),
            UUID::random(),
            $arguments->get('title'),
            $arguments->get('text')
        ));
    }

    private function postExists(string $title): bool
    {
        try {
            // Пытаемся получить пользователя из репозитория
            $this->postsRepository->getByTitle($title);
        } catch (PostNotFoundException) {
            return false;
        }
        return true;
    }
}