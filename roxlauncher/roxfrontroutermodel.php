<?php

class RoxFrontRouterModel extends RoxModelBase
{

    function getPossibleUrlLanguage($urlheadercode = false)
    {

// Uncomment briefly this line in case you have problem with it, save, log in BeWelcome, and add again the comment in this line
// return false ;

        return false;
    } // end of getPossibleUrlLanguage


    function getLanguage($langcode = false)
    {
        if (!$langcode) {
            return false;
        } else {
            if (is_numeric($langcode)) {
                return $this->singleLookup("
                    SELECT
                        languages.id AS id,
                        languages.ShortCode AS ShortCode
                    FROM
                        languages
                    WHERE
                        languages.id = '" . $this->dao->escape($langcode) . "'");
            } else {
                return $this->singleLookup("
                    SELECT
                        languages.id AS id,
                        languages.ShortCode AS ShortCode
                    FROM
                        languages
                    WHERE
                        languages.ShortCode = '" . $this->dao->escape($langcode) . "'");
            }
        }
    }
}
