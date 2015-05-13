<?php

use Phinx\Migration\AbstractMigration;

class AddNotificationEnabledColumns extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     */
    public function change()
    {
        $table = $this->table('membersgroups');
        $table->addColumn('notificationsEnabled', 'boolean')
            ->save();
        $table = $this->table('members_threads_subscribed');
        $table->addColumn('notificationsEnabled', 'boolean')
            ->save();
        $table = $this->table('members_tags_subscribed');
        $table->addColumn('notificationsEnabled', 'boolean')
            ->save();
    }

}