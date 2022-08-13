<?php

namespace Akseonov\Php2\Blog\Repositories\CommentsRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private \PDO $connection,
    )
    {
    }

    public function save(Comment $comment): void
    {
        // Подготавливаем запрос
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, author_uuid, post_uuid, text)
            VALUES (:uuid, :author_uuid, :post_uuid, :text)'
        );
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            ':uuid' => (string)$comment->getUuid(),
            ':author_uuid' => (string)$comment->getAuthorUuid(),
            ':post_uuid' => (string)$comment->getPostUuid(),
            ':text' => $comment->getText(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
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

        // Бросаем исключение, если пользователь не найден
        if ($result === false) {
            throw new CommentNotFoundException(
                "Cannot get comment: $uuid"
            );
        }

        return new Comment(
            new UUID($result['uuid']),
            new UUID($result['author_uuid']),
            new UUID($result['post_uuid']),
            $result['text']
        );
    }
}