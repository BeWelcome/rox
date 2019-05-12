<?php

use Rox\Tools\RoxMigration;

class AddPasswordResetTable extends RoxMigration
{
    /**
     * Add table for password reset keys
     */
    public function change()
    {
        $passwordReset = $this->table('passwordreset');
        $passwordReset
            ->addColumn('member_id', 'integer', [ 'length' => 11, 'null' => false])
            ->addColumn('token', 'string', [ 'length' => 128, 'null' => false])
            ->addColumn('generated', 'datetime', [ 'null' => false])
            ;
        $passwordReset->save();
    }
}
