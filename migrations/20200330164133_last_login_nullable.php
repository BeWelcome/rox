<?php

use Rox\Tools\RoxMigration;

class LastLoginNullable extends RoxMigration
{
    public function up()
    {
        $members = $this->table('members');
        $members
            ->changeColumn('LastLogin', 'datetime', ['null' => true])
            ->save()
        ;
    }

    public function down()
    {
        $members = $this->table('members');
        $members
            ->changeColumn('LastLogin', 'datetime', ['null' => false])
            ->save()
        ;
    }
}
