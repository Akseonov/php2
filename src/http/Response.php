<?php

namespace Akseonov\Php2\http;

use JsonException;

abstract class Response
{
    abstract protected function payload(): array;

    protected const SUCCESS = true;

    /**
     * @throws JsonException
     */
    public function send(): void
    {
        $data = ['success' => static::SUCCESS] + $this->payload();

        header('Content-Type: application/json');

        echo json_encode($data, JSON_THROW_ON_ERROR);
    }
}