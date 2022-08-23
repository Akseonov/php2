<?php

namespace Akseonov\Php2\Blog\Repositories\LikesRepository;

use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use PDOStatement;

class SqliteCommentLikesRepository implements CommentLikesRepositoryInterface
{
    public function __construct(
        private readonly \PDO $connection,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     * @throws UserNotFoundException
     * @throws PostNotFoundException
     * @throws LikesCommentNotFoundException
     */
    private function getLikes(PDOStatement $statement, string $likesInfo): array
    {
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            throw new LikesCommentNotFoundException(
                "Cannot get likes for comment: $likesInfo"
            );
        }

        $userRepository = new SqliteUsersRepository(
            $this->connection
        );

        $commentRepository = new SqliteCommentsRepository(
            $this->connection
        );

        return array_map(function($like) use($userRepository, $commentRepository): CommentLike
        {
            $userResult = $userRepository->get(new UUID($like['user_uuid']));
            $commentResult = $commentRepository->get(new UUID($like['comment_uuid']));

            return new CommentLike(
                new UUID($like['uuid']),
                $commentResult,
                $userResult,
            );
        }, $result);
    }

    public function save(CommentLike $commentLike): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes_comment (uuid, comment_uuid, user_uuid)
            VALUES (:uuid, :comment_uuid, :user_uuid)'
        );
        $statement->execute([
            ':uuid' => $commentLike->getUuid(),
            ':comment_uuid' => $commentLike->getComment()->getUuid(),
            ':user_uuid' => $commentLike->getUser()->getUuid(),
        ]);
    }

    /**
     * @throws CommentNotFoundException
     * @throws InvalidArgumentException
     * @throws LikesCommentNotFoundException
     * @throws UserNotFoundException
     * @throws PostNotFoundException
     */
    public function getByCommentUuid(UUID $uuid): array
    {
        $statementLikes = $this->connection->prepare(
            'SELECT * FROM likes_comment WHERE likes_comment.comment_uuid = :uuid'
        );

        $statementLikes->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getLikes($statementLikes, $uuid);
    }
}