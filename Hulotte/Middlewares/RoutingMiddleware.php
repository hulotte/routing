<?php

namespace Hulotte\Middlewares;

use GuzzleHttp\Psr7\Response;
use Hulotte\Routing\RouteDispatcher;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface
};

/**
 * Class RoutingMiddleware
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @package Hulotte\Middlewares
 */
class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var RouteDispatcher
     */
    private RouteDispatcher $dispatcher;

    /**
     * @var callable
     */
    private $notFoundCallable;

    /**
     * RoutingMiddleware constructor.
     * @param RouteDispatcher $dispatcher
     */
    public function __construct(RouteDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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

        $route = $this->dispatcher->match($request);

        if ($route === null) {
            if ($this->notFoundCallable === null) {
                return new Response(404, [], 'Not found !');
            }

            return new Response(404, [], call_user_func_array($this->notFoundCallable, [$request]));
        }

        $callable = $route->getCallable();
        $params = $route->getParams();

        if ($params) {
            foreach ($params as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
        }

        return new Response(200, [], call_user_func_array($callable, [$request]));
    }

    /**
     * @param callable $callable
     */
    public function setNotFoundCallable(callable $callable): void
    {
        $this->notFoundCallable = $callable;
    }
}
