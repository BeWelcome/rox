<?php

namespace App\TranslationLoader;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * DatabaseLoader loads translations from the words table (into the SQL cache).
 *
 * @author shevek <bla@blafaselblubb.abcde.biz>
 */
class DatabaseLoader implements LoaderInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var array MessageCatalogue */
    private $originals = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($resource, $locale, $domain = 'messages'): MessageCatalogue
    {
        if ('en' === $locale) {
            return $this->loadEnglishOriginals($domain);
        }

        return $this->loadTranslationsForLocale($locale, $domain);
    }

    private function getTranslationsForLocale($locale, $domain)
    {
        return $this->em->getRepository(Word::class)->getTranslationsForLocale($locale, $domain);
    }

    private function loadTranslationsForLocale($locale, $domain): MessageCatalogue
    {
        if (!isset($this->originals[$domain])) {
            $this->loadEnglishOriginals($domain);
        }
        $originals = $this->originals[$domain];

        $catalogue = new MessageCatalogue($locale);

        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale($locale, $domain);

        if (null !== $translations) {
            foreach ($translations as $translation) {
                $code = $translation->getCode();
                if ($originals->has($code, $domain)) {
                    $majorUpdate = $originals->getMetadata($code, $domain)['majorUpdate'];
                    if ($translation->getUpdated() < $majorUpdate) {
                        $catalogue->set($code, $originals->get($code, $domain), $domain);
                    } else {
                        $translationAllowedRaw = $originals->getMetadata($code, $domain)['translationAllowed'];
                        $translationAllowed = TranslationAllowedType::TRANSLATION_ALLOWED === $translationAllowedRaw;
                        if ($translationAllowed || 'broadcast_' === substr($code, 0, 10)) {
                            $catalogue->set($code, $translation->getSentence(), $domain);
                        } else {
                            $catalogue->set($code, $originals->get($code, $domain), $domain);
                        }
                    }
                }
            }
        }

        return $catalogue;
    }

    private function loadEnglishOriginals($domain): MessageCatalogue
    {
        if (!isset($this->originals[$domain])) {
            $this->originals[$domain] = new MessageCatalogue('en');
        }
        $catalogue = $this->originals[$domain];

        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale('en', $domain);

        if (null !== $translations) {
            foreach ($translations as $translation) {
                $code = $translation->getCode();
                $sentence = $translation->getSentence();
                $catalogue->set($code, $sentence, $domain);
                $metaData = [
                    'majorUpdate' => $translation->getMajorUpdate(),
                    'translationAllowed' => $translation->getTranslationAllowed(),
                ];
                $catalogue->setMetadata($code, $metaData, $domain);
            }
        }

        return $catalogue;
    }
}
