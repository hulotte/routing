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

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccessWithMethod(): void
    {
        $this->router
            ->addRoute('/test', 'test', function () {
                return 'Test Get Success';
            })
            ->addRoute('/test', null, function () {
                return 'Test Post Success';
            }, 'POST');

        $requestGet = $this->getRequest('/test');
        $requestPost = $this->getRequest('/test', 'POST');

        $resultGet = $this->router->match($requestGet);
        $resultPost = $this->router->match($requestPost);

        $this->assertSame('GET', $resultGet->getMethod());
        $this->assertSame('POST', $resultPost->getMethod());
        $this->assertSame('test', $resultGet->getName());
        $this->assertNull($resultPost->getName());
        $this->assertSame('Test Get Success', call_user_func_array($resultGet->getCallable(), [$requestGet]));
        $this->assertSame('Test Post Success', call_user_func_array($resultPost->getCallable(), [$requestPost]));
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccessWithParams(): void
    {
        $request = $this->getRequest('/article/8/mon-super-article');

        $this->router->addRoute('/article/{id:\d+}/{slug:[a-z-]*}', 'article.details', function () {
            return 'Test success';
        });

        $result = $this->router->match($request);

        $this->assertSame('article.details', $result->getName());
        $this->assertSame('Test success', call_user_func_array($result->getCallable(), [$request]));
    }

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    private function getRequest(string $path, string $method = 'GET'): ServerRequestInterface
    {
        $uriInterface = $this->createMock(UriInterface::class);
        $uriInterface->expects($this->once())->method('getPath')->willReturn($path);
        $request = $this->createMock(ServerRequest::class);
        $request->expects($this->once())->method('getUri')->willReturn($uriInterface);
        $request->expects($this->once())->method('getMethod')->willReturn($method);

        return $request;
    }
}
