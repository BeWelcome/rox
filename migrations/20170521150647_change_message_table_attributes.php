<?php

use Rox\Tools\RoxMigration;

class ChangeMessageTableAttributes extends RoxMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE
                messages
            MODIFY
                WhenFirstRead TIMESTAMP NULL;
        ");

    }
}
