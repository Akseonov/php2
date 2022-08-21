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

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private readonly \PDO $connection,
    )
    {
    }

    public function save(Comment $comment): void
    {
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
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new CommentNotFoundException(
                "Cannot get comment: $uuid"
            );
        }

        $postRepository = new SqlitePostsRepository($this->connection);
        $userRepository = new SqliteUsersRepository($this->connection);

        $post = $postRepository->get(new UUID($result['post_uuid']));
        $user = $userRepository->get(new UUID($result['author_uuid']));

        return new Comment(
            new UUID($result['uuid']),
            $post,
            $user,
            $result['text']
        );
    }
}