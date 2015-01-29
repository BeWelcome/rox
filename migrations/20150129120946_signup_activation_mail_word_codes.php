<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class SignupActivationMailWordCodes
 *
 * Add the word code for the flash notice shown after the first login
 *
 * See ticket: #2255
 *
 */
class SignupActivationMailWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode("SignupBodyActivationMail", 'Congratulations %1$s %2$s %3$s, you activated your profile on %4$s.<br /><br />Here\'s your username: %5$s.<br /><br />Have fun and BeWelcome.', "Text of the mail send during activation of the profile. Takes 4 parameter. First, second and last name and the name of the site.");
        $this->AddWordCode("SignupSubjectActivationMail", "Your profile is now activated", "Subject for the mail send during activation of the profile.");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('SignupBodyActivationMail');
        $this->RemoveWordCode('SignupSubjectActivationMail');
    }
}