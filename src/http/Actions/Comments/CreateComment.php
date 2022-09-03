<?php

namespace Akseonov\Php2\http\Actions\Comments;

use Akseonov\Php2\Blog\Comment;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\CommentsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\Auth\Interfaces\TokenAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreateComment implements ActionInterface
{
    public function __construct(
        private readonly CommentsRepositoryInterface $commentsRepository,
        private readonly PostsRepositoryInterface $postsRepository,
        private readonly TokenAuthenticationInterface $authentication,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('CreateComment action start');

        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $post = $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $newCommentUuid = UUID::random();

        try {
            $comment = new Comment(
                $newCommentUuid,
                $post,
                $user,
                $request->jsonBodyField('text')
            );
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->commentsRepository->save($comment);
        $this->logger->info("CreatePost action save comment: $newCommentUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentUuid,
        ]);
    }
}