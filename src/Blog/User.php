<?php

namespace Akseonov\Php2\Blog;

use Akseonov\Php2\Person\Name;

class User
{
    /**
     * @param UUID $uuid
     * @param Name $name
     */
    public function __construct(
        private UUID $uuid,
        private string $username,
        private Name $name
    )
    {

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
    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @param UUID $uuid
     */
    public function setUuid(UUID $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return Name
     */
    public function getName(): Name
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName(Name $name): void
    {
        $this->name = $name;
    }


}