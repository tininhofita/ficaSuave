<?php

class Router
{
    private $routes = [];

    public function get($route, $action)
    {
        $this->routes['GET'][$route] = $action;
    }

    public function post($route, $action)
    {
        $this->routes['POST'][$route] = $action;
    }

    public function dispatch()
    {
        if (!isset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'])) {
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 1º: tenta casar uma rota exata
        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            is_array($action) ? call_user_func([new $action[0], $action[1]]) : call_user_func($action);
            exit;
        }

        // 2º: tenta casar por regex
        foreach ($this->routes[$method] as $route => $action) {
            if (preg_match("#^{$route}$#", $uri, $matches)) {
                array_shift($matches); // remove o match completo
                is_array($action)
                    ? call_user_func_array([new $action[0], $action[1]], $matches)
                    : call_user_func_array($action, $matches);
                exit;
            }
        }

        $this->handleNotFound();
    }


    private function handleNotFound()
    {
        http_response_code(404);

        $errorPage = BASE_PATH . '/public/views/404.php';

        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "404 - Página não encontrada.";
        }

        exit;
    }
}
