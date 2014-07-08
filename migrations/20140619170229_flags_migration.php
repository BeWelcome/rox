<?php

use Phinx\Migration\AbstractMigration;

/*************************************
 * Class FlagsMigration
 *
 * Handles the update of the flags table
 *
 * See ticket: #2206
 *
 */
class FlagsMigration extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table("flags");
        $table->addColumn('Relevance', 'integer',
                    array( 'length' => 1,
                            'comment' => 'Relevance > 0 means the flag is in use.',
                            'after' => 'Description'))
                ->save();
        $this->execute("
UPDATE `flags` SET Name = 'NotAllowedToPostInForum', Relevance = 100 WHERE Name = 'NotAllowToPostInForum';
UPDATE `flags` SET Name = 'NotAllowedToPostInBlog' WHERE Name = 'NotAllowToPostInBlog';
UPDATE `flags` SET Name = 'AlwaysCheckSendMail' WHERE Name = 'AlwayCheckSendMail';
UPDATE `flags` SET Name = 'NotAllowedToWriteInChat' WHERE Name = 'NotAllowToWriteInChat';
");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("
UPDATE `flags` SET Name = 'NotAllowToPostInForum' WHERE Name = 'NotAllowedToPostInForum';
UPDATE `flags` SET Name = 'NotAllowToPostInBlog' WHERE Name = 'NotAllowedToPostInBlog';
UPDATE `flags` SET Name = 'AlwayCheckSendMail' WHERE Name = 'AlwaysCheckSendMail';
UPDATE `flags` SET Name = 'NotAllowToWriteInChat' WHERE Name = 'NotAllowedToWriteInChat';
");
        $table = $this->table("flags");
        $table->removeColumn('Relevance')
              ->save();
    }
}