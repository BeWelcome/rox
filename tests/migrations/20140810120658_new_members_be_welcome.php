<?php

use Phinx\Migration\AbstractMigration;
use Rox\Tools;

/*************************************
 * Class NewMembersBeWelcome
 *
 * Adds a column to members to show the number of messages a member got from the New Members Be Welcome team
 *
 *
 * See ticket: #2206
 *
 */
class NewMembersBeWelcome extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $members = $this->table("members");
        $members->addColumn("bewelcomed", "integer");
        $members->save();

        $this->execute("
            INSERT INTO
                rights
            SET
                Name = 'NewMembersBeWelcome',
                Description = 'Members with that right have access to the New Members BeWelcome Tool. Level = 1, Scope = \"All\"'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $members = $this->table("members");
        $members->removeColumn("bewelcomed");
        $members->save();

        $this->execute("
            DELETE FROM
                rights
            WHERE
                Name = 'NewMembersBeWelcome'");
    }
}