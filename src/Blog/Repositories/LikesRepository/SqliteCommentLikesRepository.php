<?php

namespace Akseonov\Php2\Blog\Repositories\LikesRepository;

use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteCommentLikesRepository implements CommentLikesRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
        private readonly LoggerInterface $logger,
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
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($result)) {
            $this->logger->warning("Cannot get likes for post: $likesInfo");
            throw new LikesCommentNotFoundException(
                "Cannot get likes for comment: $likesInfo"
            );
        }

        $userRepository = new SqliteUsersRepository(
            $this->connection,
            $this->logger,
        );

        $commentRepository = new SqliteCommentsRepository(
            $this->connection,
            $this->logger,
        );

        $this->logger->info("Finish get comment likes");

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
        $this->logger->info('Start save comment like');

        $statement = $this->connection->prepare(
            'INSERT INTO likes_comment (uuid, comment_uuid, user_uuid)
            VALUES (:uuid, :comment_uuid, :user_uuid)'
        );
        $statement->execute([
            ':uuid' => $commentLike->getUuid(),
            ':comment_uuid' => $commentLike->getComment()->getUuid(),
            ':user_uuid' => $commentLike->getUser()->getUuid(),
        ]);

        $this->logger->info("Finish save comment like: {$commentLike->getUuid()}");
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
        $this->logger->info('Start get comment likes by comment uuid');

        $statementLikes = $this->connection->prepare(
            'SELECT * FROM likes_comment WHERE likes_comment.comment_uuid = :uuid'
        );

        $statementLikes->execute([
            ':uuid' => (string)$uuid,
        ]);

        return $this->getLikes($statementLikes, $uuid);
    }
}