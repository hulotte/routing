<?php

namespace tests\Hulotte\Routing;

use Hulotte\Routing\{
    RouteDispatcher,
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
 * @covers \Hulotte\Routing\RouteDispatcher
 * @package tests\Hulotte\Routing
 */
class RouteDispatcherTest extends TestCase
{
    /**
     * @var RouteDispatcher
     */
    private RouteDispatcher $dispatcher;

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::getRoutes
     * @test
     */
    public function getRoutesNull(): void
    {
        $this->assertNull($this->dispatcher->getRoutes());
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::addRoute
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
     * @covers \Hulotte\Routing\RouteDispatcher::match
     * @test
     */
    public function matchNull(): void
    {
        $request = $this->getRequest('/test');

        $this->assertNull($this->dispatcher->match($request));
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::match
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
     * @covers \Hulotte\Routing\RouteDispatcher::match
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
     * @covers \Hulotte\Routing\RouteDispatcher::match
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

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::match
     * @test
     */
    public function matchWithSameRegex(): void
    {
        $request = $this->getRequest('/article/8/10');
        $this->dispatcher->addRoute('/article/{id:\d+}/{nbr:\d+}', 'article.details', function () {
            return 'Test';
        });
        $result = $this->dispatcher->match($request);

        $this->assertSame(['id' => '8', 'nbr' => '10'], $result->getParams());
        $this->assertSame('Test', call_user_func_array($result->getCallable(), [$request]));
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::match
     * @test
     */
    public function matchFail(): void
    {
        $request = $this->getRequest('/article/8');
        $this->dispatcher
            ->addRoute('/article', 'article.all', function () {
                return 'Article';
            });
        $result = $this->dispatcher->match($request);

        $this->assertNull($result);
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::generateUri
     * @test
     */
    public function generateUri(): void
    {
        $this->dispatcher->addRoute('/article', 'article.all', function () {
            return 'Article';
        });
        $result = $this->dispatcher->generateUri('article.all');

        $this->assertSame('/article', $result);
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::generateUri
     * @test
     */
    public function generateUriFailWithoutRoute(): void
    {
        $this->expectException(\Exception::class);
        $this->dispatcher->generateUri('test');
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::generateUri
     * @test
     */
    public function generateUriFail(): void
    {
        $this->dispatcher->addRoute('/article', 'article.all', function () {
            return 'Article';
        });
        $this->expectException(\Exception::class);
        $this->dispatcher->generateUri('test');
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::generateUri
     * @test
     */
    public function generateUriWithParam(): void
    {
        $this->dispatcher->addRoute('/article/{id:\d+}', 'article.detail', function () {
            return 'Article';
        });
        $result = $this->dispatcher->generateUri('article.detail', ['id' => 8]);

        $this->assertSame('/article/8', $result);
    }

    /**
     * @covers \Hulotte\Routing\RouteDispatcher::generateUri
     * @test
     */
    public function generateUriWithManyParam(): void
    {
        $this->dispatcher->addRoute('/article/{id:\d+}/{slug:[a-z-]*}', 'article.detail', function () {
            return 'Article';
        });
        $result = $this->dispatcher->generateUri('article.detail', ['id' => 8, 'slug' => 'coucou']);

        $this->assertSame('/article/8/coucou', $result);
    }

    protected function setUp(): void
    {
        $this->dispatcher = new RouteDispatcher();
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
