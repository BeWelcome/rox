<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class BootstrapSignupNewWordCodes
 *
 * New word codes needed for the redesign of the signup
 *
 * See ticket: #2250
 *
 */
class BootstrapSignupNewWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('SignupIntroductionTitle', 'Where will my personal information be displayed?', 'Panel title for signup introduction');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('SignupIntroductionTitle');
    }
}