<?php

namespace Akseonov\Php2\http\Actions\Auth;

use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\Exceptions\AuthTokenNotFoundException;
use Akseonov\Php2\Exceptions\AuthTokensRepositoryException;
use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\Auth\Interfaces\PasswordAuthenticationInterface;
use Akseonov\Php2\http\Auth\Interfaces\TokenAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use DateTimeImmutable;

class LogOut implements ActionInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        private readonly AuthTokensRepositoryInterface $authTokensRepository,
    )
    {
    }

    /**
     * @throws AuthException
     * @throws AuthTokensRepositoryException
     */
    public function handle(Request $request): Response
    {
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            return new ErrorResponse("Malformed token: [$header]");
        }

        $token = mb_substr($header, strlen(self::HEADER_PREFIX));

        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $newAuthToken = new AuthToken(
            $authToken->getToken(),
            $authToken->getUserUuid(),
            new DateTimeImmutable()
        );

        $this->authTokensRepository->save($newAuthToken);

        return new SuccessfulResponse([
            'token' => $newAuthToken->getToken(),
        ]);
    }
}