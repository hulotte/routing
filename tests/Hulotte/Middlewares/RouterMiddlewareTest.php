<?php

namespace tests\Hulotte\Middlewares;

use Hulotte\{
    Middlewares\RouterMiddleware,
    Router\Route,
    Router\Router
};
use PHPUnit\Framework\TestCase;
use Psr\Http\{Message\ResponseInterface,
    Message\ServerRequestInterface,
    Message\UriInterface,
    Server\RequestHandlerInterface
};

/**
 * Class RouterMiddlewareTest
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @covers \Hulotte\Middlewares\RouterMiddleware
 * @package tests\Hulotte\Middlewares
 */
class RouterMiddlewareTest extends TestCase
{
    /**
     * @var
     */
    private $route;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var UriInterface
     */
    private $uriInterface;

    /**
     * @covers \Hulotte\Middlewares\RouterMiddleware::process
     * @test
     */
    public function processSuccess(): void
    {
        $this->route->expects($this->once())->method('getCallable')->willReturn(function () {
            return 'coucou';
        });
        $this->router->expects($this->once())->method('match')->willReturn($this->route);
        $middleware = new RouterMiddleware($this->router);
        $this->definePath('/test');

        $result = $middleware->process(
            $this->serverRequest,
            $this->createMock(RequestHandlerInterface::class)
        );

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('coucou', $result->getBody()->getContents());
    }

    /**
     * @covers \Hulotte\Middlewares\RouterMiddleware::process
     * @test
     */
    public function processFail(): void
    {
        $this->router->expects($this->once())->method('match')->willReturn(null);
        $middleware = new RouterMiddleware($this->router);
        $this->definePath('/test');

        $result = $middleware->process(
            $this->serverRequest,
            $this->createMock(RequestHandlerInterface::class)
        );

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame(404, $result->getStatusCode());
    }

    /**
     * @covers \Hulotte\Middlewares\RouterMiddleware::process
     * @test
     */
    public function processWithEndingSlash(): void
    {
        $middleware = new RouterMiddleware($this->router);
        $this->definePath('/test/');
        $result = $middleware->process(
            $this->serverRequest,
            $this->createMock(RequestHandlerInterface::class)
        );

        $this->assertSame(301, $result->getStatusCode());
    }

    protected function setUp(): void
    {
        $this->router = $this->createMock(Router::class);
        $this->route = $this->createMock(Route::class);
        $this->serverRequest = $this->createMock(ServerRequestInterface::class);
        $this->uriInterface = $this->createMock(UriInterface::class);
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
