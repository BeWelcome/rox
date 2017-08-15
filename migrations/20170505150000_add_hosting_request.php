<?php

use Rox\Tools\RoxMigration;

class AddHostingRequest extends RoxMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $request = $this->table('request');
        $request->addColumn('arrival', 'datetime')
            ->addColumn('departure', 'datetime')
            ->addColumn( 'flexible', 'boolean')
            ->addColumn('number_of_travellers', 'integer', [
                'signed' => false
            ])
            ->addColumn('status', 'integer', [
                'length' => 1,
                'signed' => false,
                'default' => 0
            ])
            ->create();
    }
}
