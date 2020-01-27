<?php

namespace Hulotte\Router;

/**
 * Class Route
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @package Hulotte\Router
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
    private $method;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * Route constructor.
     * @param string $path
     * @param null|string $name
     * @param callable $callable
     * @param string $method
     */
    public function __construct(string $path, ?string $name, callable $callable, string $method)
    {
        $this->path = $path;
        $this->name = $name;
        $this->callable = $callable;
        $this->method = $method;
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
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
