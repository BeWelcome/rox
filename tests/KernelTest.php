<?php

namespace App\Tests;

use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class KernelTest extends TestCase
{
    public function testTheTranslatorIsWarmedOnlyByUxTranslator(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('translation.warmer', new Definition());
        $container->setDefinition('ux.translator.cache_warmer.translations_cache_warmer', new Definition());

        new Kernel('test', true)->process($container);

        $this->assertFalse($container->hasDefinition('translation.warmer'));
        $this->assertTrue($container->hasDefinition('ux.translator.cache_warmer.translations_cache_warmer'));
    }

    public function testTheTranslationWarmerStaysWithoutUxTranslator(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition('translation.warmer', new Definition());

        new Kernel('test', true)->process($container);

        $this->assertTrue($container->hasDefinition('translation.warmer'));
    }
}
