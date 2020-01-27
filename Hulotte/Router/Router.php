<?php

namespace Hulotte\Router;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Router
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @package Hulotte\Router
 */
class Router
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * Add new Route
     * @param string $path
     * @param null|string $name
     * @param callable $callable
     * @param string $method
     * @return Router
     */
    public function addRoute(string $path, ?string $name, callable $callable, string $method = 'GET'): self
    {
        $this->routes[] = new Route($path, $name, $callable, $method);

        return $this;
    }

    /**
     * Verify that a request match with a route
     * @param ServerRequestInterface $request
     * @return Route|null
     */
    public function match(ServerRequestInterface $request): ?Route
    {
        $url = $request->getUri()->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route->comparePath($url) && $method === $route->getMethod()) {
                return $route;
            }
        }

        return null;
    }
}
