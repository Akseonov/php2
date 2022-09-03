<?php

namespace Akseonov\Php2\http\Actions\Posts;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class DeletePost implements ActionInterface
{
    public function __construct(
        private readonly PostsRepositoryInterface $postsRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('DeletePost action start');

        try {
            $uuid = new UUID($request->jsonBodyField('uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->postsRepository->get($uuid);
        } catch (PostNotFoundException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->postsRepository->delete($uuid);
        $this->logger->info("DeletePost action delete post: $uuid");

        return new SuccessfulResponse([
            'uuid' => (string)$uuid,
        ]);
    }
}