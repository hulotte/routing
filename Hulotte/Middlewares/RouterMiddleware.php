<?php

namespace Hulotte\Middlewares;

use GuzzleHttp\Psr7\Response;
use Hulotte\Router\Router;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface
};

/**
 * Class RouterMiddleware
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @package Hulotte\Middlewares
 */
class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var null|callable
     */
    private $notFoundCallable = null;

    /**
     * @var Router
     */
    private $router;

    /**
     * RouterMiddleware constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $url = $request->getUri()->getPath();

        if (!empty($url) && $url !== '/' && $url[-1] === '/') {
            return new Response(301, ['Location' => substr($url, 0, -1)]);
        }

        $route = $this->router->match($request);

        if ($route === null) {
            if ($this->notFoundCallable === null){
                return new Response(404, [], 'Not found !');
            }

            return new Response(404, [], call_user_func_array($this->notFoundCallable, [$request]));
        }

        $callback = $route->getCallable();

        return new Response(200, [], call_user_func_array($callback, [$request]));
    }

    /**
     * @param callable $callable
     */
    public function setNotFoundCallable(callable $callable): void
    {
        $this->notFoundCallable = $callable;
    }
}
