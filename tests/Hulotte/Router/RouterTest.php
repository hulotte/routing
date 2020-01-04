<?php

namespace tests\Hulotte\Router;

use GuzzleHttp\Psr7\ServerRequest;
use Hulotte\Router\{
    Route,
    Router
};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{RequestInterface, ServerRequestInterface, UriInterface};

/**
 * Class RouterTest
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @covers \Hulotte\Router\Router
 * @package tests\Hulotte\Router
 */
class RouterTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

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
        $this->router = new Router();
        $this->router->addRoute('/test', 'test', function () {
            return 'test success';
        });

        $result = $this->router->match($this->request);

        $this->assertInstanceOf(Route::class, $result);
        $this->assertSame('test', $result->getName());
        $this->assertSame('test success', call_user_func_array($result->getCallable(), [$this->request]));
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchFail(): void
    {
        $this->router = new Router();
        $result = $this->router->match($this->request);

        $this->assertNull($result);
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccessWithAddRouteFluent(): void
    {
        $this->router = new Router();
        $this->router
            ->addRoute('/test', 'test', function () {
                return 'test success';
            })
            ->addRoute('/blog', 'blog', function () {
                return 'Je suis sur le blog';
            });

        $result = $this->router->match($this->request);
        $result2 = $this->router->match($this->defineRequest('/blog'));

        $this->assertSame('test', $result->getName());
        $this->assertSame('test success', call_user_func_array($result->getCallable(), [$this->request]));
        $this->assertSame('blog', $result2->getName());
        $this->assertSame('Je suis sur le blog', call_user_func_array($result2->getCallable(), [$this->request]));
    }

    /**
     * @covers \Hulotte\Router\Router::match
     * @test
     */
    public function matchSuccessWithAddRouteNotFluent(): void
    {
        $this->router = new Router();
        $this->router->addRoute('/test', 'test', function () {
            return 'test success';
        });
        $this->router->addRoute('/blog', 'blog', function () {
            return 'Je suis sur le blog';
        });

        $result = $this->router->match($this->request);
        $result2 = $this->router->match($this->defineRequest('/blog'));

        $this->assertSame('test', $result->getName());
        $this->assertSame('test success', call_user_func_array($result->getCallable(), [$this->request]));
        $this->assertSame('blog', $result2->getName());
        $this->assertSame('Je suis sur le blog', call_user_func_array($result2->getCallable(), [$this->request]));
    }

    protected function setUp(): void
    {
        $this->request = $this->defineRequest('/test');
    }

    /**
     * @param string $path
     * @return RequestInterface
     */
    private function defineRequest(string $path): RequestInterface
    {
        $uriInterface = $this->createMock(UriInterface::class);
        $uriInterface->expects($this->once())->method('getPath')->willReturn($path);
        $request = $this->createMock(ServerRequest::class);
        $request->expects($this->once())->method('getUri')->willReturn($uriInterface);

        return $request;
    }
}
