<?php

class RoxFrontRouterModel extends RoxModelBase
{

    function getPossibleUrlLanguage($urlheadercode = false)
    {

// Uncomment briefly this line in case you have problem with it, save, log in BeWelcome, and add again the comment in this line
// return false ;

        return $this->singleLookup(
            "SELECT 
                languages.id,
                ShortCode 
            FROM 
                urlheader_languages,languages
            WHERE 
                urlheader='" . $this->dao->escape($urlheadercode) . "' 
                AND languages.id=urlheader_languages.IdLanguage");
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
                        languages,
                        words
                    WHERE
                        languages.id = '" . $this->dao->escape($langcode) . "' AND
                        languages.id = words.Idlanguage AND
                        words.code = 'WelcomeToSignup'");
            } else {
                return $this->singleLookup("
                    SELECT
                        languages.id AS id,
                        languages.ShortCode AS ShortCode
                    FROM
                        languages,
                        words
                    WHERE
                        languages.ShortCode = '" . $this->dao->escape($langcode) . "' AND
                        languages.id = words.Idlanguage AND
                        words.code = 'WelcomeToSignup'");
            }
        }
    }
}
