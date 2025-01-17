<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Xiian\BundleRoute\MissingMappingException;

class RoutesByNamespace
{
    protected array $routeMappingByPHPNamespace = [];

    public static function createFromRouter(RouterInterface $router): self
    {
        $that = new self();
        $that->loadFromRouteCollection($router->getRouteCollection());

        return $that;
    }

    public function getRoutesByPHPNamespace(string $phpNamespace): array
    {
        if (!array_key_exists($phpNamespace, $this->routeMappingByPHPNamespace)) {
            throw MissingMappingException::forMapping($phpNamespace);
        }

        return $this->routeMappingByPHPNamespace[$phpNamespace];
    }

    protected function loadFromRouteCollection(RouteCollection $routeCollection): void
    {
        foreach ($routeCollection->all() as $routeName => $route) {
            /** @var string $controllerClassName */
            $controller = $route->getDefault('_controller');
            if (!$controller) {
                continue;
            }

            $this->addRouteNamespace(
                explode('::', $controller)[0],
                $routeName,
                $route
            );
        }
    }

    public function addRouteNamespace(string $controllerClassName, string $routeName, Route $route): void
    {
        while ($controllerClassName !== '') {
            $this->routeMappingByPHPNamespace[$controllerClassName]             = $this->routeMappingByPHPNamespace[$controllerClassName] ?? [];
            $this->routeMappingByPHPNamespace[$controllerClassName][$routeName] = $route;

            if (!str_contains($controllerClassName, '\\')) {
                break;
            }

            $controllerClassName = substr($controllerClassName, 0, strrpos($controllerClassName, '\\'));
        }
    }
}
