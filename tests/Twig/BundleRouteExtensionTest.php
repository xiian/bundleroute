<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Test\Twig;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Xiian\BundleRoute\BundleRouteMapper;
use Xiian\BundleRoute\Twig\BundleRouteExtension;

/**
 * @coversDefaultClass BundleRouteExtension
 * @uses BundleRouteExtension::__construct
 */
class BundleRouteExtensionTest extends TestCase
{

    private BundleRouteExtension $sut;

    private BundleRouteMapper & Stub $bundleRouteMapper;

    protected function setUp(): void
    {
        $this->bundleRouteMapper = $this->createMock(BundleRouteMapper::class);
        $this->sut               = new BundleRouteExtension($this->bundleRouteMapper);
    }

    /**
     * @covers ::getFunctions
     */
    public function testGetFunctions(): void
    {
        $functions = $this->sut->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
        $this->assertSame('bundle_route', $functions[0]->getName());
        $this->assertSame([$this->sut, 'getBundleRoute'], $functions[0]->getCallable());
    }

    /**
     * @covers ::getFunctions
     */
    public function testGetFunctionWithCustomName(): void
    {
        $functionName = 'custom_name';

        $sut       = new BundleRouteExtension($this->bundleRouteMapper, $functionName);
        $functions = $sut->getFunctions();

        $this->assertCount(1, $functions);
        $this->assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
        $this->assertSame($functionName, $functions[0]->getName());
        $this->assertSame([$sut, 'getBundleRoute'], $functions[0]->getCallable());
    }

    /**
     * @covers ::getBundleRoute
     */
    public function testGetBundleRoute(): void
    {
        $route       = 'route';
        $twigSelf    = 'twigSelf';
        $mappedRoute = __METHOD__;

        $this->bundleRouteMapper
            ->expects($this->once())
            ->method('getBundleRoute')
            ->with($route, $twigSelf)
            ->willReturn($mappedRoute);

        $result = $this->sut->getBundleRoute($route, $twigSelf);

        $this->assertSame($mappedRoute, $result);
    }
}
