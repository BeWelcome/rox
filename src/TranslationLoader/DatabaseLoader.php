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

    /** @var MessageCatalogue */
    private $originals;

    /** @var array DateTime */
    private $majorUpdates = [];

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->originals = new MessageCatalogue('en');
        $this->loadEnglishOriginals('messages');
        $this->loadEnglishOriginals('messages+intl-icu');
        $this->loadEnglishOriginals('validators');
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if ('en' === $locale) {
            return $this->originals;
        }

        return $this->loadTranslationsForLocale($locale, $domain);
    }

    private function getTranslationsForLocale($locale, $domain)
    {
        $repository = $this->em->getRepository(Word::class);
        $translations = $repository->getTranslationsForLocale($locale, $domain);

        return $translations;
    }

    private function loadTranslationsForLocale($locale, $domain)
    {
        $catalogue = new MessageCatalogue($locale);

        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale($locale, $domain);

        foreach ($translations as $translation) {
            $code = $translation->getCode();
            if ($this->originals->has($code, $domain)) {
                $majorUpdate = $this->originals->getMetadata($code, $domain);
                if ($majorUpdate <= $translation->getUpdated()) {
                    $catalogue->set($code, $translation->getSentence());
                } else {
                    $catalogue->set($code, $this->originals->get($code, $domain));
                }
            }
        }

        return $catalogue;
    }

    private function loadEnglishOriginals($domain): void
    {
        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale('en', $domain);

        foreach ($translations as $translation) {
            $code = $translation->getCode();
            $sentence = $translation->getSentence();
            $this->originals->set($code, $sentence, $domain);
            $this->originals->setMetadata($code, $translation->getMajorUpdate(), $domain);
        }
    }
}
