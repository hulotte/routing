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
     * @var mixed
     */
    private mixed $notFoundCallback;

    /**
     * RoutingMiddleware constructor.
     * @param RouteDispatcher $dispatcher
     */
    public function __construct(RouteDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->notFoundCallback = null;
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
            if ($this->notFoundCallback === null) {
                return new Response(404, [], 'Not found !');
            }

            return $this->createNotFoundResponse($this->notFoundCallback, $request, 'notFoundCallback', 404);
        }

        $callback = $route->getCallback();
        $params = $route->getParams();

        if ($params) {
            foreach ($params as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
        }

        return $this->createNotFoundResponse($callback, $request, $route->getName());
    }

    /**
     * @param mixed $callback
     */
    public function setNotFoundCallback(mixed $callback): void
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * @param mixed $callback
     * @param ServerRequestInterface $request
     * @param null|string $routeName
     * @param int $status
     * @return ResponseInterface
     */
    private function createNotFoundResponse(
        mixed $callback,
        ServerRequestInterface $request,
        ?string $routeName = null,
        int $status = 200
    ): ResponseInterface
    {
        if (is_callable($callback)) {
            return new Response($status, [], call_user_func_array($callback, [$request]));
        }

        return new Response($status, [], call_user_func_array([$callback, $routeName], [$request]));
    }
}
