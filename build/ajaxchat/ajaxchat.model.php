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
    public function getNowTime($timeshift = false)
    {
        if (!$timeshift){ 
            return $this->singleLookup(
                "
SELECT NOW() as now_time
                "
            )->now_time;
        } else {
            $timeshift = mysql_real_escape_string($timeshift);
            return $this->singleLookup(
                "
SELECT ADDTIME(NOW(), '$timeshift') as shifted_now_time
                "
            )->shifted_now_time;
        }
    }
    
    
    public function lookbackLimitDays() {
        return $this->getNowTime('-2 0:0:0');
    }
    
    public function lookbackLimitWeeks() {
        return $this->getNowTime('-12 0:0:0');
    }
    
    public function lookbackLimitMonths() {
        return $this->getNowTime('-50 0:0:0');
    }
    
    public function lookbackLimitForever() {
        return '0000-';
    }
    
    
    public function getMessagesInRoom($chatroom_id, $lookback_limit)
    {
        // echo 'lookback_limit = '.$lookback_limit;
        // echo implode('/',PRequest::get()->request);
        // print_r($lookback_limit);
        $chatroom_id = (int)$chatroom_id;
        
        // $lookback_limit is a javascript-created string timestamp + id
        // TODO: is the check $lookback_limit really robust enough?
        $lookback_limit = mysql_real_escape_string($lookback_limit);
        $messages_found = $this->bulkLookup(
            "
SELECT
    chat_messages.*,
    UNIX_TIMESTAMP(chat_messages.created)  AS unixtime_created,
    UNIX_TIMESTAMP(chat_messages.updated)  AS unixtime_updated,
    members.Username                       AS username
FROM
    chat_messages,
    members
WHERE
    chat_messages.chatroom_id = $chatroom_id  AND
    members.id                = chat_messages.author_id      AND
    chat_messages.updated     > '$lookback_limit'
            "
        );
        
        $messages = array();
        for ($i=0; $i<count($messages_found); ++$i) {
            if (strcmp($messages_found[$i]->updated, $lookback_limit) > 0) {
                $messages_found[$i]->text = htmlspecialchars($messages_found[$i]->text);
                $messages_found[$i]->created2 = date('d-m-Y H:i:s', $messages_found[$i]->unixtime_created);
                if (date('Y-m-d') == date('Y-m-d', $messages_found[$i]->unixtime_created))
                    $messages_found[$i]->created2 = date('H:i:s', $messages_found[$i]->unixtime_created);
                $messages[] = $messages_found[$i];
            }
        }
        
        return $messages;
    }
    
    
    public function createMessageInRoom($chatroom_id, $author_id, $text)
    {
        // TODO: check for input sanity / avoid SQL injection
        // id is auto-generated (hopefully..)
        $text = mysql_real_escape_string($text);
        $chatroom_id = (int)$chatroom_id;
        $author_id = (int)$author_id;
        
        $this->singleLookup(
            "
INSERT INTO
    chat_messages
SET
    chatroom_id = $chatroom_id,
    author_id   = $author_id,
    text        = '$text',
    created     = NOW(),
    updated     = NOW()
            "
        );
        
        return $this->singleLookup(
            "
SELECT
    chat_messages.*,
    members.Username as username
FROM
    chat_messages,
    members
WHERE
    members.id       = chat_messages.author_id  AND
    chat_messages.id = LAST_INSERT_ID()
            "
        );
    }
    
    
}



?>
