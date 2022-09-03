<?php

namespace Akseonov\Php2\Blog\Repositories\RepositoryInterfaces;

use Akseonov\Php2\Blog\AuthToken;

interface AuthTokensRepositoryInterface
{
    public function save(AuthToken $authToken): void;

    public function get(string $token): AuthToken;
}