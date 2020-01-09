<?php

namespace tests\Hulotte\Router;

use GuzzleHttp\Psr7\ServerRequest;
use Hulotte\Router\{
    Route,
    Router
};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{
    RequestInterface,
    ServerRequestInterface,
    UriInterface
};

/**
 * Class RouterTest
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @covers \Hulotte\Router\Router
 * @package tests\Hulotte\Router
 */
class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccess(): void
    {
        $request = $this->getRequest('/test');

        $this->router->addRoute('/test', 'test', function () {
            return 'Test success';
        });

        $result = $this->router->match($request);

        $this->assertInstanceOf(Route::class, $result);
        $this->assertSame('test', $result->getName());
        $this->assertSame('Test success', call_user_func_array($result->getCallable(), [$request]));
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchFail(): void
    {
        $request = $this->getRequest('/test');
        $result = $this->router->match($request);

        $this->assertNull($result);
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccessWithAddRouteFluent(): void
    {
        $this->router
            ->addRoute('/test', 'test', function () {
                return 'test success';
            })
            ->addRoute('/blog', 'blog', function () {
                return 'Je suis sur le blog';
            });

        $request = $this->getRequest('/test');
        $request2 = $this->getRequest('/blog');

        $result = $this->router->match($request);
        $result2 = $this->router->match($request2);

        $this->assertSame('test', $result->getName());
        $this->assertSame('test success', call_user_func_array($result->getCallable(), [$request]));
        $this->assertSame('blog', $result2->getName());
        $this->assertSame('Je suis sur le blog', call_user_func_array($result2->getCallable(), [$request2]));
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccessWithAddRouteNotFluent(): void
    {
        $this->router->addRoute('/test', 'test', function () {
            return 'test success';
        });
        $this->router->addRoute('/blog', 'blog', function () {
            return 'Je suis sur le blog';
        });

        $request = $this->getRequest('/test');
        $request2 = $this->getRequest('/blog');

        $result = $this->router->match($request);
        $result2 = $this->router->match($request2);

        $this->assertSame('test', $result->getName());
        $this->assertSame('test success', call_user_func_array($result->getCallable(), [$request]));
        $this->assertSame('blog', $result2->getName());
        $this->assertSame('Je suis sur le blog', call_user_func_array($result2->getCallable(), [$request2]));
    }

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    private function getRequest(string $path): ServerRequestInterface
    {
        $uriInterface = $this->createMock(UriInterface::class);
        $uriInterface->expects($this->once())->method('getPath')->willReturn($path);
        $request = $this->createMock(ServerRequest::class);
        $request->expects($this->once())->method('getUri')->willReturn($uriInterface);

        return $request;
    }
}
