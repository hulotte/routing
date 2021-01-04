<?php

namespace Hulotte\Routing;

/**
 * Class Route
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @package Hulotte\Routing
 */
class Route
{
    /**
     * @var array|null
     */
    private ?array $params;

    /**
     * @var array|null
     */
    private ?array $regexes;

    /**
     * Route constructor.
     * @param string $path
     * @param string|null $name
     * @param callable $callable
     * @param string $method
     */
    public function __construct(
        private string $path,
        private ?string $name,
        private $callable,
        private string $method
    ) {
        $this->params = null;
        $this->regexes = null;
    }

    /**
     * @param string $index
     * @param string $param
     */
    public function addParam(string $index, string $param): void
    {
        $this->params[$index] = $param;
    }

    /**
     * @param string $index
     * @param string $regex
     */
    public function addRegex(string $index, string $regex): void
    {
        $this->regexes[$index] = $regex;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array|null
     */
    public function getRegexes(): ?array
    {
        return $this->regexes;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}
