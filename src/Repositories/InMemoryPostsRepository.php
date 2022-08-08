<?php

namespace Akseonov\Php2\Repositories;

use Akseonov\Php2\Posts\Post;
use Akseonov\Php2\Exeptions\PostNotFoundException;

class InMemoryPostsRepository
{
    private array $posts = [];

    public function save(Post $post): void
    {
        $this->posts[] = $post;
    }

    public function get(int $id): Post
    {
        foreach ($this->posts as $post) {
            if ($post->id() === $id) {
                return $post;
            }
        }
        throw new PostNotFoundException("Post not found: $id");
    }
}