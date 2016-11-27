<?php

namespace AppBundle\TranslationLoader;

use Doctrine\ORM\EntityManager;
use PDO;
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
        $connection = $this->em->getConnection();

        $sql = 'SELECT SQL_CACHE `code`, `Sentence` FROM `words` WHERE ShortCode = ? ORDER BY code';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $locale);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = array_column($result, 'Sentence', 'code');

        $catalogue = new MessageCatalogue($locale, ['messages' => $messages]);

        return $catalogue;
    }
}
