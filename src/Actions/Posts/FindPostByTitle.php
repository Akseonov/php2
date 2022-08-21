<?php

namespace Akseonov\Php2\Actions\Posts;

use Akseonov\Php2\Actions\ActionInterface;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\PostsRepositoryInterface;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\PostNotFoundException;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;

class FindPostByTitle implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $title = $request->query('title');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $post = $this->postsRepository->getByTitle($title);
        } catch (PostNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

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