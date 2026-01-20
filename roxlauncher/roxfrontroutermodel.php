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
        }

        return $this->singleLookup("
            SELECT
                language.ShortCode AS ShortCode
            FROM
                language
            WHERE
                language.ShortCode = '" . $this->dao->escape($langcode) . "'");
    }
}
