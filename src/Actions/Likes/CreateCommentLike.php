<?php

namespace Akseonov\Php2\Actions\Likes;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\CommentLike;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\CommentNotFoundException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;

class CreateCommentLike implements ActionInterface
{
    public function __construct(
        private readonly CommentLikesRepositoryInterface $commentLikesRepository,
        private readonly CommentsRepositoryInterface $commentsRepository,
        private readonly UsersRepositoryInterface $usersRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $userUuid = new UUID($request->jsonBodyField('user_uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $commentUuid = new UUID($request->jsonBodyField('comment_uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $user = $this->usersRepository->get($userUuid);
        } catch (UserNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $comment = $this->commentsRepository->get($commentUuid);
        } catch (CommentNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $newCommentLikeUuid = UUID::random();

        $commentLike = new CommentLike(
            $newCommentLikeUuid,
            $comment,
            $user
        );

        $this->commentLikesRepository->save($commentLike);

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentLikeUuid,
        ]);
    }
}