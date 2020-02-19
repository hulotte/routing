<?php

namespace Hulotte\Middlewares;

use GuzzleHttp\Psr7\Response;
use Hulotte\Routing\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RoutingMiddleware
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @package Hulotte\Middlewares
 */
class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var callable
     */
    private $notFoundCallable;

    /**
     * RoutingMiddleware constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
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
