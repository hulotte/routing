<?php

namespace tests\Hulotte\Router;

use GuzzleHttp\Psr7\ServerRequest;
use Hulotte\Router\{
    Route,
    Router
};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

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
        $this->router->addRoute('/test', 'test', function(){
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
        $result = $this->router->match($this->request);

        $this->assertNull($result);
    }

    protected function setUp(): void
    {
        $this->request = new ServerRequest('GET', '/test');
        $this->router = new Router();
    }
}
