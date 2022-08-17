<?php

namespace Akseonov\Php2\Blog\Repositories\RepositoryInterfaces;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\UUID;

interface PostsRepositoryInterface
{
    public function save(Post $post): void;
    public function get(UUID $uuid): Post;
    public function getByTitle(string $title): Post;
    public function delete(UUID $uuid): void;
}