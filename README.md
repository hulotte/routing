# Hulotte Routing
## Description
Hulotte Routing is a simple routing system. This package respects PSR7 standards.

## Installation
The easiest way to install Hulotte Middleware Dispatcher is to use 
[Composer](https://getcomposer.org/) with this command : 

```bash
$ composer require hulotte/routing
```

## How to declare routes ?
Instanciate the Router class :

```php
$router = new \Hulotte\Router\Router();
```

Add routes with addRoute method. The necessary parameters are : a path, a name 
and a callable. 

```php
$router->addRoute('/accueil', 'accueil', function(){
    return 'Hello World';
});
```

You can add other routes with the fluent way :

```php
$router->addRoute('/accueil', 'accueil', function(){
        return 'Hello World';
    })
    ->addRoute('/blog', 'blog', function(){
        return 'Welcome on my blog';
    });
```

## How to use routing system ?
When routes are declared there's to way to use Hulotte Routing :

### Manuel method
Example of use :

```php
// $request is an object that implements ServerRequestInterface
// Response is an object that implements ResponseInterface

$route = $this->router->match($request);

if ($route === null) {
    return new Response(404, [], 'Not found !');
}

$callback = $route->getCallable();

return new Response(200, [], call_user_func_array($callback, [$request]));
```

### Middleware
A RouterMiddleware class is available. This middleware erase ending slash on url 
with a 301 redirection, return a 404 error if the route does not exists and create
response.

It's necessary to instanciate Router and define routes before use the middleware.

```php
new \Hulotte\Middlewares\RouterMiddleware($router);
```

It's possible to define custom callable for error 404.

```php
$routerMiddleware = new \Hulotte\Middlewares\RouterMiddleware($router);
$routerMiddleware->setNotFoundCallable(function(){
    return 'Oups, not found !';
});
```
