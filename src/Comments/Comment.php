<?php

namespace Akseonov\Php2\Comments;

class Comment
{
    protected int $id;
    protected int $idAuthor;
    protected int $idPost;
    protected string $text;

    public function __construct(int $id, int $idAuthor, int $idPost, string $text)
    {
        $this->id = $id;
        $this->idAuthor = $idAuthor;
        $this->idPost = $idPost;
        $this->text = $text;
    }

    public function __toString()
    {
        return $this->text . PHP_EOL;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getIdAuthor(): string
    {
        return $this->idAuthor;
    }

    public function setIdAuthor(string $idAuthor): void
    {
        $this->idAuthor = $idAuthor;
    }

    public function getIdPost(): string
    {
        return $this->idPost;
    }

    public function setIdPost(string $idPost): void
    {
        $this->idPost = $idPost;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}