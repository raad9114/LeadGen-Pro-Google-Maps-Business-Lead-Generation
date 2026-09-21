<?php
/**
 * Simple PHP Router
 * Maps URL paths to controller actions
 */

class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    /**
     * Register a GET route
     */
    public function get(string $path, string $controller, string $method): void
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Register a POST route
     */
    public function post(string $path, string $controller, string $method): void
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Register a PUT route
     */
    public function put(string $path, string $controller, string $method): void
    {
        $this->routes['PUT'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $path, string $controller, string $method): void
    {
        $this->routes['DELETE'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_GET['url'] ?? '';
        $requestUri = trim($requestUri, '/');

        // Support PUT/DELETE via POST with _method field
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        $routes = $this->routes[$requestMethod] ?? [];

        foreach ($routes as $route => $handler) {
            $route = trim($route, '/');
            $pattern = $this->routeToRegex($route);

            if (preg_match($pattern, $requestUri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controllerName = $handler['controller'];
                $methodName = $handler['method'];

                $controllerFile = dirname(__DIR__) . '/app/controllers/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    $this->notFound("Controller not found: {$controllerName}");
                    return;
                }

                require_once $controllerFile;

                if (!class_exists($controllerName)) {
                    $this->notFound("Controller class not found: {$controllerName}");
                    return;
                }

                $controller = new $controllerName();

                if (!method_exists($controller, $methodName)) {
                    $this->notFound("Method not found: {$controllerName}::{$methodName}");
                    return;
                }

                call_user_func_array([$controller, $methodName], $params);
                return;
            }
        }

        $this->notFound();
    }

    /**
     * Convert route pattern to regex
     * Supports {param} placeholders
     */
    private function routeToRegex(string $route): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Handle 404
     */
    private function notFound(string $message = 'Page not found'): void
    {
        http_response_code(404);
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        } else {
            $pageTitle = '404 Not Found';
            $appName = Config::appName();
            include dirname(__DIR__) . '/templates/pages/404.php';
        }
    }

    /**
     * Check if request is an API call
     */
    private function isApiRequest(): bool
    {
        $url = $_GET['url'] ?? '';
        return str_starts_with($url, 'api/') ||
               (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
