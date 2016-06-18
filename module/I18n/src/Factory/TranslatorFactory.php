<?php

namespace Rox\I18n\Factory;

use Illuminate\Database\Connection;
use Rox\I18n\Loader\DatabaseLoader;
use Rox\I18n\Service\LanguageService;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\MessageSelector;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $translator = new Translator($container, new MessageSelector(), [
            DatabaseLoader::class => [
                'database',
            ],
        ], [
            'debug' => $container->getParameter('kernel.debug'),
            'cache_dir' => $container->getParameter('kernel.cache_dir'),
        ]);

        // We need to load each language into the translator as a 'resource.'
        // This doesn't fetch any translations - it simply tells the service
        // which languages are available from the database loader. Also note
        // that it will only fetch the translations from the database on the
        // first request for that language and cache it to a file for
        // subsequent requests.

        /** @var LanguageService $languageService */
        $languageService = $container->get(LanguageService::class);

        // Initialise the Eloquent database before attempting to get the
        // languages below via getAvailableLanguages. This class is called
        // before same method is called in Application.php
        $container->get(Connection::class);

        $languages = $languageService->getAvailableLanguages();

        foreach ($languages as $language) {
            $translator->addResource('database', null, $language->ShortCode);
        }

        $translator->setFallbackLocales([
            $container->getParameter('kernel.default_locale'),
        ]);

        return $translator;
    }
}
