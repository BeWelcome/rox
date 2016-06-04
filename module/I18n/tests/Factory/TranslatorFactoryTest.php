<?php

namespace Rox\I18n\Factory;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TranslatorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $factory = new TranslatorFactory();

        $container = $this->getMock(ContainerInterface::class);

        $factory->__invoke($container);
    }
}
