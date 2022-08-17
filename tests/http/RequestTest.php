<?php

namespace Akseonov\Php2\UnitTests\http;

use Akseonov\Php2\Exceptions\HttpException;
use Akseonov\Php2\http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testItPathThrowHttpExceptionIfRequestUriNotProvided(): void
    {
        $request = new Request([], [], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Cannot get path from the request');

        $request->path();
    }

    public function testItPathThrowHttpExceptionIfRequestUriNotValid(): void
    {
        $request = new Request([], [
            'REQUEST_URI' => '?234'
        ], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Cannot get path from the request');

        $request->path();
    }

    /**
     * @throws HttpException
     */
    public function testItPathReturnPath(): void
    {
        $request = new Request([], [
            'REQUEST_URI' => '/users/show'
        ], '');

        $path = $request->path();

        $this->assertEquals('/users/show', $path);
    }

    public function testItQueryThrowHttpExceptionIfParameterNotProvided(): void
    {
        $request = new Request([], [], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('No such query param in the request: param');

        $request->query('param');
    }

    public function testItQueryThrowHttpExceptionIfParameterIsEmpty(): void
    {
        $request = new Request([
            'param' => ''
        ], [], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Empty query param in the request: param');

        $request->query('param');
    }

    public function testItQueryReturnParameterValue(): void
    {
        $request = new Request([
            'param' => 'value'
        ], [], '');

        $value = $request->query('param');

        $this->assertEquals('value', $value);
    }

    public function testItHeaderThrowHttpExceptionIfHeaderNotProvided(): void
    {
        $request = new Request([], [], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Not such header in request: header');

        $request->header('header');
    }

    public function testItHeaderThrowHttpExceptionIfHeaderEmpty(): void
    {
        $request = new Request([], [
            'HTTP_HEADER' => ''
        ], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Empty header in request: ');

        $request->header('header');
    }

    /**
     * @throws HttpException
     */
    public function testItHeaderReturnHeaderValue(): void
    {
        $request = new Request([], [
            'HTTP_HEADER' => 'value'
        ], '');

        $value = $request->header('header');

        $this->assertEquals('value', $value);
    }

    public function testItMethodThrowHttpExceptionIfMethodNotProvided(): void
    {
        $request = new Request([], [], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Cannot get method from request');

        $request->method();
    }

    /**
     * @throws HttpException
     */
    public function testItMethodReturnMethodValue(): void
    {
        $request = new Request([], [
            'REQUEST_METHOD' => 'GET'
        ], '');

        $method = $request->method();

        $this->assertEquals('GET', $method);
    }

    public function testItJsonBodyThrowHttpExceptionIfJsonNotValid(): void
    {
        $request = new Request([], [], '');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Cannot decode json body');

        $request->jsonBody();
    }

    public function testItJsonBodyThrowHttpExceptionIfJsonHasNotArrayOrObject(): void
    {
        $request = new Request([], [], '234');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Not an array/object in json body');

        $request->jsonBody();
    }

    /**
     * @throws HttpException
     */
    public function testItJsonBodyReturnJsonBody(): void
    {
        $request = new Request([], [], '{"key":"value"}');

        $body = $request->jsonBody();

        $this->assertEquals(["key" => "value"], $body);
    }

    public function testItJsonBodyFieldThrowHttpExceptionIfFieldNotProvided(): void
    {
        $request = new Request([], [], '{"key":"value"}');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('No such Field: field');

        $request->jsonBodyField('field');
    }

    public function testItJsonBodyFieldThrowHttpExceptionIfFieldEmpty(): void
    {
        $request = new Request([], [], '{"field":""}');

        $this->expectException(HttpException::class);
        $this->expectErrorMessage('Empty Field: field');

        $request->jsonBodyField('field');
    }

    /**
     * @throws HttpException
     */
    public function testItJsonBodyFieldReturnField(): void
    {
        $request = new Request([], [], '{"field":"123"}');

        $field = $request->jsonBodyField('field');

        $this->assertEquals("123", $field);
    }
}