<?php


use Rox\Tools\RoxMigration;

class MessageWhenFirstReadNullable extends RoxMigration
{
    public function change()
    {
        $messages = $this->table('messages');
        $messages->changeColumn('WhenFirstRead', 'timestamp', [
                'null' => true,
                'default' => null,
            ])
            ->save();
        $this->execute("UPDATE messages SET WhenFirstRead = NULL WHERE WhenFirstRead = '0000-00-00 00:00:00'");
    }
}
