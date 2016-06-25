<?php

namespace Rox\I18n\Loader;

use Illuminate\Database\Connection;
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
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        // ignore $resource just load content of the table words for the $locale into the catalogue

        $sql = 'SELECT SQL_CACHE `code`, `Sentence` FROM `words` WHERE ShortCode = ? ORDER BY code';

        $stmt = $this->connection->getPdo()->prepare($sql);

        $stmt->execute([$locale]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = array_column($result, 'Sentence', 'code');

        $catalogue = new MessageCatalogue($locale, ['messages' => $messages]);

        return $catalogue;
    }
}
