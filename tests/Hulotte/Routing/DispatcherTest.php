<?php

namespace tests\Hulotte\Routing;

use Hulotte\Routing\{
    Dispatcher,
    Route
};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{
    ServerRequestInterface,
    UriInterface
};

/**
 * Class DispatcherTest
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @covers \Hulotte\Routing\Dispatcher
 * @package tests\Hulotte\Routing
 */
class DispatcherTest extends TestCase
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @covers \Hulotte\Routing\Dispatcher::getRoutes
     * @test
     */
    public function getRoutesNull(): void
    {
        $this->assertNull($this->dispatcher->getRoutes());
    }

    /**
     * @covers \Hulotte\Routing\Dispatcher::addRoute
     * @test
     */
    public function addRoute(): void
    {
        $callback = function () {
            return 'Test';
        };
        $this->dispatcher
            ->addRoute('/test', 'test', $callback)
            ->addRoute('/test2', null, $callback, 'POST')
            ->addRoute('/test3', 'test3', $callback, ['GET', 'POST']);

        $routes = $this->dispatcher->getRoutes();

        $this->assertSame('test', $routes[0]->getName());
        $this->assertSame('GET', $routes[0]->getMethod());
        $this->assertNull($routes[1]->getName());
        $this->assertSame('POST', $routes[1]->getMethod());
        $this->assertSame('test3', $routes[2]->getName());
        $this->assertSame('test3', $routes[3]->getName());
        $this->assertSame('GET', $routes[2]->getMethod());
        $this->assertSame('POST', $routes[3]->getMethod());
    }

    /**
     * @covers \Hulotte\Routing\Dispatcher::match
     * @test
     */
    public function matchNull(): void
    {
        $request = $this->getRequest('/test');

        $this->assertNull($this->dispatcher->match($request));
    }

    /**
     * @covers \Hulotte\Routing\Dispatcher::match
     * @test
     */
    public function matchSimple(): void
    {
        $request = $this->getRequest('/test');
        $this->dispatcher->addRoute('/test', 'test', function () {
            return 'Test';
        });
        $result = $this->dispatcher->match($request);

        $this->assertInstanceOf(Route::class, $result);
        $this->assertSame('/test', $result->getPath());
        $this->assertSame('test', $result->getName());
        $this->assertSame('Test', call_user_func_array($result->getCallable(), [$request]));
    }

    /**
     * @covers \Hulotte\Routing\Dispatcher::match
     * @test
     */
    public function matchWithParam(): void
    {
        $request = $this->getRequest('/user/7');
        $this->dispatcher->addRoute('/user/{id:\d+}', 'user.details', function () {
            return 'Test';
        });
        $result = $this->dispatcher->match($request);

        $this->assertSame(['id' => '7'], $result->getParams());
    }

    /**
     * @covers \Hulotte\Routing\Dispatcher::match
     * @test
     */
    public function matchWithManyParams(): void
    {
        $request = $this->getRequest('/article/8/mon-super-titre');
        $this->dispatcher->addRoute('/article/{id:\d+}/{slug:[a-z-]*}', 'article.details', function () {
            return 'Test';
        });
        $result = $this->dispatcher->match($request);

        $this->assertSame(['id' => '8', 'slug' => 'mon-super-titre'], $result->getParams());
        $this->assertSame('Test', call_user_func_array($result->getCallable(), [$request]));
    }

    protected function setUp(): void
    {
        $this->dispatcher = new Dispatcher();
    }

    /**
     * @param string $path
     * @param string $method
     * @return ServerRequestInterface
     */
    private function getRequest(string $path, string $method = 'GET'): ServerRequestInterface
    {
        $uriInterface = $this->createMock(UriInterface::class);
        $uriInterface->expects($this->once())->method('getPath')->willReturn($path);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getUri')->willReturn($uriInterface);
        $request->expects($this->once())->method('getMethod')->willReturn($method);

        return $request;
    }
}
