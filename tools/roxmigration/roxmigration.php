<?php

namespace Rox\Tools;

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
     * Private worker function to eitehr update the word code in the DB or to add it as a new one.
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
     * @throws \Exception
     */
    private function _writeWordCodeToDb($add, $code, $sentence, $description, $majorUpdate = false, $dnt = 'No', $priority = '5')
    {
        list($code,$sentence,$description,$dnt) = $this->EscapeVariables(array($code,$sentence,$description,$dnt));

        // Check if $dnt is either 'Yes' or 'No', if not throw exception
        $dnt = strtolower($dnt);
        if ($dnt != "'yes'" & $dnt != "'no'") {
            throw new \Exception("AddWordCode: Donottranslate has to be yes or no");
        }
        if (!is_numeric($priority)) {
            throw new \Exception("AddWordCode: Priority has to be numeric");
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
        catch(\Exception $e) {
            echo "Couldn't add/update wordcode " . $code . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            echo PHP_EOL;
        }
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
     * @throws \Exception
     */
    protected function AddWordCode($code, $sentence, $description, $dnt = 'No', $priority = '5')
    {
        if (strlen($description) < 15) {
            throw new \Exception("Description too short need at least 15 characters");
        }
        $this->_writeWordCodeToDb(true, $code, $sentence, $description, false, $dnt, $priority);
    }

    /*****
     * Updates a word code during a migration (English language only)
     *
     * @param string $code The new WordCode
     * @param string $sentence The 'translation'
     * @param string $description The description needs to be at least 15 characters long
     * @param bool $majorupdate The update is a major one rendering the old translation obsolete
     * @param string $dnt Sets the donottranslate flag (either 'yes' or 'no')
     * @param string $priority Sets the translation priority
     * @throws \Exception
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
SET `isarchived` = 0
WHERE `code` = " . $code
                    ;
        $this->execute($query);
    }


    /****
     * Remove the word code and all translations from the database
     *
     * @param string $code The WordCode to remove
     */
    protected function RemoveWordCode($code)
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
        $adapter= $this->getAdapter();
        $connection = $adapter->getConnection();

        // Escape everything
        $varSafe = array();
        foreach ($vars as $var)
        {
            $varSafe[] = $connection->quote($var);
        }
        return $varSafe;        
    }
}
