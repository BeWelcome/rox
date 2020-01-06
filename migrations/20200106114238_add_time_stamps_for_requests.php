<?php

use Rox\Tools\RoxMigration;

class AddTimeStampsForRequests extends RoxMigration
{
    public function change()
    {
        $request = $this->table('request');
        $request
            ->addColumn('created', 'datetime', [ 'after' => 'Status'])
            ->addColumn('updated', 'datetime', [ 'after' => 'created', 'null' => true])
            ->save();
        // Set initial values for the existing requests
        $this->execute("UPDATE request r, messages m SET r.created = m.created where r.created = '0000-00-00 00:00:00' and r.id = m.request_id;");
        $this->execute("UPDATE request r, messages m SET r.updated = m.updated where r.id = m.request_id;");
        $this->execute("UPDATE request r SET r.updated = NULL where r.created = r.updated;");
    }
}
