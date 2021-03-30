<?php

namespace tests\FakeClass\Hulotte;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerInvoke
 * @author Sébastien CLEMENT <s.clement@la-taniere.net>
 * @package tests\FakeClass\Hulotte
 */
class ControllerInvoke
{
    public function __invoke(ServerRequestInterface $request): string
    {
        return 'Test';
    }
}
