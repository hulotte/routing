# Hulotte Routing
## Description
Hulotte Routing is a simple routing system. This package respects PSR7 standards.

## Installation
The easiest way to install Hulotte Routing is to use 
[Composer](https://getcomposer.org/) with this command : 

```bash
$ composer require hulotte/routing
```

## How to declare routes ?
Instanciate the RouteDispatcher class

```php
$dispatcher = new \Hulotte\Routing\RouteDispatcher();
```

Add routes with addRoute method. The necessary parameters are : a path, a name 
and a callable. 

```php
$dispatcher->addRoute('/accueil', 'accueil', function(){
    return 'Hello World';
});
```

You can precise method with the last parameter ('GET' by default).

```php
$dispatcher->addRoute('/accueil', 'accueil', function(){
    return 'Hello World';
}, 'POST');

$dispatcher->addRoute('/accueil', 'accueil', function(){
    return 'Hello World';
}, ['GET', 'POST']);
```

You can add other routes with the fluent way :

```php
$dispatcher->addRoute('/accueil', 'accueil', function(){
        return 'Hello World';
    })
    ->addRoute('/blog', 'blog', function(){
        return 'Welcome on my blog';
    });
```

### With parameters

Parameters need to be declare like this : {parameterName:parameterRegex} and each parameter need to be separate by a '/'.
The parameters will be available on the callable by import the request.

```php
$dispatcher->addRoute('/article/{id:\d+}/{slug:[a-z-]*}', 'accueil', function(ServerRequestInterface $request){
    $params = $request->getAttributes();
});
```

## How to use routing system ?
When routes are declared there's two ways to use Hulotte Routing :

### Manuel method
Example of use :

```php
// $request is an object that implements ServerRequestInterface
// Response is an object that implements ResponseInterface

$route = $dispatcher->match($request);

if ($route === null) {
    return new Response(404, [], 'Not found !');
}

$callable = $route->getCallable();

return new Response(200, [], call_user_func_array($callable, [$request]));
```

### Using class for callback
You can use a class or a class method as a callback.

If you want to use a class per route :
```php
class MyClass 
{
    public function __invoke(ServerRequestInterface $request)
    {
        return 'Hello World';
    }
}

Return New Response(200, [], call_user_func_array(new MyClass(), [$request]));
```

Or if you want to use a class method as a callback :
```php
class MyClass 
{
    public function myMethod(ServerRequestInterface $request)
    {
        return 'Hello World';
    }
}

Return New Response(200, [], call_user_func_array([new MyClass(), 'myMethod'], [$request]));
```

### Middleware
A RouterMiddleware class is available. This middleware erase ending slash on url 
with a 301 redirection, return a 404 error if the route does not exists and create
response.

It's necessary to instanciate RouteDispatcher and define routes before use the middleware.

```php
new \Hulotte\Middlewares\RoutingMiddleware($dispatcher);
```

It's possible to define custom callable for error 404.

```php
$routingMiddleware = new \Hulotte\Middlewares\RoutingMiddleware($dispatcher);
$routingMiddleware->setNotFoundCallable(function(){
    return 'Oups, not found !';
});
```

#### Important !
If you use a class method as callback with the middleware, the name of the route must be
the same as the name of the class.

Ditto if you define a class method for notFoundCallable, the class method name must be 
'notFoundCallable'.