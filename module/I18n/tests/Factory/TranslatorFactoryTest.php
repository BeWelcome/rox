<?php

namespace Rox\I18n\Factory;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TranslatorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $factory = new TranslatorFactory();

        /** @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $factory->__invoke($container);
    }
}
