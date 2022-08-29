<?php

namespace Akseonov\Php2\http\Actions\Auth;

use Akseonov\Php2\Blog\AuthToken;
use Akseonov\Php2\Blog\Repositories\RepositoryInterfaces\AuthTokensRepositoryInterface;
use Akseonov\Php2\Blog\UUID;
use Akseonov\Php2\Exceptions\AuthException;
use Akseonov\Php2\http\Actions\ActionInterface;
use Akseonov\Php2\http\Auth\Interfaces\PasswordAuthenticationInterface;
use Akseonov\Php2\http\ErrorResponse;
use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;
use Akseonov\Php2\http\SuccessfulResponse;
use DateTimeImmutable;
use Exception;

class LogIn implements ActionInterface
{
    public function __construct(
        private readonly PasswordAuthenticationInterface $passwordAuthentication,
        private readonly AuthTokensRepositoryInterface $authTokensRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(Request $request): Response
    {
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        $authToken = new AuthToken(
            bin2hex(random_bytes(40)),
            new UUID($user->getUuid()),
            (new DateTimeImmutable())->modify('+1 day')
        );

        $this->authTokensRepository->save($authToken);

        return new SuccessfulResponse([
            'token' => $authToken->getToken(),
        ]);
    }
}