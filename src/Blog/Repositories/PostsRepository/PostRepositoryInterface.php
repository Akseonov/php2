<?php

namespace Akseonov\Php2\Blog\Repositories\PostsRepository;

use Akseonov\Php2\Blog\Post;
use Akseonov\Php2\Blog\UUID;

interface PostRepositoryInterface
{
    public function save(Post $post): void;
    public function get(UUID $uuid): Post;
    public function getByTitle(string $title): Post;
}