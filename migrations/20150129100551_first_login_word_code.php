<?php

use Phinx\Migration\AbstractMigration;

/**
 * Class FirstLoginWordCode
 *
 * Add the word code for the flash notice shown after the first login
 *
 * See ticket: #2264
 *
 */
class FirstLoginWordCode extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('FirstLoginFirst', 'Welcome %s, as it is the first time you login please fill out your profile.', 'Flash notice shown on first login');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('FirstLoginFirst');
    }
}