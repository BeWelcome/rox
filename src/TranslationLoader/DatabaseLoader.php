<?php

namespace App\TranslationLoader;

use App\Entity\Word;
use Doctrine\ORM\EntityManager;
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
        /** @var Word $translations */
        $translations = $this->em->getRepository(Word::class)->findBy(['shortCode' => $locale]);

        $messages = [];
        $validators = [];
        /** @var Word $translation */
        foreach ($translations as $translation) {
            // hack for validations messages
            $code = $translation->getCode();
            if ($this->startsWith($code, 'search.location')) {
                $validators[$code] = $translation->getSentence();
            }
            $messages[$code] = $translation->getSentence();
        }

        $catalogue = new MessageCatalogue($locale, ['messages' => $messages, 'validators' => $validators]);

        return $catalogue;
    }

    public function startsWith($string, $startString)
    {
        $len = \strlen($startString);

        return substr($string, 0, $len) === $startString;
    }
}
