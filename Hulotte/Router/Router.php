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
     * @param string $name
     * @param callable $callable
     * @return Router
     */
    public function addRoute(string $path, string $name, callable $callable): self
    {
        $this->routes[] = new Route($path, $name, $callable);

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

        foreach ($this->routes as $route) {
            if ($url === $route->getPath()) {
                return $route;
            }
        }

        return null;
    }
}
