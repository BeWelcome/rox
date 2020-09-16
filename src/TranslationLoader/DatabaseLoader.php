<?php

namespace App\TranslationLoader;

use App\Controller\MessageController;
use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * DatabaseLoader loads translations from the words table (into the SQL cache).
 *
 * @author shevek <bla@blafaselblubb.abcde.biz>
 */
class DatabaseLoader implements LoaderInterface
{
    /** @var EntityManager */
    private $em;

    /** @var array MessageCatalogue */
    private $originals = [];

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if ('en' === $locale) {
            return $this->loadEnglishOriginals($domain);
        }

        return $this->loadTranslationsForLocale($locale, $domain);
    }

    private function getTranslationsForLocale($locale, $domain)
    {
        $repository = $this->em->getRepository(Word::class);
        $translations = $repository->findBy(['shortCode' => $locale, 'domain' => $domain], ['code' => 'ASC']);

        return $translations;
    }

    private function loadTranslationsForLocale($locale, $domain)
    {
        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale($locale, $domain);

        /** @var Word[] $originals */
        if (!isset($this->originals[$domain])) {
            $this->loadEnglishOriginals($domain);
        }
        $originals = $this->originals[$domain];

        $messages = [];
        foreach ($translations as $translation) {
            $code = $translation->getCode();
            $sentence = $translation->getSentence();
            $messages[$code] = $sentence;
        }

        $catalogue = new MessageCatalogue($locale, [
            $domain => array_merge($originals, $messages),
        ]);

        return $catalogue;
    }

    private function loadEnglishOriginals($domain)
    {
        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale('en', $domain);

        $messages = [];
        foreach ($translations as $translation) {
            $code = $translation->getCode();
            $sentence = $translation->getSentence();
            $messages[$code] = $sentence;
        }
        $this->originals[$domain] = $messages;

        $catalogue = new MessageCatalogue('en', [
            $domain => $messages,
        ]);

        return $catalogue;
    }
}
