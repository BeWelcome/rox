<?php

namespace App\TranslationLoader;

use App\Doctrine\TranslationAllowedType;
use App\Entity\Word;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * DatabaseLoader loads translations from the words table (into the SQL cache).
 *
 * @author shevek <bla@blafaselblubb.abcde.biz>
 */
class DatabaseLoader implements LoaderInterface
{
    /** @var array MessageCatalogue */
    private $originals = [];

    private bool $databaseUnavailable = false;

    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug = false,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
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
        if ($this->databaseUnavailable) {
            return [];
        }

        try {
            return $this->em->getRepository(Word::class)->getTranslationsForLocale($locale, $domain);
        } catch (TableNotFoundException) {
            // Fresh database without a schema yet (e.g. the first bin/console call that is
            // about to create it builds the cache and warms the translations). Fall back to
            // the file based translations; the next cache:clear picks up the database ones.
            return [];
        } catch (ConnectionException $exception) {
            // Since symfony/ux-translator 3.6 building the container loads all catalogues
            // while var/translations doesn't exist yet (fresh checkout, CI), so tools like
            // lint:yaml would need a database. Allow that in debug (dev, test) only: a prod
            // cache must not be built without the database translations.
            if (!$this->debug) {
                throw $exception;
            }
            $this->databaseUnavailable = true;
            $this->logger->warning(
                'Database not reachable, translations are loaded from files only. Run cache:clear once the database is up.',
                ['exception' => $exception],
            );

            return [];
        }
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
                        if ($translationAllowed || str_starts_with($code, 'broadcast_')) {
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
