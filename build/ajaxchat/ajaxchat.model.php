<?php
/** 
 * Ajax Chat Model
 * 
 * @package ajaxchat
 * @author lemon-head
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class AjaxchatModel extends RoxModelBase
{
    public function getMessagesInRoom($chatroom_id, $lookback_time)
    {
        // TODO: what about the $lookback_time ?
        return $this->bulkLookup(
            "
SELECT chat_messages.*, members.Username as username
FROM chat_messages, members
WHERE chat_messages.chatroom_id = $chatroom_id
AND members.id = chat_messages.author_id
            "
        );
    }
    
    
    public function createMessageInRoom($chatroom_id, $author_id, $text)
    {
        // TODO: check for input sanity / avoid SQL injection
        // id is auto-generated (hopefully..)
        $text = mysql_real_escape_string($text);
        $this->singleLookup(
            "
INSERT INTO chat_messages
SET
    chatroom_id = $chatroom_id,
    author_id = $author_id,
    text = '$text',
    created = NOW(),
    updated = NOW()
            "
        );
    }
    
    
}



?>