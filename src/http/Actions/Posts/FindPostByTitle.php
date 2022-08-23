<?php

namespace Akseonov\Php2\http\Actions\Posts;

use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindPostByTitle implements ActionInterface
{
    public function __construct(
        private readonly PostsRepositoryInterface $postsRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function handle(Request $request): Response
    {
        $this->logger->info('FindPostByTitle action start');

        try {
            $title = $request->query('title');
        } catch (HttpException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $post = $this->postsRepository->getByTitle($title);
        } catch (PostNotFoundException $exception) {
            $this->logger->warning($exception->getMessage());
            return new ErrorResponse($exception->getMessage());
        }

        $this->logger->info("FindPostByTitle action find post: $title");

        return new SuccessfulResponse([
            'uuid' => $post->getUuid(),
            'user' => [
                'uuid' => $post->getUser()->getUuid(),
                'username' => $post->getUser()->getUsername(),
                'first_name' => $post->getUser()->getName()->getFirstName(),
                'last_name' => $post->getUser()->getName()->getLastName(),
            ],
            'title' => $post->getTitle(),
            'text' => $post->getText(),
        ]);
    }
}