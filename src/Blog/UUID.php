<?php

namespace Akseonov\Php2\Blog;

use Akseonov\Php2\Exceptions\InvalidArgumentException;

class UUID
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $uuidString
    ) {
        if (!uuid_is_valid($uuidString)) {
            throw new InvalidArgumentException(
                "Malformed UUID: $this->uuidString"
            );
        }
    }

    public static function random(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }
    public function __toString(): string
    {
        return $this->uuidString;
    }
}