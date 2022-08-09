<?php

namespace Akseonov\Php2\Blog;

class Comment
{
    public function __construct(
        private UUID $uuid,
        private UUID $postUuid,
        private UUID $authorUuid,
        private string $text
    )
    {
    }

    public function __toString(): string
    {
        return $this->authorUuid . ' пишет комментарий: ' . $this->text . PHP_EOL;
    }

    /**
     * @return UUID
     */
    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return UUID
     */
    public function getPostUuid(): UUID
    {
        return $this->postUuid;
    }

    /**
     * @return UUID
     */
    public function getAuthorUuid(): UUID
    {
        return $this->authorUuid;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
}