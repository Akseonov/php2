<?php

namespace Akseonov\Php2\Blog\Repositories\CommentsRepository;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Blog\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Person\Name;

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
            ':author_uuid' => (string)$comment->getUser()->getUuid(),
            ':post_uuid' => (string)$comment->getPost()->getUuid(),
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
            'SELECT comments.uuid as comments_uuid,
                        comments.author_uuid as comments_author_uuid, 
                        comments.post_uuid as comments_post_uuid,
                        comments.text as comments_text,
                        posts.uuid as posts_uuid,
                        posts.author_uuid as posts_author_uuid,
                        posts.title as posts_title,
                        posts.text as posts_text,
                        users1.uuid as comment_user_uuid,
                        users1.username as comment_user_username,
                        users1.first_name as comment_user_first_name,
                        users1.last_name as comment_user_last_name,
                        users2.uuid as post_user_uuid,
                        users2.username as post_user_username,
                        users2.first_name as post_user_first_name,
                        users2.last_name as post_user_last_name
                    FROM comments
                    LEFT JOIN posts
                    ON comments.post_uuid = posts.uuid
                    LEFT JOIN users as users1
                    ON comments.author_uuid = users1.uuid
                    LEFT JOIN users as users2
                    ON posts.author_uuid = users2.uuid
                    WHERE comments.uuid = :uuid'
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

        $userComment = new User(
            new UUID($result['comments_author_uuid']),
            $result['comment_user_username'],
            new Name(
                $result['comment_user_first_name'],
                $result['comment_user_last_name']
            )
        );

        $userPost = new User(
            new UUID($result['posts_author_uuid']),
            $result['post_user_username'],
            new Name(
                $result['post_user_first_name'],
                $result['post_user_last_name']
            )
        );

        $post = new Post(
            new UUID($result['posts_uuid']),
            $userPost,
            $result['posts_title'],
            $result['posts_text']
        );

        return new Comment(
            new UUID($result['comments_uuid']),
            $post,
            $userComment,
            $result['comments_text']
        );
    }
}