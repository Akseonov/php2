<?php

namespace Akseonov\Php2\Blog\Repositories;

use Akseonov\Php2\Blog\User;
use Akseonov\Php2\Blog\UUID;

interface UsersRepositoryInterface
{
    public function save(User $user): void;
    public function get(UUID $uuid): User;
    public function getByUsername(string $username): User;
}