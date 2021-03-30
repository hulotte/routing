<?php

namespace tests\Hulotte\Middlewares;

use Hulotte\{
    Middlewares\RoutingMiddleware,
    Routing\RouteDispatcher,
    Routing\Route
};
use PHPUnit\Framework\TestCase;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Message\UriInterface,
    Server\RequestHandlerInterface
};
use tests\FakeClass\Hulotte\ControllerInvoke;
use tests\FakeClass\Hulotte\ControllerMethod;

/**
 * Class RoutingMiddlewareTest
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @covers \Hulotte\Middlewares\RoutingMiddleware
 * @package tests\Hulotte\Middlewares
 */
class RoutingMiddlewareTest extends TestCase
{
    /**
     * @var RouteDispatcher
     */
    private RouteDispatcher $dispatcher;

    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $handler;

    /**
     * @var Route
     */
    private Route $route;

    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $serverRequest;

    /**
     * @var UriInterface
     */
    private UriInterface $uriInterface;

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processSuccess(): void
    {
        $this->route->expects($this->once())->method('getCallback')->willReturn(function () {
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
    public function processWithNotFoundCallback(): void
    {
        $this->dispatcher->expects($this->once())->method('match')->willReturn(null);
        $middleware = new RoutingMiddleware($this->dispatcher);
        $middleware->setNotFoundCallback(function () {
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

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processWithParams(): void
    {
        $this->definePath('/test/8');
        $this->serverRequest->expects($this->once())->method('withAttribute')->willReturn($this->serverRequest);
        $this->route->expects($this->once())->method('getParams')->willReturn(['id' => 8]);
        $this->route->expects($this->once())->method('getCallback')->willReturn(function () {
            return 'Test';
        });
        $this->dispatcher->expects($this->once())->method('match')->willReturn($this->route);
        $middleware = new RoutingMiddleware($this->dispatcher);

        $middleware->process($this->serverRequest, $this->handler);
    }

    /**
     * @covers \Hulotte\Middlewares\RoutingMiddleware::process
     * @test
     */
    public function processWithControllerInvoke(): void
    {
        $this->route->expects($this->once())->method('getCallback')->willReturn(new ControllerInvoke());
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
    public function processWithControllerMethod(): void
    {
        $this->route->expects($this->once())->method('getCallback')->willReturn(new ControllerMethod());
        $this->route->expects($this->once())->method('getName')->willReturn('theMethod');
        $this->dispatcher->expects($this->once())->method('match')->willReturn($this->route);
        $middleware = new RoutingMiddleware($this->dispatcher);
        $this->definePath('/test');

        $result = $middleware->process($this->serverRequest, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('Test', $result->getBody()->getContents());
    }

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(RouteDispatcher::class);
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
