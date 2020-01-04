<?php

use Rox\Tools\RoxMigration;

class AddRequestColumnsForStatistics extends RoxMigration
{
    public function change()
    {
        $statistics = $this->table('stats');
        $statistics
            ->addColumn('NbRequestsSent', 'integer', ['after' => 'NbMessageRead'])
            ->addColumn('NbRequestsAccepted', 'integer', ['after' => 'NbRequestsSent'])
            ->save();
        $this->execute("UPDATE stats set created = date(created);");
    }
}
