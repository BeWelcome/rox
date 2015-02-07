<?php

/**
* Class FooterWordCodes
*
* Add the word code for the flash notice shown after the first login
*
* See ticket: #2266
*
*/
class FooterWordCodes extends Rox\Tools\RoxMigration
{
    /**
    * Migrate Up.
    */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode("FooterSiteDisplayed", 'The site is currently displayed in %1$s', "Shown in the footer. The parameter will be the language dropdown.");
    }

    /**
    * Migrate Down.
    */
    public function down()
    {
        $this->RemoveWordCode('FooterSiteDisplayed');
    }
}
