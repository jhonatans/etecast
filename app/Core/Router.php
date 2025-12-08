<?php

namespace App\Core;

class Router {
    private $routes = [];

    public function add($method, $route, $controller) {
        $route = preg_replace('/\{([a-z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        $route = '#^' . $route . '$#';
        $this->routes[] = [$method, $route, $controller];
    }

    public function dispatch() {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            list($routeMethod, $routePattern, $controllerAction) = $route;

            if ($method !== $routeMethod) {
                continue;
            }

            if (preg_match($routePattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                list($controller, $action) = $controllerAction;
                $controllerClass = "App\\Controllers\\" . $controller;

                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $action)) {
                        call_user_func_array([$controllerInstance, $action], $params);
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        echo "<h1>404 - Página não encontrada</h1>";
    }
}