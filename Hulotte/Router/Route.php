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
     * @var string
     */
    private $pathRegex;

    /**
     * @var null|array
     */
    private $regexes;

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

        $this->extractRegexes();
    }

    /**
     * @param string $path
     * @return bool
     */
    public function comparePath(string $path): bool
    {
        if (preg_match('#' . $this->pathRegex . '#', $path)) {
            return true;
        }

        return false;
    }

    public function extractRegexes(): void
    {
        $regex = '#{.*}#';

        if (preg_match($regex, $this->path)) {
            $urlParts = explode('/', $this->path);

            if ($urlParts[0] === '') {
                unset($urlParts[0]);
            }

            foreach ($urlParts as $urlPart) {
                $this->pathRegex .= '/';

                if (preg_match($regex, $urlPart, $matches)) {
                    $parts = str_replace('{', '', $matches[0]);
                    $parts = str_replace('}', '', $parts);
                    $parts = explode(':', $parts);

                    $this->regexes[$parts[0]] = $parts[1];
                    $this->pathRegex .= $parts[1];
                } else {
                    $this->pathRegex .= $urlPart;
                }
            }
        } else {
            $this->pathRegex = $this->path;
        }
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

    /**
     * @return string
     */
    public function getPathRegex(): string
    {
        return $this->pathRegex;
    }

    /**
     * @return array|null
     */
    public function getRegexes(): ?array
    {
        return $this->regexes;
    }
}
