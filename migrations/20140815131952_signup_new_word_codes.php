<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class SignupNewWordCodes
 *
 * New word codes needed for signup due to the honey pot
 *
 * See ticket: #2240
 *
 */
class SignupNewWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('SignupErrorSomethingWentWrong', 'Sorry, something went wrong during signup', 'Message shown in case the honeypot is hit', 'yes');
        $this->AddWordCode('SignupSelectMotherTongue', 'Please select a language', 'Placeholder text for the language list dropdown');
        $this->AddWordCode('SignupErrorNoMotherTongue', 'Please select a mother tongue', 'Error message in case no mother tongue was selected');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('SignupErrorSomethingWentWrong');
        $this->RemoveWordCode('SignupSelectMotherTongue');
        $this->RemoveWordCode('SignupErrorNoMotherTongue');
    }
}