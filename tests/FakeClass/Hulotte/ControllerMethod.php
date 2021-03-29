<?php

namespace tests\FakeClass\Hulotte;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerMethod
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @package tests\FakeClass\Hulotte
 */
class ControllerMethod
{
    public function theMethod(ServerRequestInterface $request): string
    {
        return 'Test';
    }
}
