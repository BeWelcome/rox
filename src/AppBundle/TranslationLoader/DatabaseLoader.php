<?php

namespace AppBundle\TranslationLoader;

use AppBundle\Entity\Word;
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
        $translations = $this->em->getRepository(Word::class)->findBy(['shortcode' => $locale]);

        $messages = [];
        foreach ($translations as $translation) {
            $messages[$translation->getCode()] = $translation->getSentence();
        }

        $catalogue = new MessageCatalogue($locale, ['messages' => $messages]);

        return $catalogue;
    }
}
