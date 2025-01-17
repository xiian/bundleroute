<?php

declare(strict_types=1);

namespace Xiian\BundleRoute;

use InvalidArgumentException;
use Xiian\BundleRoute\Routing\RoutesByNamespace;
use Xiian\BundleRoute\Twig\BundleRouteExtension;
use Xiian\BundleRoute\Twig\TwigNamespaceMapper;

class BundleRouteMapper
{
    public function __construct(
        private readonly RoutesByNamespace   $routesByNamespace,
        private readonly TwigNamespaceMapper $twigNamespaceMapper,
    ) {
    }

    public function getBundleRoute(string $route, string $twigSelf): string
    {
        $nsRoutes = $this->getRoutesByTwigPath($twigSelf);

        $prefix       = StringUtils::getPrefixForStrings(array_keys($nsRoutes));
        $prefixLength = strlen($prefix);

        while($prefixLength > 0) {
            foreach ($nsRoutes as $routeName => $r) {
                if (substr($routeName, $prefixLength) === $route) {
                    // We have to be explicit that we want to allow the aliasing
                    if ($r->hasOption(BundleRouteExtension::OPTION_KEY)) {
                        return $routeName;
                    }
                }
            }
            $prefixLength--;
        }

        return $route;
    }

    public function getRoutesByPHPNamespace(string $phpNamespace): array
    {
        return $this->routesByNamespace->getRoutesByPHPNamespace($phpNamespace);
    }

    public function getRoutesByTwigNamespace(string $twigNamespace): array
    {
        try {
            $phpNamespace = $this->twigNamespaceMapper->getPHPNamespaceFromTwigNamespace($twigNamespace);
        } catch (MissingMappingException) {
            return [];
        }

        return $this->getRoutesByPHPNamespace($phpNamespace);
    }

    public function getRoutesByTwigPath(string $twigSelf): array
    {
        try {
            $twigNamespace = $this->twigNamespaceMapper->getTwigNamespaceFromPath($twigSelf);
        } catch (InvalidArgumentException) {
            return [];
        }

        return $this->getRoutesByTwigNamespace($twigNamespace);
    }

}
