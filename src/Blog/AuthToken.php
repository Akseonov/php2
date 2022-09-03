<?php

namespace Akseonov\Php2\Blog;

use DateTimeImmutable;

class AuthToken
{
    public function __construct(
        private readonly string            $token,
        private readonly UUID              $userUuid,
        private readonly DateTimeImmutable $expiresOn
    )
    {
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return UUID
     */
    public function getUserUuid(): UUID
    {
        return $this->userUuid;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getExpiresOn(): DateTimeImmutable
    {
        return $this->expiresOn;
    }
}