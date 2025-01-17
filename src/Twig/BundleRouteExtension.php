<?php

declare(strict_types=1);

namespace Xiian\BundleRoute\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xiian\BundleRoute\BundleRouteMapper;

class BundleRouteExtension extends AbstractExtension
{
    public const string OPTION_KEY = __CLASS__;

    public function __construct(
        private readonly BundleRouteMapper $bundleRouteMapper,
        protected readonly string          $functionName = 'bundle_route',
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction($this->functionName, [$this, 'getBundleRoute']),
        ];
    }

    public function getBundleRoute(string $route, string $twigSelf): string
    {
        return $this->bundleRouteMapper->getBundleRoute($route, $twigSelf);
    }
}
