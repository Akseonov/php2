<?php

namespace Akseonov\Php2\Blog\Repositories\PostsRepository;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
        private readonly \PDO $connection,
    )
    {
    }

    /**
     * @throws InvalidArgumentException|PostNotFoundException
     * @throws UserNotFoundException
     */
    private function getPost(\PDOStatement $statement, string $postInfo): Post
    {
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot get post: $postInfo"
            );
        }

        $userRepository = new SqliteUsersRepository($this->connection);

        $user = $userRepository->get(new UUID($result['author_uuid']));

        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text']
        );
    }

    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author_uuid, title, text)
            VALUES (:uuid, :author_uuid, :title, :text)'
        );

        $statement->execute([
            ':uuid' => (string)$post->getUuid(),
            ':author_uuid' => (string)$post->getUser()->getUuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
        );

        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getPost($statement, $uuid);
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
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

    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE posts.uuid=:uuid;'
        );

        $statement->execute([
            ':uuid' => $uuid,
        ]);
    }
}