<?php

namespace Rox\Tools;

use App\Doctrine\DomainType;
use Exception;
use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Adapter\PdoAdapter;
use Phinx\Db\Adapter\TimedOutputAdapter;
use Phinx\Migration\AbstractMigration;

/****
 * Class RoxMigration
 * @package Rox
 *
 * Adds methods to the phinx migration class to enable easy adding/removal of word codes as part of migrations
 *
 */
class RoxMigration extends AbstractMigration
{
    /***
     * Private worker function to either update the word code in the DB or to add it as a new one.
     *
     * Basically the same query but the created column is handled differently
     *
     * @param bool $add true means we add a word codee
     * @param string $code
     * @param string $sentence
     * @param string $description
     * @param bool $majorUpdate
     * @param string $dnt
     * @param string $priority
     * @throws Exception
     */
    private function _writeWordCodeToDb($add, $code, $sentence, $description, $majorUpdate = false, $dnt = 'No', $priority = '5')
    {
        list($code,$sentence,$description,$dnt) = $this->EscapeVariables(array($code,$sentence,$description,$dnt));

        // Check if $dnt is either 'Yes' or 'No', if not throw exception
        $dnt = strtolower($dnt);
        if ($dnt != "'yes'" & $dnt != "'no'") {
            throw new Exception("AddWordCode: Donottranslate has to be yes or no");
        }
        if (!is_numeric($priority)) {
            throw new Exception("AddWordCode: Priority has to be numeric");
        }

        if ($add) {
            $query = "INSERT";
        } else {
            $query = "REPLACE";
        }
        $query .= " INTO
                words
            SET
                code = " . $code . ",
                Shortcode = 'en',
                sentence = " . $sentence . ",
                updated = NOW(),
                donottranslate = " . $dnt . ",
                IdLanguage = 0,
                Description = " . $description . ",
                IdMember = 1,";
        if ($add) {
            $query .= "created = NOW(),";
        }
        $query .= "
                TranslationPriority = " . $priority . ",
                isarchived = NULL
            ";
        if ($majorUpdate) {
            $query .= ", majorupdate = NOW()";
        }
        try {
            $this->execute($query);
        }
        catch(Exception $e) {
            echo "Couldn't add/update wordcode " . $code . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            echo PHP_EOL;
        }
    }

    /***
     * Private worker function to either update the word code in the DB or to add it as a new one.
     *
     * Basically the same query but the created column is handled differently
     *
     * @param string $code
     * @param string $sentence
     * @param string $description
     * @param $domain
     */
    private function _writeTranslationToDb($code, $sentence, $description, $domain)
    {
        list($code,$sentence,$description,$domain) = $this->EscapeVariables(array($code,$sentence,$description,$domain));

        $query = "INSERT INTO
                words
            SET
                code = " . $code . ",
                Shortcode = 'en',
                sentence = " . $sentence . ",
                updated = NOW(),
                donottranslate = 'No',
                IdLanguage = 0,
                Description = " . $description . ",
                IdMember = 1,
                created = NOW(),
                TranslationPriority = 5,
                isarchived = NULL,
                majorupdate = NOW(),
                domain = " . $domain;
        $this->execute($query);
    }

    /*****
     * Adds a word code during a migration (English language only)
     *
     * If the word code already exists an PDO exception will be thrown
     *
     * @param string $code The new WordCode
     * @param string $sentence The 'translation'
     * @param string $description The description needs to be at least 15 characters long
     * @param string $dnt Sets the donottranslate flag (either 'yes' or 'no')
     * @param string $priority Sets the translation priority
     * @throws Exception
     */
    protected function AddWordCode($code, $sentence, $description, $dnt = 'No', $priority = '5')
    {
//        if (strlen($description) < 15) {
//            throw new \Exception("Description too short need at least 15 characters");
//        }
        $this->_writeWordCodeToDb(true, $code, $sentence, $description, false, $dnt, $priority);
    }

    /*****
     * Updates a word code during a migration (English language only)
     *
     * @param string $code The new WordCode
     * @param string $sentence The 'translation'
     * @param string $description The description needs to be at least 15 characters long
     * @param bool $majorUpdate The update is a major one rendering the old translation obsolete
     * @param string $dnt Sets the donottranslate flag (either 'yes' or 'no')
     * @param string $priority Sets the translation priority
     * @throws Exception
     */
    protected function UpdateWordCode($code, $sentence, $description, $majorUpdate = false, $dnt = 'No', $priority = '5')
    {
        $this->_writeWordCodeToDb(false, $code, $sentence, $description, $majorUpdate, $dnt, $priority);
    }

    /****
     * Archive the wordcode and all translations from the database
     *
     * Generally used in up-migrations only
     *
     * @param string $code The WordCode to archive
     */
    protected function ArchiveWordCode($code)
    {
        list($code) = $this->EscapeVariables(array($code));
        $query = "
UPDATE `words`
SET `isarchived` = 1
WHERE `code` = " . $code
                    ;
        $this->execute($query);
    }

    /****
     * Archive the wordcode and all translations from the database
     *
     * Generally used in down-migrations only
     *
     * @param string $code The WordCode to archive
     */
    protected function UnarchiveWordCode($code)
    {
        list($code) = $this->EscapeVariables(array($code));
        $query = "
UPDATE `words`
SET `isarchived` = NULL
WHERE `code` = " . $code
                    ;
        $this->execute($query);
    }


    /****
     * Remove the word code and all translations from the database
     *
     * @param string $code The WordCode to remove
     * @param null $sentence
     * @param null $description
     */
    protected function RemoveWordCode($code, $sentence = null, $description = null)
    {
        list($code) = $this->EscapeVariables(array($code));
        $query = "DELETE FROM words WHERE code = " . $code;
        $this->execute($query);
    }

    /****
     * Escape query variables
     *
     * @param array $vars Collection of raw variables
     * @return array Collection of escaped variables
     */
    protected function EscapeVariables($vars)
    {
        /** @var TimedOutputAdapter $timedOutputAdapter */
        $timedOutputAdapter = $this->getAdapter();

        /** @var MysqlAdapter $adapter */
        $adapter = $timedOutputAdapter->getAdapter();
        $connection = $adapter->getConnection();

        // Escape everything
        $varSafe = array();
        foreach ($vars as $var)
        {
            $varSafe[] = $connection->quote($var);
        }
        return $varSafe;
    }

    /**
     * @param $oldCode
     * @param $newCode
     */
    protected function RenameWordCode($oldCode, $newCode)
    {
        $statement = $this->getAdapter()->getConnection()->prepare("UPDATE words SET code = :newCode WHERE code = :oldCode");
        $statement->execute([
            ':oldCode' => $oldCode,
            ':newCode' => $newCode,
        ]);
    }

    /**
     * @param $oldCode
     * @param $newCode
     */
    protected function RevertRenameWordCode($oldCode, $newCode)
    {
        // To be used in the down method of a migration (renaming RenameWordCode to RevertRenameWordCode for easier maintenance)
        $this->RenameWordCode($newCode, $oldCode);
    }

    /*****
     * Adds a word code during a migration (English language only)
     *
     * If the word code already exists an PDO exception will be thrown
     *
     * @param string $code The new WordCode
     * @param string $sentence The 'translation'
     * @param string $description The description needs to be at least 15 characters long
     * @param string $domain
     * @throws Exception
     */
    protected function addTranslation($code, $sentence, $description, $domain = DomainType::ICU_MESSAGES)
    {
        $this->_writeTranslationToDb($code, $sentence, $description, $domain);
    }

    /**
     * @param $oldCode
     * @param $newCode
     */
    protected function copyTranslation($oldCode, $newCode)
    {
        $statement = $this->getAdapter()->getConnection()->prepare("INSERT INTO words (code, shortcode, sentence, created, updated, donottranslate, idLanguage, description, idMember, TranslationPriority, isArchived, majorUpdate, domain)
            SELECT :newCode, shortcode, sentence, created, updated, donottranslate, idLanguage, description, idMember, TranslationPriority, isArchived, majorUpdate, domain
            FROM words 
            WHERE code = :oldCode");
        $statement->execute([
            ':oldCode' => $oldCode,
            ':newCode' => $newCode,
        ]);
    }

    /**
     * @param $oldCode
     * @param $newCode
     */
    protected function undoCopyTranslation($oldCode, $newCode)
    {
        // Easier maintenance ignore oldCode
        $statement = $this->getAdapter()->getConnection()->prepare("DELETE FROM words  WHERE code = :newCode");
        $statement->execute([
            ':newCode' => $newCode,
        ]);
    }

}
