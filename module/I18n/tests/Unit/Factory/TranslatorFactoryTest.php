<?php

namespace Rox\I18n\Factory;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Rox\I18n\Service\LanguageService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TranslatorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $factory = new TranslatorFactory();

        /** @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        /** @var LanguageService|PHPUnit_Framework_MockObject_MockObject $languageService */
        $languageService = $this->createMock(LanguageService::class);

        $languageService
            ->expects($this->once())
            ->method('getAvailableLanguages')
            ->willReturn([]);

        $container
            ->expects($this->at(3))
            ->method('get')
            ->with(LanguageService::class)
            ->willReturn($languageService);

        $factory->__invoke($container);
    }
}
