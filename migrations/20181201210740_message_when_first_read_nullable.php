<?php


use Rox\Tools\RoxMigration;

class MessageWhenFirstReadNullable extends RoxMigration
{
    public function up()
    {
        $messages = $this->table('messages');
        $messages->changeColumn('WhenFirstRead', 'timestamp', [
            'null' => true,
            'default' => null,
        ])
            ->save();
        $this->execute("UPDATE messages SET WhenFirstRead = NULL WHERE WhenFirstRead = '0000-00-00 00:00:00'");
    }

    public function down()
    {
        $messages = $this->table('messages');
        $messages->changeColumn('WhenFirstRead', 'timestamp', [
            'default' => '0000-00-00 00:00:00',
        ])
            ->save();
        $this->execute("UPDATE messages SET WhenFirstRead = '0000-00-00 00:00:00' WHERE WhenFirstRead IS NULL");
    }
}
