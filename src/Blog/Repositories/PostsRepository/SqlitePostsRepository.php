<?php

namespace Akseonov\Php2\Blog\Repositories\PostsRepository;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
        private readonly PDO            $connection,
        private readonly LoggerInterface $logger,
    )
    {
    }

    /**
     * @throws InvalidArgumentException|PostNotFoundException
     * @throws UserNotFoundException
     */
    private function getPost(PDOStatement $statement, string $postInfo): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("Cannot get post: $postInfo");
            throw new PostNotFoundException(
                "Cannot get post: $postInfo"
            );
        }

        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);

        $user = $userRepository->get(new UUID($result['author_uuid']));

        $this->logger->info("Finish get post {$result['uuid']}");

        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text']
        );
    }

    public function save(Post $post): void
    {
        $this->logger->info('Start save post');

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

        $this->logger->info("Finish save post {$post->getUuid()}");
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): Post
    {
        $this->logger->info('Start get post by uuid');

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
        $this->logger->info('Start get post by title');

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
        $this->logger->info('Start delete post');

        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE posts.uuid=:uuid;'
        );

        $statement->execute([
            ':uuid' => $uuid,
        ]);

        $this->logger->info("Finish delete post: $uuid");
    }
}