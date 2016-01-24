<?php

use Phinx\Migration\AbstractMigration;

class LoginMessagesTables extends Rox\Tools\RoxMigration
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
        $loginMessages = $this->table('login_messages');
        $loginMessages
            ->addColumn('text', 'string')
            ->addColumn('created', 'datetime')
            ->create();
        $loginMessagesAcknowledged = $this->table('login_messages_acknowledged',
            array('id' => false, 'primary_key' => array('messageId', 'memberId')));
        $loginMessagesAcknowledged
            ->addColumn('messageId', 'integer')
            ->addColumn('memberId', 'integer')
            ->addColumn('acknowledged', 'boolean')
            ->create();
        $this->execute("INSERT INTO `login_messages` (`id`, `text`) VALUES (NULL, 'BeWelcome has just been updated please check the community news')");
    }
}