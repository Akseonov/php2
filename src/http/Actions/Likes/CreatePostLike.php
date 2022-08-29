<?php

namespace Akseonov\Php2\http\Actions\Likes;

use Akseonov\Php2\Blog\PostLike;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostLikesRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\UsersRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\LikesPostNotFoundException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\Exceptions\UserNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\Auth\Interfaces\TokenAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreatePostLike implements ActionInterface
{
    public function __construct(
        private readonly PostLikesRepositoryInterface $postLikesRepository,
        private readonly PostsRepositoryInterface $postsRepository,
//        private readonly UsersRepositoryInterface $usersRepository,
        private readonly TokenAuthenticationInterface $authentication,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('CreatePostLike action start');

        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));

            if ($this->postLikeExist(new UUID($user->getUuid()), $postUuid)) {
                $this->logger->warning("Post like already exists: {$user->getUuid()}, $postUuid");
                throw new HttpException("Post like already exists: {$user->getUuid()}, $postUuid");
            }

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

        $newPostLikeUuid = UUID::random();

        $postLike = new PostLike(
            $newPostLikeUuid,
            $post,
            $user
        );

        $this->postLikesRepository->save($postLike);
        $this->logger->info("CreatePostLike action create like for post: $newPostLikeUuid");

        return new SuccessfulResponse([
            'uuid' => (string)$newPostLikeUuid,
        ]);
    }

    private function postLikeExist(UUID $userUuid, UUID $postUuid): bool
    {
        try {
            $postLikes = $this->postLikesRepository->getByPostUuid($postUuid);
        } catch (LikesPostNotFoundException) {
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