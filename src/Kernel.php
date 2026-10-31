<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        // ux-translator's cache warmer warms the translator itself (all locales) before it dumps
        // the JavaScript translations. Symfony's translation warmer would do the same a second
        // time, which reloads every catalogue and keeps both copies in memory: a prod
        // cache:clear then needs more than the 512 MB memory_limit.
        if ($container->hasDefinition('ux.translator.cache_warmer.translations_cache_warmer')) {
            $container->removeDefinition('translation.warmer');
        }
    }

    /**
     * @return list<string> An array of allowed values for APP_ENV
     */
    private function getAllowedEnvs(): array
    {
        return ['prod', 'dev', 'test'];
    }
}
