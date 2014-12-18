<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class BootstrapSignupNewWordCodes
 *
 * New word codes needed for the redesign of the login widget
 *
 * See ticket: #
 *
 */
class BootstrapLoginNewWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('OrDivider', 'or', 'Login widget to separate login and signup');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('OrDivider');
    }
}