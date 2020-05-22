<?php

namespace App\TranslationLoader;

use App\Entity\Word;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * DatabaseLoader loads translations from the words table (into the SQL cache).
 *
 * @author shevek <bla@blafaselblubb.abcde.biz>
 */
class DatabaseLoader implements LoaderInterface
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Word[] $originals
     * @param string $code
     * @return array
     * @throws Exception
     */
    private function findOriginal($originals, $code, $lastPos)
    {
        $i = $lastPos;
        $original = false;
        while (!$original && $i < count($originals))
        {
            if ($originals[$i]->getCode() == $code) {
                $original = $originals[$i];
            }
            $i++;
        }
        if (false === $original) {
            // we didn't find any original for this code (weird!), so assume we keep the last pos for the next try
            $i = $lastPos;
        }

        return [$original, $i];
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
        return $this->em->getRepository(Word::class)
            ->findBy(['shortCode' => $locale, 'domain' => $domain], ['code' => 'ASC']);
    }

    private function loadTranslationsForLocale($locale, $domain)
    {
        /** @var Word[] $translations */
        $translations = $this->getTranslationsForLocale($locale, $domain);

        /** @var Word[] $originals */
        $originals = $this->getTranslationsForLocale('en', $domain);

        $lastPos = 0;
        $messages = [];
        foreach ($translations as $translation) {
            $code = $translation->getCode();
            $sentence = $translation->getSentence();

            list($original, $lastPos)  = $this->findOriginal($originals, $code, $lastPos);
            if (false !== $original) {
                if ($original->getMajorUpdate() > $translation->getUpdated())
                {
                    // If english text has been updated and marked as major use the english text
                    $messages[$code] = $original->getSentence();
                } else {
                    $messages[$code] = $sentence;
                }
            }
        }

        $catalogue = new MessageCatalogue($locale, [
            $domain => $messages
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

        $catalogue = new MessageCatalogue('en', [
            $domain => $messages
        ]);

        return $catalogue;
    }

    public function startsWith($string, $startString)
    {
        $len = \strlen($startString);

        return substr($string, 0, $len) === $startString;
    }
}
