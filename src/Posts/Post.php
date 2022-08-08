<?php

namespace Akseonov\Php2\Posts;

class Post
{
    private int $id;
    private int $idAuthor;
    private string $title;
    private string $text;

    public function __construct(int $id, int $idAuthor, string $title, string $text)
    {
        $this->id = $id;
        $this->idAuthor = $idAuthor;
        $this->title = $title;
        $this->text = $text;
    }

    public function __toString()
    {
        return $this->title . PHP_EOL . $this->text . PHP_EOL;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
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