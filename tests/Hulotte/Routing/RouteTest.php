<?php

namespace tests\Hulotte\Routing;

use Hulotte\Routing\Route;
use PHPUnit\Framework\TestCase;

/**
 * Class RouteTest
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @covers \Hulotte\Routing\Route
 * @package tests\Hulotte\Routing
 */
class RouteTest extends TestCase
{
    /**
     * @var Route
     */
    private Route $route;

    /**
     * @covers \Hulotte\Routing\Route::addRegex
     * @test
     */
    public function addRegex(): void
    {
        $this->route->addRegex('id', '\d+');

        $result = $this->route->getRegexes();

        $this->assertIsArray($result);
        $this->assertSame('\d+', $result['id']);
    }

    /**
     * @covers \Hulotte\Routing\Route::addParam
     * @test
     */
    public function addParam(): void
    {
        $this->route->addParam('slug', 'mon-slug');

        $result = $this->route->getParams();

        $this->assertSame('mon-slug', $result['slug']);
    }

    protected function setUp(): void
    {
        $this->route = new Route('/test', 'test', function () {
            return 'Test';
        }, 'GET');
    }
}
