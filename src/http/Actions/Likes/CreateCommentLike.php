<?php

namespace Akseonov\Php2\http\Actions\Likes;

use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesCommentNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreateCommentLike implements ActionInterface
{
    public function __construct(
        private readonly CommentLikesRepositoryInterface $commentLikesRepository,
        private readonly CommentsRepositoryInterface $commentsRepository,
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('CreateCommentLike action start');

        try {
            $userUuid = new UUID($request->jsonBodyField('user_uuid'));
            $commentUuid = new UUID($request->jsonBodyField('comment_uuid'));

            if ($this->commentLikeExist($userUuid, $commentUuid)) {
                $this->logger->warning("Post like already exists: $userUuid, $commentUuid");
                throw new HttpException("Post like already exists: $userUuid, $commentUuid");
            }
        } catch (HttpException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->usersRepository->get($userUuid);
            $comment = $this->commentsRepository->get($commentUuid);
        } catch (UserNotFoundException | CommentNotFoundException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $newCommentLikeUuid = UUID::random();

        $commentLike = new CommentLike(
            $newCommentLikeUuid,
            $comment,
            $user
        );

        $this->commentLikesRepository->save($commentLike);
        $this->logger->info("CreateCommentLike action create like for comment: $newCommentLikeUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentLikeUuid,
        ]);
    }

    private function commentLikeExist(UUID $userUuid, UUID $commentUuid): bool
    {
        try {
            $postLikes = $this->commentLikesRepository->getByCommentUuid($commentUuid);
        } catch (LikesCommentNotFoundException) {
            return false;
        }

        foreach ($postLikes as $like) {
            if ((string)$like->getUser()->getUuid() === (string)$userUuid) {
                return true;
            }
        }

        return false;
    }
}