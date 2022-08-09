<?php

namespace Akseonov\Php2\Blog\Repositories\PostsRepository;

use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Exceptions\PostNotFoundException;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\UUID;

class SqlitePostsRepository implements PostRepositoryInterface
{
    public function __construct(
        private \PDO $connection,
    )
    {
    }

    /**
     * @throws InvalidArgumentException|PostNotFoundException
     */
    private function getPost(\PDOStatement $statement, string $title): Post
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot get post: $title"
            );
        }

        return new Post(
            new UUID($result['uuid']),
            new UUID($result['author_uuid']),
            $result['title'],
            $result['text']
        );
    }

    public function save(Post $post): void
    {
        // Подготавливаем запрос
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_uuid, title, text)
            VALUES (:uuid, :author_uuid, :title, :text)'
        );
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            ':uuid' => (string)$post->getUuid(),
            ':author_uuid' => (string)$post->getAuthorUuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        // Бросаем исключение, если пользователь не найден
        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot get post: $uuid"
            );
        }

        return new Post(
            new UUID($result['uuid']),
            new UUID($result['author_uuid']),
            $result['title'],
            $result['text']
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    public function getByTitle(string $title): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE title = :title'
        );

        $statement->execute([
            ':title' => $title,
        ]);
        return $this->getPost($statement, $title);
    }
}