<?php

namespace Akseonov\Php2\Blog\Repositories\CommentsRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use PDO;
use Psr\Log\LoggerInterface;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private readonly PDO             $connection,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function save(Comment $comment): void
    {
        $this->logger->info('Start save comment');

        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, author_uuid, post_uuid, text)
            VALUES (:uuid, :author_uuid, :post_uuid, :text)'
        );
        $statement->execute([
            ':uuid' => $comment->getUuid(),
            ':author_uuid' => $comment->getUser()->getUuid(),
            ':post_uuid' => $comment->getPost()->getUuid(),
            ':text' => $comment->getText(),
        ]);

        $this->logger->info("Finish save comment: {$comment->getUuid()}");
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $this->logger->info('Start get comment by uuid');

        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("Cannot get comment: $uuid");
            throw new CommentNotFoundException(
                "Cannot get comment: $uuid"
            );
        }

        $postRepository = new SqlitePostsRepository($this->connection, $this->logger);
        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);

        $post = $postRepository->get(new UUID($result['post_uuid']));
        $user = $userRepository->get(new UUID($result['author_uuid']));

        $this->logger->info("Finish get comment by uuid: {$result['uuid']}");

        return new Comment(
            new UUID($result['uuid']),
            $post,
            $user,
            $result['text']
        );
    }
}