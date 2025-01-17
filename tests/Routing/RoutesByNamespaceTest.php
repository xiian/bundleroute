<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Test\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Xiian\BundleRoute\MissingMappingException;
use Xiian\BundleRoute\Routing\RoutesByNamespace;

/**
 * @coversDefaultClass \Xiian\BundleRoute\Routing\RoutesByNamespace
 */
class RoutesByNamespaceTest extends TestCase
{
    /**
     * @param string[] $expected
     * @param Route[]  $actual
     */
    public function assertRoutesMatch(array $expected, array $actual): void
    {
        $actualRoutes = array_map(fn(Route $route) => $route->getPath(), $actual);
        sort($actualRoutes);
        sort($expected);
        self::assertEquals($expected, $actualRoutes, 'Routes are not as expected');
    }

    public function testBasicWithMultipleAdded(): void
    {
        $sut = new RoutesByNamespace();

        $sut->addRouteNamespace(__CLASS__, 'routename', new Route('/routename'));
        $sut->addRouteNamespace(__CLASS__, 'routename2', new Route('/routename2'));

        $this->assertRoutesMatch(['/routename', '/routename2'], $sut->getRoutesByPHPNamespace(__CLASS__));
    }

    public function testBasicWithMultipleAddedInDifferentChildNamespaces(): void
    {
        $sut = new RoutesByNamespace();

        $sut->addRouteNamespace('\\One\\Two\\Three', 'routename', new Route('/routename'));
        $sut->addRouteNamespace('\\One\\Two\\Three', 'routename2', new Route('/routename2'));
        $sut->addRouteNamespace('\\One\\Two\\Free', 'otherroutename', new Route('/otherroutename'));
        $sut->addRouteNamespace('\\One\\Two\\Free', 'otherroutename2', new Route('/otherroutename2'));

        $this->assertRoutesMatch([
            '/routename',
            '/routename2',
            '/otherroutename',
            '/otherroutename2',
        ], $sut->getRoutesByPHPNamespace('\\One\\Two'));
    }

    public function testBasicWithMultipleAddedInDifferentChildNamespacesDeeper(): void
    {
        $sut = new RoutesByNamespace();

        $sut->addRouteNamespace('\\One\\Two\\Three\\Go\\Deeper\\More\\Alpha', 'routename', new Route('/routename'));
        $sut->addRouteNamespace('\\One\\Two\\Three\\Go\\Deeper\\More\\Beta', 'routename2', new Route('/routename2'));
        $sut->addRouteNamespace('\\One\\Two\\Free\\Go\\Deeper\\More\\Gamma', 'otherroutename', new Route('/otherroutename'));
        $sut->addRouteNamespace('\\One\\Two\\Free\\Go\\Deeper\\More\\Cappa', 'otherroutename2', new Route('/otherroutename2'));

        $this->assertRoutesMatch([
            '/routename',
            '/routename2',
            '/otherroutename',
            '/otherroutename2',
        ], $sut->getRoutesByPHPNamespace('\\One\\Two'));

        $this->assertRoutesMatch([
            '/routename',
            '/routename2',
        ], $sut->getRoutesByPHPNamespace('\\One\\Two\\Three'));

        $this->assertRoutesMatch([
            '/otherroutename',
            '/otherroutename2',
        ], $sut->getRoutesByPHPNamespace('\\One\\Two\\Free'));

        $this->assertRoutesMatch([
            '/otherroutename',
            '/otherroutename2',
        ], $sut->getRoutesByPHPNamespace('\\One\\Two\\Free\\Go\\Deeper\\More'));

        $this->assertRoutesMatch([
            '/otherroutename',
        ], $sut->getRoutesByPHPNamespace('\\One\\Two\\Free\\Go\\Deeper\\More\\Gamma'));
    }

    public function testBasicWithSingleAdded(): void
    {
        $sut = new RoutesByNamespace();

        $sut->addRouteNamespace(__CLASS__, 'routename', new Route('/routename'));

        $routesByPHPNamespace = $sut->getRoutesByPHPNamespace(__CLASS__);
        $this->assertRoutesMatch(['/routename'], $routesByPHPNamespace);
    }

    public function testBasicWithSingleAddedGetParent(): void
    {
        $sut = new RoutesByNamespace();

        $controllerClassName = '\\One\\Two\\Three';
        $sut->addRouteNamespace($controllerClassName, 'routename', new Route('/routename'));

        $routesByPHPNamespace = $sut->getRoutesByPHPNamespace('\\One');
        $this->assertRoutesMatch(['/routename'], $routesByPHPNamespace);
    }

    public function testCreateFromRouter(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('route', new Route('/route', ['_controller' => 'Basic::one']));
        $routeCollection->add('route2', new Route('/route2', ['_controller' => 'Basic::two']));

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')
               ->willReturn($routeCollection);

        $sut = RoutesByNamespace::createFromRouter($router);

        $routes = $sut->getRoutesByPHPNamespace('Basic');
        $this->assertRoutesMatch(['/route', '/route2'], $routes);
    }

    public function testCreateFromRouterEmpty(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $sut    = RoutesByNamespace::createFromRouter($router);

        $this->expectException(MissingMappingException::class);
        $sut->getRoutesByPHPNamespace('unknown');
    }

    public function testEmpty(): void
    {
        $this->expectException(MissingMappingException::class);
        $sut = new RoutesByNamespace();
        $sut->getRoutesByPHPNamespace('unknown');
    }
}
