<?php

namespace Akseonov\Php2\Actions\Posts;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\InvalidArgumentException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\SuccessfulResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;

class DeletePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $uuid = new UUID($request->jsonBodyField('uuid'));
        } catch (HttpException | InvalidArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->postsRepository->get($uuid);
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $this->postsRepository->delete($uuid);

        return new SuccessfulResponse([
            'uuid' => (string)$uuid,
        ]);
    }
}