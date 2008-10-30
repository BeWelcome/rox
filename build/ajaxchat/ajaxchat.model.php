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
    function getNowTime($timeshift = false)
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
    
    
    function lookbackLimitDays() {
        return $this->getNowTime('-2 0:0:0');
    }
    
    function lookbackLimitWeeks() {
        return $this->getNowTime('-12 0:0:0');
    }
    
    function lookbackLimitMonths() {
        return $this->getNowTime('-50 0:0:0');
    }
    
    function lookbackLimitForever() {
        return '0000-';
    }
    
    function waitForMessagesInRoom($chatroom_id, $prev_message_id, $interval_milliseconds = 400, $n_intervals = 23)
    {
        // echo 'lookback_limit = '.$lookback_limit;
        // echo implode('/',PRequest::get()->request);
        // print_r($lookback_limit);
        $chatroom_id = (int)$chatroom_id;
        $prev_message_id = (int)$prev_message_id;
        $interval_milliseconds = (int)$interval_milliseconds;
        $n_intervals = (int)$n_intervals;
        
        for ($i=0; $i<$n_intervals; ++$i) {
            $messages = $this->bulkLookup(
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
    chat_messages.author_id   = members.id     AND
    chat_messages.chatroom_id = $chatroom_id   AND
    chat_messages.id          > $prev_message_id
                "
            );
            if (!empty($messages)) {
                end($messages)->text.= ' - '.$i;
                break;
            }
            usleep($interval_milliseconds);
        }
        
        foreach ($messages as &$message) {
            $message->text = htmlspecialchars($message->text);
            $message->created2 = date('d-m-Y H:i:s', $message->unixtime_created);
            if (date('Y-m-d') == date('Y-m-d', $message->unixtime_created)) {
                $message->created2 = date('H:i:s', $message->unixtime_created);
            }
        }
        
				// Now retrieve the activity in the room
				$ListOfMembers=array() ;
				
        $q = $this->dao->query("
				SELECT Username,chat_rooms_members.updated,Status
				from members,chat_rooms_members
				where IdRoom=".$chatroom_id." and members.id=chat_rooms_members.IdMember") ;
   			if (!$q) {
      	   throw new PException('Failed to retrieve list of members in the chatroom');
   			}
				while ($rr=$q->fetch(PDB::FETCH_OBJ)) {
					$ListOfMembers[]=$rr ;
				}
			
// Mark that a member activity in the room (sinc ehe retrieves messages)
        $this->singleLookup("
				REPLACE 
				into chat_rooms_members (IdRoom,IdMember) 
				values(".$chatroom_id.",".$_SESSION["IdMember"].")" ) 
				;


				$LastActivity->Messages=$messages ;
				$LastActivity->ListOfMembers=$ListOfMembers ;
			
				return($LastActivity) ;

				
    } // end of waitForMessagesInRoom
    
    function getMessagesInRoom($chatroom_id, $lookback_limit)
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


    } // end of getMessagesInRoom
    
    
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
