<?php


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
            ->addColumn('step', 'float', [
                'comment' => 'Daily increment defined on initialization of slider'
            ])
            ->addColumn('current', 'float', [
                'comment' => 'Current value used in search result to adapt order'
            ])
            ->addColumn('initialized', 'timestamp', [
                'comment' => 'The date when the slider was last initialized'
            ])
            ->create();
    }
}
