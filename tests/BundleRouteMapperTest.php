<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Xiian\BundleRoute\BundleRouteMapper;
use Xiian\BundleRoute\MissingMappingException;
use Xiian\BundleRoute\Routing\RoutesByNamespace;
use Xiian\BundleRoute\Twig\BundleRouteExtension;
use Xiian\BundleRoute\Twig\TwigNamespaceMapper;

/**
 * @coversDefaultClass \Xiian\BundleRoute\BundleRouteMapper
 */
class BundleRouteMapperTest extends TestCase
{
    protected BundleRouteMapper $sut;

    protected RoutesByNamespace $routesByNamespace;

    protected TwigNamespaceMapper $twigNamespaceMapper;

    public function setUp(): void
    {
        $this->routesByNamespace   = new RoutesByNamespace();
        $this->twigNamespaceMapper = new TwigNamespaceMapper();

        $this->sut = new BundleRouteMapper(
            $this->routesByNamespace,
            $this->twigNamespaceMapper
        );
    }

    public function testGetRoutesByPHPNamespace(): void
    {
        $this->routesByNamespace->addRouteNamespace('App\Controller\Bob', 'bob', new Route('/bob'));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));

        $out = $this->sut->getRoutesByPHPNamespace('App\Controller\Bob');
        $this->assertEquals(['bob'], array_keys($out));
    }

    public function testGetRoutesByPHPNamespaceWithNoRoutes(): void
    {
        $this->expectException(MissingMappingException::class);
        $this->sut->getRoutesByPHPNamespace('App\Controller\Bob');
    }

    public function testGetRoutesByTwigNamespaceWithMissingMappingOfTwigNS(): void
    {
        $out = $this->sut->getRoutesByTwigNamespace('Twiggy');
        $this->assertEquals([], $out);
    }

    public function testGetRoutesByTwigNamespaceWithMissingMappingOfPHPNs(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');

        $this->expectException(MissingMappingException::class);
        $this->expectExceptionMessageMatches('~App\\\\Controller\\\\Twiggy~');
        $this->sut->getRoutesByTwigNamespace('Twiggy');
    }

    public function testGetRoutesByTwigNamespaceWithValidValues(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');
        $twiggyRoute1     = new Route('/bob');
        $twiggyRoute1Name = 'bob';
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', $twiggyRoute1Name, $twiggyRoute1);
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));

        $out = $this->sut->getRoutesByTwigNamespace('Twiggy');
        $this->assertEquals([
            $twiggyRoute1Name => $twiggyRoute1,
        ], $out);
    }

    public function testGetRoutesByTwigPathWithMissingMappingOfTwigNS(): void
    {
        $out = $this->sut->getRoutesByTwigPath('@Twiggy/Path/file.twig');
        $this->assertEquals([], $out);
    }

    public function testGetRoutesByTwigPathWithMissingMappingOfPHPNs(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');

        $this->expectException(MissingMappingException::class);
        $this->expectExceptionMessageMatches('~App\\\\Controller\\\\Twiggy~');
        $this->sut->getRoutesByTwigPath('@Twiggy/Path/file.twig');
    }

    public function testGetRoutesByTwigPathWithInvalidTwigNamespace(): void
    {
        $out = $this->sut->getRoutesByTwigPath('Twiggy/Path/file.twig');
        $this->assertEquals([], $out);
    }

    public function testGetRoutesByTwigPathWithValidValues(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');
        $twiggyRoute1     = new Route('/bob');
        $twiggyRoute1Name = 'bob';
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', $twiggyRoute1Name, $twiggyRoute1);
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));

        $out = $this->sut->getRoutesByTwigPath('@Twiggy/Path/file.twig');
        $this->assertEquals([
            $twiggyRoute1Name => $twiggyRoute1,
        ], $out);
    }

    public function testGetBundleRouteWithMissingMappingOfTwigNS(): void
    {
        $out = $this->sut->getBundleRoute('test_route', '@Twiggy/Path/file.twig');
        $this->assertEquals('test_route', $out);
    }

    public function testGetBundleRouteWithMissingMappingOfPHPNs(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');

        $this->expectException(MissingMappingException::class);
        $this->expectExceptionMessageMatches('~App\\\\Controller\\\\Twiggy~');
        $this->sut->getBundleRoute('test_route', '@Twiggy/Path/file.twig');
    }

    public function testGetBundleRouteWithInvalidTwigNamespace(): void
    {
        $out = $this->sut->getBundleRoute('test_route', 'Twiggy/Path/file.twig');
        $this->assertEquals('test_route', $out);
    }

    public function testGetBundleRouteWithValidValuesButNoRoutes(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');
        $twiggyRoute1     = new Route('/bob');
        $twiggyRoute1Name = 'bob';
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', $twiggyRoute1Name, $twiggyRoute1);
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));

        $out = $this->sut->getBundleRoute('test_route', '@Twiggy/Path/file.twig');
        $this->assertEquals('test_route', $out);
    }

    public function testGetBundleRouteWithValidValuesNotPrefixed(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'bob', new Route('/bob'));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'rob', new Route('/rob'));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'mob', new Route('/mob'));

        $out = $this->sut->getBundleRoute('bob', '@Twiggy/Path/file.twig');
        $this->assertEquals('bob', $out);
    }

    public function testGetBundleRouteWithValidValuesPrefixed(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'twiggy_bob', new Route('/bob', options: [BundleRouteExtension::OPTION_KEY => true]));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'twiggy_rob', new Route('/rob', options: [BundleRouteExtension::OPTION_KEY => true]));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'twiggy_mob', new Route('/mob', options: [BundleRouteExtension::OPTION_KEY => true]));

        $out = $this->sut->getBundleRoute('bob', '@Twiggy/Path/file.twig');
        $this->assertEquals('twiggy_bob', $out);
    }

    public function testGetBundleRouteWithValidValuesPrefixedButAlsoCommonPrefixBefore(): void
    {
        $this->twigNamespaceMapper->addNamespaceMapping('TwiggyBundle', 'App\Controller\Twiggy');
        $this->routesByNamespace->addRouteNamespace('App\Controller\Tom', 'tom', new Route('/tom'));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'twiggy_name_bob', new Route('/bob', options: [BundleRouteExtension::OPTION_KEY => true]));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'twiggy_name_rob', new Route('/rob', options: [BundleRouteExtension::OPTION_KEY => true]));
        $this->routesByNamespace->addRouteNamespace('App\Controller\Twiggy', 'twiggy_name_mob', new Route('/mob', options: [BundleRouteExtension::OPTION_KEY => true]));

        $out = $this->sut->getBundleRoute('name_bob', '@Twiggy/Path/file.twig');
        $this->assertEquals('twiggy_name_bob', $out);
    }
}
