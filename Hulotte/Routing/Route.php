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
     * @var callable
     */
    private $callable;

    /**
     * @var string
     */
    private string $method;

    /**
     * @var string|null
     */
    private ?string $name;

    /**
     * @var array|null
     */
    private ?array $params;

    /**
     * @var string
     */
    private string $path;

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
    public function __construct(string $path, ?string $name, callable $callable, string $method)
    {
        $this->path = $path;
        $this->name = $name;
        $this->callable = $callable;
        $this->method = $method;
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
