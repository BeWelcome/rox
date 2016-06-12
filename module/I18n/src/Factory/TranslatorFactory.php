<?php

namespace Rox\I18n\Factory;

use Locale;
use Rox\I18n\Loader\DatabaseLoader;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageSelector;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        //if (!isset($_SESSION['lang'])) {
            //$_SESSION['lang'] = 'en';
        //}

        $locale = Locale::getDefault();

        $lang = Locale::getPrimaryLanguage($locale);

        //$language = Locale::getDisplayLanguage($lang);

        $translator = new Translator($container, new MessageSelector(), [
            DatabaseLoader::class => [
                'database',
            ],
        ], [
            'debug' => true,
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
        ]);

        $translator->addResource('database', null, $lang);

        $translator->setFallbackLocales(['en']);

        //if ($_SESSION['lang'] !== 'en') {
            //$translator->setFallbackLocales(['en']);
        //}

        return $translator;
    }
}
