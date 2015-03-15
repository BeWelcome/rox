<?php

use Phinx\Migration\AbstractMigration;

/*****************
 * Class ArchiveCommentNotificationWordcodes
 *
 * Archive no longer used wordcodes for comment notification
 *
 * See ticket: 2230
 */

class ArchiveCommentNotificationWordcodes extends Rox\Tools\RoxMigration
{        /**
     * Migrate Up.
     */
    public function up()
    {
        $this->ArchiveWordCode('message_profile_comment');
        $this->ArchiveWordCode('message_profile_comment_update');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->UnarchiveWordCode('message_profile_comment');
        $this->UnarchiveWordCode('message_profile_comment_update');
    }
}