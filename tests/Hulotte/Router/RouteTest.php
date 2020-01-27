<?php

namespace tests\Hulotte\Router;

use Hulotte\Router\Route;
use PHPUnit\Framework\TestCase;

/**
 * Class RouteTest
 * @author Sébastien CLEMENT<s.clement@la-taniere.net>
 * @covers \Hulotte\Router\Route
 * @package tests\Hulotte\Router
 */
class RouteTest extends TestCase
{
    /**
     * @covers \Hulotte\Router\Route::comparePath
     * @test
     */
    public function comparePath(): void
    {
        $route = $this->createRoute('/article');
        $result = $route->comparePath('/article');

        $this->assertTrue($result);
    }

    /**
     * @covers \Hulotte\Router\Route::comparePath
     * @test
     */
    public function comparePathWithParam(): void
    {
        $route = $this->createRoute('/article/{id:\d+}');
        $result = $route->comparePath('/article/7');

        $this->assertTrue($result);
    }

    /**
     * @covers \Hulotte\Router\Route::extractRegexes
     * @test
     */
    public function extractRegexesWithNoRegex(): void
    {
        $route = $this->createRoute('/article');
        $result = $route->getRegexes();

        $this->assertNull($result);
    }

    /**
     * @covers \Hulotte\Router\Route::extractRegexes
     * @test
     */
    public function extractRegexes(): void
    {
        $route = $this->createRoute('/article/{id:\d+}');
        $result = $route->getRegexes();

        $this->assertSame(['id' => '\d+'], $result);
    }

    /**
     * @covers \Hulotte\Router\Route::extractRegexes
     * @test
     */
    public function extractRegexesWithManyRegex(): void
    {
        $route = $this->createRoute('/article/{id:\d+}/{slug:[a-z-]*}');
        $result = $route->getRegexes();

        $this->assertSame(['id' => '\d+', 'slug' => '[a-z-]*'], $result);
    }

    /**
     * @covers \Hulotte\Router\Route::getPathRegex
     * @test
     */
    public function getPathRegex(): void
    {
        $route = $this->createRoute('/article/{id:\d+}/{slug:[a-z-]*}');
        $result = $route->getPathRegex();

        $this->assertSame('/article/\d+/[a-z-]*', $result);
    }

    /**
     * @covers \Hulotte\Router\Route::getPathRegex
     * @test
     */
    public function getPathRegexIfNull(): void
    {
        $route = $this->createRoute('/article');
        $result = $route->getPathRegex();

        $this->assertSame('/article', $result);
    }

    /**
     * @param string $path
     * @return Route
     */
    private function createRoute(string $path): Route
    {
        return new Route($path, 'test', function () {
            return 'test';
        }, 'GET');
    }
}
