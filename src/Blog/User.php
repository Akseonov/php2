<?php

namespace Akseonov\Php2\Blog;

use Akseonov\Php2\Person\Name;

class User
{
    /**
     * @param UUID $uuid
     * @param string $username
     * @param Name $name
     */
    public function __construct(
        private readonly UUID   $uuid,
        private readonly string $username,
        private readonly string $password,
        private readonly Name   $name
    )
    {

    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    private static function hash(string $password, UUID $uuid): string
    {
        return hash('sha256', $uuid . $password);
    }

    public function checkPassword(string $password): bool
    {
        return $this->getPassword() === self::hash($password, $this->uuid);
    }

    public static function createForm(
        string $username,
        string $password,
        Name $name
    ): self
    {
        $uuid = UUID::random();
        return new self(
            $uuid,
            $username,
            self::hash($password, $uuid),
            $name,
        );
    }

    public function __toString(): string
    {
        $firstName = $this->getName()->getFirstName();
        $lastName = $this->getName()->getLastName();
        return "Юзер $this->uuid с именем $firstName $lastName и логином $this->username." . PHP_EOL;
    }

    /**
     * @return UUID
     */
    public function getUuid(): string
    {
        return (string)$this->uuid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return Name
     */
    public function getName(): Name
    {
        return $this->name;
    }
}