<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Test\Twig;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Xiian\BundleRoute\MissingMappingException;
use Xiian\BundleRoute\Twig\TwigNamespaceMapper;

/**
 * @coversDefaultClass \Xiian\BundleRoute\Twig\TwigNamespaceMapper
 */
class TwigNamespaceMapperTest extends TestCase
{
    public function testCreateFromKernelWithMultiple(): void
    {
        $bundleName      = 'CoatBundle';
        $bundleNamespace = 'App\Twig\Coat';

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getBundles')->willReturn([
            $this->createMockBundle($bundleName, $bundleNamespace),
            $this->createMockBundle('PackageBundle', 'App\Bundle\Package'),
        ]);

        $sut = TwigNamespaceMapper::createFromKernel($kernel);
        $out = $sut->getPHPNamespaceFromTwigNamespace('Coat');
        $this->assertEquals($bundleNamespace, $out);
    }

    public function testCreateFromKernelWithOne(): void
    {
        $bundleName      = 'CoatBundle';
        $bundleNamespace = 'App\Twig\Coat';

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getBundles')->willReturn([
            $this->createMockBundle($bundleName, $bundleNamespace),
        ]);

        $sut = TwigNamespaceMapper::createFromKernel($kernel);
        $out = $sut->getPHPNamespaceFromTwigNamespace('Coat');
        $this->assertEquals($bundleNamespace, $out);
    }

    public function testGetPHPNamespaceFromTwigNamespaceEmpty(): void
    {
        $sut = new TwigNamespaceMapper();
        $this->expectException(MissingMappingException::class);
        $sut->getPHPNamespaceFromTwigNamespace('');
    }

    public function testGetPHPNamespaceFromTwigNamespaceWithOneMatch(): void
    {
        $sut = new TwigNamespaceMapper();
        $sut->addNamespaceMapping('bundlenameBundle', 'phpnamespace');

        $out = $sut->getPHPNamespaceFromTwigNamespace('bundlename');
        $this->assertEquals('phpnamespace', $out);
    }

    public function testGetTwigNamespaceFromPathWithInvalidPath(): void
    {
        $sut = new TwigNamespaceMapper();
        $this->expectException(InvalidArgumentException::class);
        $sut->getTwigNamespaceFromPath('invalid/path');
    }

    public function testGetTwigNamespaceFromPathWithValidPath(): void
    {
        $sut = new TwigNamespaceMapper();
        $out = $sut->getTwigNamespaceFromPath('@Valid/Path/thing.twig');
        $this->assertEquals('Valid', $out);
    }

    private function createMockBundle(string $bundleName, string $bundleNamespace): BundleInterface&MockObject
    {
        $testBundle = $this->createMock(BundleInterface::class);
        $testBundle->method('getName')->willReturn($bundleName);
        $testBundle->method('getNamespace')->willReturn($bundleNamespace);

        return $testBundle;
    }
}
