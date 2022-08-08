<?php

namespace Akseonov\Php2\Users;

class User
{
    private int $id;
    private string $name;
    private string $surname;

    public function __construct(int $id, string $name, string $surname)
    {
        $this->id = $id;
        $this->name = $name;
        $this->surname = $surname;
    }

    public function __toString()
    {
        return $this->name . ' ' . $this->surname . PHP_EOL;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getFullName(): string
    {
        return $this->name . $this->surname;
    }
}