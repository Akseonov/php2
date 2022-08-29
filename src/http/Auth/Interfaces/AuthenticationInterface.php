<?php

namespace Akseonov\Php2\http\Auth\Interfaces;

use Akseonov\Php2\Blog\User;
use Akseonov\Php2\http\Request;

interface AuthenticationInterface
{
    public function user(Request $request): User;
}