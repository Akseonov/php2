<?php

namespace Akseonov\Php2\http;

use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\Exceptions\JsonException;

class Request
{
    public function __construct(
        private array $get,
        private array $server,
        private string $body
    )
    {
    }

    /**
     * @throws HttpException
     */
    public function path(): string
    {
        if (!array_key_exists('REQUEST_URI', $this->server)) {
            throw new HttpException('Cannot get path from the request');
        }

        $component = parse_url($this->server['REQUEST_URI']);

        if (!is_array($component) || !array_key_exists('path', $component)) {
            throw new HttpException('Cannot get path from the request');
        }

        return $component['path'];
    }

    /**
     * @throws HttpException
     */
    public function query(string $param): string
    {
        if (!array_key_exists($param, $this->get)) {
            throw new HttpException("No such query param in the request: $param");
        }

        $value = trim($this->get[$param]);

        if (empty($value)) {
            throw new HttpException("Empty query param in the request: $param");
        }

        return $value;
    }

    /**
     * @throws HttpException
     */
    public function header(string $header): string
    {
        $headerName = mb_strtoupper("http_" . str_replace('-', '_', $header));

        if (!array_key_exists($headerName, $this->server)) {
            throw new HttpException("Not such header in request: $header");
        }

        $value = trim($this->server[$headerName]);

        if (empty($value)) {
            throw new HttpException("Empty header in request: $value");
        }

        return $value;
    }

    /**
     * @throws HttpException
     */
    public function method(): string
    {
        if (!array_key_exists('REQUEST_METHOD', $this->server)) {
            throw new HttpException('Cannot get method from request');
        }

        return $this->server['REQUEST_METHOD'];
    }

    /**
     * @throws HttpException
     */
    public function jsonBody(): array
    {
        try {
            $data = json_decode(
                $this->body,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new HttpException('Cannot decode json body');
        }

        if (!is_array($data)) {
            throw new HttpException('Not an array/object in json body');
        }

        return $data;
    }

    /**
     * @throws HttpException
     */
    public function jsonBodyField(string $field): mixed
    {
        $data = $this->jsonBody();

        if (!array_key_exists($field, $data)) {
            throw new HttpException("No such Field: $field");
        }

        if (empty($data[$field])) {
            throw new HttpException("Empty Field: $field");
        }

        return $data[$field];
    }
}