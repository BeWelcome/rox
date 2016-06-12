<?php

namespace Rox\Core\Loader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\AutowirePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConfigLoader
{
    public function __invoke(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AutowirePass());

        $loader = new YamlFileLoader($container, new FileLocator(getcwd()));

        $environment = $container->getParameter('kernel.environment');

        $rootConfigs = glob(sprintf('config/{,*.}{global,%s,local}.yml', $environment), GLOB_BRACE);

        $files = array_merge($rootConfigs, glob('module/*/config/*.yml'));

        // Remove routes.yml files, because they are read by a different yml loader.
        $files = preg_grep('/routes(\.global)?\.yml$/', $files, PREG_GREP_INVERT);

        foreach ($files as $file) {
            $loader->load($file);
        }
    }
}
