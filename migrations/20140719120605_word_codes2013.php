<?php

use Phinx\Migration\AbstractMigration;
use Rox\RoxMigration;

class WordCodes2013 extends RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->AddWordCode('AdminTreasurerNoCountry', '');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('AdminTreasurerNoCountry');
    }
}