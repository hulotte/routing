<?php

namespace tests\Hulotte\Middlewares;

use Hulotte\Middlewares\RoutingMiddleware;
use Hulotte\Routing\Dispatcher;
use Hulotte\Routing\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RoutingMiddlewareTest
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @covers \Hulotte\Middlewares\RoutingMiddleware
 * @package tests\Hulotte\Middlewares
 */
class RoutingMiddlewareTest extends TestCase
{
    /**
     * @var Dispatcher|MockObject
     */
    private $dispatcher;

    /**
     * @var MockObject|RequestHandlerInterface
     */
    private $handler;

    /**
     * @var Route|MockObject
     */
    private $route;

    /**
     * @var MockObject|ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var MockObject|UriInterface
     */
    private $uriInterface;

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processSuccess(): void
    {
        $this->route->expects($this->once())->method('getCallable')->willReturn(function () {
            return 'Test';
        });
        $this->dispatcher->expects($this->once())->method('match')->willReturn($this->route);
        $middleware = new RoutingMiddleware($this->dispatcher);
        $this->definePath('/test');

        $result = $middleware->process($this->serverRequest, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Test', $result->getBody()->getContents());
    }

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processFail(): void
    {
        $this->dispatcher->expects($this->once())->method('match')->willReturn(null);
        $middelware = new RoutingMiddleware($this->dispatcher);
        $this->definePath('/test');

        $result = $middelware->process($this->serverRequest, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(404, $result->getStatusCode());
    }

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processWithEndingSlash(): void
    {
        $middleware = new RoutingMiddleware($this->dispatcher);
        $this->definePath('/test/');
        $result = $middleware->process($this->serverRequest, $this->handler);

        $this->assertSame(301, $result->getStatusCode());
    }

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processWithNotFoundCallable(): void
    {
        $this->dispatcher->expects($this->once())->method('match')->willReturn(null);
        $middleware = new RoutingMiddleware($this->dispatcher);
        $middleware->setNotFoundCallable(function () {
            return 'Oups not found';
        });
        $this->definePath('/test');

        $result = $middleware->process($this->serverRequest, $this->handler);

        $this->assertSame(404, $result->getStatusCode());
        $this->assertSame('Oups not found', $result->getBody()->getContents());
    }

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processWithoutNotFoundCallable(): void
    {
        $this->dispatcher->expects($this->once())->method('match')->willReturn(null);
        $middleware = new RoutingMiddleware($this->dispatcher);
        $this->definePath('/test');

        $result = $middleware->process($this->serverRequest, $this->handler);

        $this->assertSame(404, $result->getStatusCode());
        $this->assertSame('Not found !', $result->getBody()->getContents());
    }

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->route = $this->createMock(Route::class);
        $this->serverRequest = $this->createMock(ServerRequestInterface::class);
        $this->uriInterface = $this->createMock(UriInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    /**
     * @param string $path
     */
    private function definePath(string $path): void
    {
        $this->uriInterface->expects($this->once())->method('getPath')->willReturn($path);
        $this->serverRequest->expects($this->once())->method('getUri')->willReturn($this->uriInterface);
    }
}
