<?php

namespace Hulotte\Routing;

use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Dispatcher
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @package Hulotte\Routing
 */
class RouteDispatcher
{
    /**
     * RouteDispatcher constructor
     * @param null|Route[]
     */
    public function __construct(private ?array $routes = null)
    {
    }

    /**
     * @param string $path
     * @param string|null $name
     * @param callable $callable
     * @param string|array $methods
     * @return $this
     */
    public function addRoute(string $path, ?string $name, callable $callable, $methods = 'GET'): self
    {
        if (is_array($methods)) {
            foreach ($methods as $method) {
                $this->routes[] = $this->parse(new Route($path, $name, $callable, $method));
            }
        } else {
            $this->routes[] = $this->parse(new Route($path, $name, $callable, $methods));
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array|null $params
     * @return string
     * @throws Exception
     */
    public function generateUri(string $name, array $params = null): string
    {
        if ($this->routes === null) {
            throw new Exception('Aucune route disponible.');
        }

        $findRoute = null;

        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                $findRoute = $route;
            }
        }

        if ($findRoute === null) {
            throw new Exception('La route "' . $name . '" n\'existe pas.');
        }

        $path = $findRoute->getPath();

        if ($params) {
            foreach ($findRoute->getRegexes() as $key => $regex) {
                if (isset($params[$key])) {
                    $path = str_replace($regex, $params[$key], $path);
                }
            }
        }

        return $path;
    }

    /**
     * @return Route[]|null
     */
    public function getRoutes(): ?array
    {
        return $this->routes;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Route|null
     */
    public function match(ServerRequestInterface $request): ?Route
    {
        $url = $request->getUri()->getPath();
        $method = $request->getMethod();

        if ($this->routes) {
            foreach ($this->routes as $route) {
                if (preg_match('#^' . $route->getPath() . '$#', $url) && $method === $route->getMethod()) {
                    $route = $this->extractParams($route, $url);

                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * @param Route $route
     * @param string $url
     * @return Route
     */
    private function extractParams(Route $route, string $url): Route
    {
        if ($route->getRegexes() !== null) {
            $routePath = explode('/', $route->getPath());
            $urlPath = explode('/', $url);
            $regexes = $route->getRegexes();

            foreach ($urlPath as $key => $pathPart) {
                if (in_array($routePath[$key], $regexes)) {
                    $arrayKey = array_key_first($regexes);
                    array_shift($regexes);

                    $route->addParam($arrayKey, $pathPart);
                }
            }
        }

        return $route;
    }

    /**
     * @param Route $route
     * @return Route
     */
    private function parse(Route $route): Route
    {
        $regex = '#{.*}#';

        if (preg_match($regex, $route->getPath())) {
            $urlParts = explode('/', $route->getPath());
            $pathRegex = '';

            if ($urlParts[0] === '') {
                unset($urlParts[0]);
            }

            foreach ($urlParts as $urlPart) {
                $pathRegex .= '/';

                if (preg_match($regex, $urlPart, $matches)) {
                    $parts = str_replace('{', '', $matches[0]);
                    $parts = str_replace('}', '', $parts);
                    $parts = explode(':', $parts);

                    $route->addRegex($parts[0], $parts[1]);
                    $pathRegex .= $parts[1];
                } else {
                    $pathRegex .= $urlPart;
                }
            }

            $route->setPath($pathRegex);
        }

        return $route;
    }
}
