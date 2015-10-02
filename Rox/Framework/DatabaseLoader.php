<?php

namespace Rox\Framework;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
* DatabaseLoader loads translations from the words table (into the SQL cache).
*
* @author shevek <bla@blafaselblubb.abcde.biz>
*
*/
class DatabaseLoader implements LoaderInterface
{
    private $_dao;
    /**
    * {@inheritdoc}
    *
    * @api
    */
    public function load($resource, $locale, $domain = 'messages') {
        // ignore $resource just load content of the table words for the $locale into the catalogue

        $db_vars = \PVars::getObj('config_rdbms');
        if (!$db_vars) {
            throw new \PException('DB config error!');
        }
        $dao = \PDB::get($db_vars->dsn, $db_vars->user, $db_vars->password);
        $this->_dao =& $dao;

        $sql = "
            SELECT SQL_CACHE
              `code`,
              `Sentence`
            FROM
              `words`
            WHERE
              ShortCode = '" . $locale . "'
            ORDER BY
              code";

        $q = $this->_dao->query($sql);
        $rows = $q->numRows();
        $catalogue = new MessageCatalogue($locale);
        if ($rows <> 0) {
            while($row = $q->fetch(\PDB::FETCH_OBJ)) {
                $catalogue->set($row->code, $row->Sentence, $domain);
            };
        }
        return $catalogue;
    }
}