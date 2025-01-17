<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Twig;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\KernelInterface;
use Xiian\BundleRoute\MissingMappingException;

class TwigNamespaceMapper
{
    public function __construct(
        protected array $bundleNamespaceMapping = []
    ) {
    }

    public function addNamespaceMapping(string $bundleName, string $phpNamespace): void
    {
        $this->bundleNamespaceMapping[$bundleName] = $phpNamespace;
    }

    public static function createFromKernel(KernelInterface $kernel): self
    {
        $that = new self();
        foreach ($kernel->getBundles() as $bundle) {
            $that->addNamespaceMapping($bundle->getName(), $bundle->getNamespace());
        }

        return $that;
    }

    public function getPHPNamespaceFromTwigNamespace(string $twigNamespace): string
    {
        $bundleName = $twigNamespace . 'Bundle';

        if (!array_key_exists($bundleName, $this->bundleNamespaceMapping)) {
            throw MissingMappingException::forMapping($twigNamespace);
        }

        return $this->bundleNamespaceMapping[$bundleName];
    }

    public function getTwigNamespaceFromPath(string $twigPath): string
    {
        if (!str_starts_with($twigPath, '@')) {
            throw new InvalidArgumentException('Twig path must start with @');
        }

        return substr($twigPath, 1, strpos($twigPath, '/') - 1);
    }
}
