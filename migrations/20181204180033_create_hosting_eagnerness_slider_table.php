<?php


use Phinx\Util\Literal;
use Rox\Tools\RoxMigration;

class CreateHostingEagnernessSliderTable extends RoxMigration
{
    public function change()
    {
        $hes = $this->table('hosting_eagerness_slider', [
            'comment' => 'Stores the hosting eagerness of members.',
            'collation' => 'utf8mb4_unicode_520_ci'
        ]);
        $hes
            ->addColumn('member_id', 'integer', [
                'comment' => 'Foreign key into the members table'
            ])
            ->addColumn('step', 'integer', [
                'comment' => 'Daily increment defined on initialization of slider'
            ])
            ->addColumn('current', 'integer', [
                'comment' => 'Current value used in search result to adapt order'
            ])
            ->addColumn('remaining', 'integer', [
                'comment' => 'The remaining hours for the boost'
            ])
            ->addColumn('initialized', 'datetime', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'The date when the slider was last initialized'
            ])
            ->addColumn('enddate', 'datetime', [
                'null' => false,
                'comment' => 'Only used for display purposes, stores the originally selected date the boost ends'
            ])
            ->addColumn('updated', 'datetime', [
                'null' => true,
                'default' => null,
                'update' => 'CURRENT_TIMESTAMP',
                'comment' => 'The date when the slider was last initialized'
            ])
            ->create();
    }
}
