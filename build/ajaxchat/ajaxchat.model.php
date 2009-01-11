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
	 			global $_SYSHCVOL ;

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
//                end($messages)->text.= ' - '.$i;
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
        
// Mark that a member activity in the room (since he retrieves messages)
        $rr=$this->singleLookup("select IdMember from chat_rooms_members where IdRoom=".$chatroom_id." and IdMember=".$_SESSION["IdMember"]." /* update entry */") ;
				if (isset($rr->IdMember)) { 
        	$ss="update chat_rooms_members set updated=now() where IdRoom=".$chatroom_id." and IdMember=".$_SESSION["IdMember"] ;					}
				else {
        	$ss="insert into chat_rooms_members (IdRoom,IdMember,created)	values(".$chatroom_id.",".$_SESSION["IdMember"].",now()) /*new entry */" ;
				}
 				$result = $this->dao->query($ss);
				if (!$result) {
	   			throw new PException($ss.'Failed to update the activity of member '.$_SESSION["IdMember"].' in room #'.$chatroom_id);
				}

				// Now retrieve the activity in the room
				$ListOfMembers=array() ;
				
        $q = $this->dao->query("
				SELECT Username,appearance,chat_rooms_members.LastWrite  as LastWrite,chat_rooms_members.updated as LastActivity,members.Status as Status, ' *' as ChatStatus 
				from (members,online) left join chat_rooms_members on members.id=chat_rooms_members.IdMember and chat_rooms_members.updated>date_sub(Now(),Interval 240 second) and chat_rooms_members.IdRoom=".$chatroom_id."
				where  members.Status in ('Active','Pending','NeedMore,','MailToConfirm') and online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) and members.id=online.IdMember") ;
   			if (!$q) {
      	   throw new PException('Failed to retrieve list of members in the chatroom #'.$chatroom_id);
   			}
				while ($rr=$q->fetch(PDB::FETCH_OBJ)) {
					if (isset($rr->LastWrite)) {
						$rr->ChatStatus='*' ;
						$tDiff=time()-strtotime($rr->LastWrite)  ;

						if ($tDiff>120) {
							$rr->ChatStatus='* zz ' ;
						}
					}
					else {
						$rr->ChatStatus='   ' ;
					}
					switch ($rr->Status) {
						case 'Active' :
							$rr->DisplayStatus='' ; // This is the normal case no need to display somethin
							break ;
						case 'Pending' :
							$rr->DisplayStatus='(P)' ;
							break ;
						case 'NeedMore' :
							$rr->DisplayStatus='(N)' ;
							break ;
						case 'MailToConfirm' :
							$rr->DisplayStatus='(@)' ;
							break ;
						default:
							$rr->DisplayStatus='?' ;
					}
					$ListOfMembers[]=$rr ;
				}
			


				$LastActivity->Messages=$messages ;
				$LastActivity->ListOfMembers=$ListOfMembers ;
        $LastActivity->created2 = date('H:i:s');
			
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
    
    
    public function createMessageInRoom($chatroom_id, $author_id, $text) {
        // TODO: check for input sanity / avoid SQL injection
        // id is auto-generated (hopefully..)
        $text = mysql_real_escape_string($text);
        $chatroom_id = (int)$chatroom_id;
        $author_id = (int)$author_id;
        
        $ss="
INSERT INTO
    chat_messages
SET
    chatroom_id = $chatroom_id,
    author_id   = $author_id,
    text        = '$text',
    created     = NOW(),
    updated     = NOW()
            " ;
						
 				$result = $this->dao->query($ss);
				if (!$result) {
	   			throw new PException('Failed to insert mesage in room #'.$chatroom_id);
				}
        
// Mark that a member activity in the room (since he retrieves messages)
        $ss="REPLACE into chat_rooms_members (IdRoom,IdMember,LastWrite) values(".$chatroom_id.",".$_SESSION["IdMember"].",now() )" ;
				
 				$result = $this->dao->query($ss);
				if (!$result) {
	   			throw new PException('Failed to update the write activity of member '.$_SESSION["IdMember"].' in room #'.$chatroom_id);
				}

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

    } // end of createMessageInRoom
    
    
} 



?>
