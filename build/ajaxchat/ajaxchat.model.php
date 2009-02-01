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

	public $IdRoom ; // The IdRoom used for this model
	public $words ; // a shortuct to the words object
	public $room ; // COntains the content of the room record

    public function __construct($MyIdRoom=1,$Title="",$Description="") {
		$this->words=new MOD_words();
		$this->room=$this->singleLookup("select * from chat_rooms where id=".$MyIdRoom) ;
		if (!isset($this->room->id)) {
		 	$this->CreateRoom($Title,$Description) ;
		}
		else {
		 	$this->IdRoom=$this->room->id ; // Initial room
		}
		if (isset($_SESSION['IdMember'])) {
			$rr=$this->singleLookup("select * from chat_rooms_members where IdRoom=".$MyIdRoom." and IdMember=".$_SESSION['IdMember']) ;
			if (isset($rr->IdRoom)) {
				$this->dao->query("update chat_rooms_members set LastRefresh=now()  where IdRoom=".$MyIdRoom." and IdMember=".$_SESSION['IdMember']) ;
			}
		}
		parent::__construct();
    }
    
/*
	This function returns true if according to table chat_rooms_moderators the current user has right to do the thing in $Possibility
*/	
	function IsAllowed($Possibility="") {
		if (empty($_SESSION["IdMember"]))  {
			return(false) ;
		}
		$ss="select * from chat_room_moderators where IdRoom=".$this->room->id." and IdMember=".$_SESSION["IdMember"] ;
		$ss=$ss." and FIND_IN_SET('".$Possibility."',MemberCan)" ;
//		echo $ss,"<br />" ;
		$rr=$this->singleLookup($ss) ;
		return(!empty($rr->id)) ;
	} // end of  isAllowed
	
	function AddInRoom($Username) {
		$rrMember=$this->singleLookup("select id from members where Username='".$Username."' and (Status='Active' or Status='ActiveHidden' )") ;
		if (empty($rrMember->id)) {
			$ss="Failed in AddInRoom(".$Username.")" ;
			MOD_log::get()->write($ss ,"Debug") ;
			die($ss) ;
		}
			
		$IdMember=$rrMember->id ;
			
		$rr=$this->singleLookup("select * from chat_rooms_members where IdRoom=".$this->room->id." and IdMember=".$IdMember) ;
		if (isset($rr->IdRoom)) {
			if ($rr->StatusInRoom=='Banned') {
				die("Not Possible user is banned. Todo :make a proper layout") ;
			}
		 	$this->dao->query("update chat_rooms_members set udpated='0000-00-00 00:00:00'  where IdRoom=".$this->room->id." and IdMember=".$IdMember) ;
		}
		 else {
		 	 $this->dao->query("insert into chat_rooms_members(updated,IdRoom,IdMember,created) values('0000-00-00 00:00:00',".$this->room->id.",".$IdMember.",now())") ;
		}
		MOD_log::get()->write("Has invited ".$Username." in room #".$this->room->id ,"chat") ;
		$this->createMessageInRoom(0,$_SESSION["Username"]." has invited ".$Username." here") ;
	} // end of AddInRoom

		
	function DeleteRoom($IdRoom=0) {
		$room=$this->singleLookup("select * from chat_rooms where id=".$IdRoom) ;
		if (!MOD_right::get()->HasRight("Chat","DeleteRoom") and ($room->IdRoomOwner!=$_SESSION["IdMember"])) {
			$ss="You are not allowed to delete Room #".$IdRoom ;
			MOD_log::get()->write($ss ,"chat") ;
			die($ss) ;
			return ;
		}
		$this->CleanRoom() ;
		$this->RemovePeopleFromRoom($IdRoom) ;
		$ss="delete from forum_trads where TableColumn like 'chat_rooms.%' and IdRecord=".$room->id;
 		$qq = $this->dao->query($ss." /*Remove trads */ ") ;
		if (!$qq) {
	   		throw new PException('DeleteRoom::Failed to remove trads ');
		}
		$ss="delete from chat_rooms where id=".$room->id;
 		$qq = $this->dao->query($ss." /*delete room trads */ ") ;
		if (!$qq) {
	   		throw new PException('DeleteRoom::Failed to remove room ');
		}
		MOD_log::get()->write("Deleting room #".$this->IdRoom.' '.$room->RoomTitle ,"chat") ;
		MOD_log::get()->write("Delete done" ,"chat") ;
	}
		
	function RemovePeopleFromRoom($IdRoom=0,$Username="") {
		$room=$this->singleLookup("select * from chat_rooms where id=".$IdRoom) ;
		if (!empty($Username)) {
			$rGuy=$this->singleLookup("select id from members where Username='".$Username."'") ;
		}
		MOD_log::get()->write("RemovePeopleFromRoom [".$Username."] room #".$this->IdRoom ,"chat") ;
		if (isset($rGuy->id)) {
			$ss="delete from chat_rooms_members  where IdRoom=".$room->id." and IdMember=".$rGuy->id;
		}
		else {
			$ss="delete from chat_rooms_members  where IdRoom=".$room->id;
		}
 		$qq = $this->dao->query($ss." /*RemovePeopleFromRoom a room */ ") ;
		if (!$qq) {
			throw new PException('RemovePeopleFromRoom::Failed to remove people from a room ');
		}
		if (isset($rGuy->id)) {
			$sLog="$Username removed from room by ".$_SESSION["Username"] ;
			$this->createMessageInRoom(0,$sLog) ;
		}
		else {
			$sLog="RemovePeopleFromRoom done (".$qq->affectedRows()." people removed)" ;
			$this->createMessageInRoom(0,$sLog) ;
		}
		MOD_log::get()->write($sLog ,"chat") ;
	}
		
	function CleanRoom() {
		$room=$this->room ;
		if (!MOD_right::get()->HasRight("Chat","CleanRoom") and ($room->IdRoomOwner!=$_SESSION["IdMember"])) {
			$ss="You are not allowed to clean Room #".$room->id ;
	      	MOD_log::get()->write($ss ,"chat") ;
			die($ss) ;
			return ;
		}
      	MOD_log::get()->write("Cleaning room #".$room->id.' '.$room->RoomTitle ,"chat") ;
		$ss="delete from chat_messages where IdRoom=".$room->id;
 		$qq = $this->dao->query($ss." /*clean a room */ ") ;
		if (!$qq) {
	   		throw new PException('CleanRoom::Failed to clean a room ');
		}
		$count=$qq->affectedRows() ;
      	MOD_log::get()->write("Cleaning done (".$count." messages deleted)" ,"chat") ;
		$this->createMessageInRoom(0,$_SESSION["Username"]." has clean the room (".$count." messages deleted)") ;
	}
		
	function CreateRoom($Title="",$Description="") {
		if ($Title=="") {
			return(0) ;
		}
		$IdTitle=$this->words->InsertInFTrad($this->dao->escape($Title),"chat_rooms.RoomTitle",0, $_SESSION["IdMember"], 0) ; 
		$IdDescription=$this->words->InsertInFTrad($this->dao->escape($Description),"chat_rooms.RoomDescription",0, $_SESSION["IdMember"], 0) ; 
        $ss="insert into chat_rooms (IdRoomOwner,created,RoomTitle,RoomDescription) 	values(".$_SESSION["IdMember"].",now(),$IdTitle,$IdDescription)" ;
 		$rr = $this->dao->query($ss." /*new entry room */ ") ;
		if (!$rr) {
			throw new PException('CreateRoom::Failed to insert a room ');
		}
		$this->IdRoom=$rr->insertId() ;
		$this->singleLookup("update forum_trads set IdRecord=".$this->IdRoom." where TableColumn='chat_rooms.RoomTitle' and IdRecord=0") ;
		$this->singleLookup("update forum_trads set IdRecord=".$this->IdRoom." where TableColumn='chat_rooms.RoomDescription' and IdRecord=0") ;
    	$this->room=$this->singleLookup("select * from chat_rooms where id=".$this->IdRoom) ;

        $ss="insert into chat_rooms_members (IdRoom,IdMember,created) 	values($this->IdRoom,".$_SESSION["IdMember"].",now())" ;
 		$rr = $this->dao->query($ss." /*new entry chat_rooms_members */ ") ;
		if (!$rr) {
			throw new PException('CreateRoom::Failed to insert chat_rooms_members ');
		}

      	MOD_log::get()->write("Creating room #".$this->IdRoom.' '.$Title ,"chat") ;
		return($this->IdRoom) ; 				
	}
		
// Build the list of people who can be invite in the room
// This is base on the people in one of the PublicRoom
  	function BuildPossibleGuestList() {
		$ss="select distinct Username,IdMember from chat_rooms_members,members,chat_rooms where members.id=chat_rooms_members.IdMember and chat_rooms.id=chat_rooms_members.IdRoom and RoomType='Public' and chat_rooms_members.updated>date_sub(now(), interval 5 minute)" ;
		
        $q = $this->dao->query($ss) ;
   		if (!$q) {
			throw new PException('Failed to retrieve possible guest for room #'.$this->IdRoom);
   		}
				
		$list=array() ;
		while ($rr=$q->fetch(PDB::FETCH_OBJ)) {
			$ss="select * from chat_rooms_members where IdMember=".$rr->IdMember." and IdRoom=".$this->IdRoom ;
			$rAlreadyIn=$this->singleLookup($ss) ;
					
			if (empty($rAlreadyIn->IdMember)) {
				array_push($list,$rr) ;
			}
		}
		return($list) ;
	} // end of BuildPossibleGuestList
		
	function SetIdRoom($TheIdRoom=1) {
		$this->IdRoom=$TheIdRoom ;
    	$this->room=$this->singleLookup("select * from chat_rooms where id=".$this->IdRoom) ;
	}
		
    function getNowTime($timeshift = false) {
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
    
    
    function lookbackLimitHours() {
        return $this->getNowTime('-2 0:0:0');
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
	
	// Return "" if entering the current room is allowed, an error string elsewhere
	function FeedBackAllowance() {
		$words = $this->words ;

		if ($_SESSION["MemberStatus"]=='MailToConfirm') {
			return($words->getFormatted("ChatCannotEnterMailToConfirm",$_SESSION['Username'])) ;
		}
//  just test if the room exists
		$rr=$this->room ;
		if (!isset($rr->id)) {
			return($words->getFormatted("ChatCannotEnterRoomNotExists",$this->room->id)) ;
		} 
		return ("") ;
	} // end of FeedBackAllowance
 
    function waitForMessagesInRoom($prev_message_id, $interval_milliseconds = 400, $n_intervals = 23) {
	 	global $_SYSHCVOL ;
		
		$LastActivity->NewIntervall=500000 ; // If something goes wrong (the procedure doesn't run to completion), we reduce the update rate for future
		$LastActivity->ListOfMembers=array() ;
		$LastActivity->ServerTime = date('H:i:s');
		$LastActivity->ListOfPublicLink=array() ;
		$LastActivity->ListOfPrivateLink=array() ;

// First  test if the member  can read this room and prepare the update of the memberinroom
        $rr=$this->singleLookup("select IdMember,StatusInRoom from chat_rooms_members where IdRoom=".$this->room->id." and IdMember=".$_SESSION["IdMember"]." ") ;
		if (isset($rr->IdMember)) { 
			if ($rr->StatusInRoom=='Banned') {
				MOD_log::get()->write("Banned member in room #".$this->room->id." is trying to access" ,"chat") ; 				
				throw new PException('Sorry you are banned from room #'.$this->room->id);
				return ;
			}
			else {
				$ssMemberInRoom="update chat_rooms_members set updated=now(),CountActivity=CountActivity+1 where IdRoom=".$this->room->id." and IdMember=".$_SESSION["IdMember"] ;					
			}
		}
		elseif ($this->room->RoomType=='Public') {
        	$ssMemberInRoom="insert into chat_rooms_members (IdRoom,IdMember,created,CountActivity)	values(".$this->room->id.",".$_SESSION["IdMember"].",now(),1) " ;
			MOD_log::get()->write("Has joined room #".$this->room->id ,"chat") ; 				
		}
		else {
			MOD_log::get()->write("Member not in Private room in room #".$this->room->id." 	but trying to access it" ,"chat") ; 				
//			throw new PException('Sorry you are not yet invited in room #'.$this->room->id);
			$LastActivity->alerts='Sorry you are not invited in room #'.$this->room->id ;
			
			// Create a fake message which will be display as a feedback to inform user
			$OneMessage->text="Sorry you are not invited in room #".$this->room->id ;
			$OneMessage->updated=date('d-m-Y H:i:s') ;
			$OneMessage->IdRoomm=$this->room->id ;
			$OneMessage->created2=date('d-m-Y H:i:s') ;
			$OneMessage->username='system' ;
			$LastActivity->Messages=array($OneMessage) ;
			return($LastActivity) ; 
		}

		// Retrieve the messages
        $prev_message_id = (int)$prev_message_id;
        $interval_milliseconds = (int)$interval_milliseconds;
        $n_intervals = (int)$n_intervals;
        
        for ($i=0; $i<$n_intervals; ++$i) {
			$ss="
SELECT
    chat_messages.*,
    UNIX_TIMESTAMP(chat_messages.created)  AS unixtime_created,
    UNIX_TIMESTAMP(chat_messages.updated)  AS unixtime_updated,
    members.Username                       AS username
FROM
    (chat_messages)
LEFT JOIN members
ON chat_messages.IdAuthor   = members.id 
WHERE
    chat_messages.IdRoom = ".$this->room->id."   AND
    chat_messages.id          > ".$prev_message_id ;
            $messages = $this->bulkLookup($ss);
            if (!empty($messages)) {
                break;
            }
            usleep($interval_milliseconds);
        } // end of for $i
				
        
        foreach ($messages as &$message) {
            $message->text = htmlspecialchars($message->text);
            $message->created2 = date('d-m-Y H:i:s', $message->unixtime_created);
            if (date('Y-m-d') == date('Y-m-d', $message->unixtime_created)) {
                $message->created2 = date('H:i:s', $message->unixtime_created);
            }
        }
        
// Mark that a member activity in the room (since he retrieves messages)
 		$result = $this->dao->query($ssMemberInRoom);
		if (!$result) {
	   		throw new PException($ssMemberInRoom.'Failed to update the activity of member '.$_SESSION["IdMember"].' in room #'.$this->room->id);
		}
				
		// Now retrieve the activity in the room
		$ListOfMembers=array() ;
				
        $q = $this->dao->query("
				SELECT Username,appearance,chat_rooms_members.LastRefresh,chat_rooms_members.LastWrite  as LastWrite,chat_rooms_members.updated as LastActivity,members.Status as Status, ' *' as ChatStatus ,now() as DatabaseTime
				from (members,online) join chat_rooms_members on members.id=chat_rooms_members.IdMember and ((chat_rooms_members.updated>date_sub(Now(),Interval 240 second))or(chat_rooms_members.updated='0000-00-00 00:00:00')) and chat_rooms_members.IdRoom=".$this->IdRoom."
				where  members.Status in ('Active','Pending','NeedMore,','MailToConfirm') and online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) and members.id=online.IdMember") ;
   		if (!$q) {
			throw new PException('Failed to retrieve list of members in the chatroom #'.$this->IdRoom);
   		}
				
		$LastActivity->NewIntervall=$this->room->RefreshIntervall ;
		// Now choose the icon asociated to the guy
		$tDiff="no recent write" ;
		while ($rr=$q->fetch(PDB::FETCH_OBJ)) {
			$rr->ChatStatus='<img src="images/icons/status_away.png" alt="" />' ; // default shape
			if ((strtotime($rr->DatabaseTime)-strtotime($rr->LastRefresh))<240) {
				$rr->ChatStatus='<img src="images/icons/status_online.png" alt="" />' ; // Case the member has recently enter the room
			} ;
			if (($rr->LastActivity=="0000-00-00 00:00:00")or empty($rr->LastActivity)) {
				$rr->ChatStatus='<img src="images/icons/status_invited_pending.png" alt="" />' ; // Case the member has recently enter the room
			}
			if ((isset($rr->LastWrite))and($rr->LastWrite!="0000-00-00 00:00:00")) {
				$tDiff=strtotime($rr->DatabaseTime)-strtotime($rr->LastWrite)  ;
				if ($tDiff<=10) {
					$rr->ChatStatus='<img src="images/icons/user_comment.png" alt="" />' ;
				}
				if ($tDiff>10) {
//					$rr->ChatStatus='(sleep)' ;
					$rr->ChatStatus='<img src="images/icons/status_online.png" alt="" />' ;
				}
				if ($tDiff>240) {
					$rr->ChatStatus='<img src="images/icons/status_sleep.png" alt="" />' ;
					if ((strtotime($rr->DatabaseTime)-strtotime($rr->LastRefresh))<240) {
						$rr->ChatStatus='<img src="images/icons/status_online.png" alt="" />' ;// Case the member has recently enter the room
					} ;
				}
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
			


        $q = $this->dao->query("select * from chat_rooms where RoomType='Public'") ;
   		if (!$q) {
			throw new PException('Failed to retrieve list of public rooms');
   		}
				
		$ListOfPublicLink=array() ;
		while ($rr=$q->fetch(PDB::FETCH_OBJ)) {
			$ss="<a href=\"chat/room/".$rr->id."\" title=\"".$this->words->fTrad($rr->RoomDescription)."\">";
			$ss=$ss.$this->words->fTrad($rr->RoomTitle)."</a>" ;
			array_push($ListOfPublicLink,$ss) ;
		}

        $q = $this->dao->query("select chat_rooms.*,Username,chat_rooms_members.updated as LastActivity from chat_rooms,chat_rooms_members,members  where RoomType='Private' and chat_rooms_members.IdMember=".$_SESSION['IdMember']." and chat_rooms.id=chat_rooms_members.IdRoom and members.id=chat_rooms.IdRoomOwner") ;
		if (!$q) {
			throw new PException('Failed to retrieve list of private rooms');
   		}
		$ListOfPrivateLink=array() ;
		while ($rr=$q->fetch(PDB::FETCH_OBJ)) {
			$title=$this->words->fTrad($rr->RoomTitle) ;
			if (empty($title))  {
				$title="Room #".$rr->id ;
			}
			$ss="<a href=\"chat/room/".$rr->id."\" title=\"Owned by:".$rr->Username." ".$this->words->fTrad($rr->RoomDescription)."\">";
			$ss=$ss.$title."</a>" ;
			if ((!isset($rr->LastActivity))or($rr->LastActivity=="0000-00-00 00:00:00")) { // If its a new room where the member has just been invited, make it blinking green
				$color="white" ;
				if (rand(1,2)==1) $color="lightgreen" ;
				$ss="<table><tr><td bgcolor=\"$color\">".$ss."</td></tr></table>" ;
			}
			
			array_push($ListOfPrivateLink,$ss) ;

		}

		// This log message creates heavy load, it will be needed to dismantle it
      	if ($_SESSION["Param"]->AjaxChatDebuLevel>=2) {
			MOD_log::get()->write("waitForMessagesInRoom:: Query Loop ".count($messages)." messages fetched with id greater that \$prev_message_id=#".$prev_message_id." \$tDiff=".$tDiff." Idroom=#".$this->IdRoom ,"chat") ;
		} 				

		$LastActivity->Messages=$messages ;
		$LastActivity->ListOfMembers=$ListOfMembers ;
        $LastActivity->ServerTime = date('H:i:s');
        $LastActivity->ListOfPublicLink=$ListOfPublicLink;
        $LastActivity->ListOfPrivateLink=$ListOfPrivateLink;

		$LastActivity->alerts="" ; // no alerts
			
		return($LastActivity) ;
				
    } // end of waitForMessagesInRoom
    
    function getMessagesInRoom($lookback_limit)  {
		
//		die ("I think this function is not used") ;
        // echo 'lookback_limit = '.$lookback_limit;
        // echo implode('/',PRequest::get()->request);
        // print_r($lookback_limit);
        
        // $lookback_limit is a javascript-created string timestamp + id
        // TODO: is the check $lookback_limit really robust enough?
        $lookback_limit = mysql_real_escape_string($lookback_limit);
		$ss="
SELECT
    chat_messages.*,
    UNIX_TIMESTAMP(chat_messages.created)  AS unixtime_created,
    UNIX_TIMESTAMP(chat_messages.updated)  AS unixtime_updated,
    members.Username                       AS username

FROM
    (chat_messages)
LEFT JOIN members
ON chat_messages.IdAuthor   = members.id 
WHERE
    chat_messages.IdRoom = $this->IdRoom  AND
    chat_messages.updated     > '$lookback_limit'
            " ;
        $messages_found = $this->bulkLookup($ss);
        
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
    
    /*
	createMessageInRoom function inserts a message in a room
	@IdAuthor is the id of the author (can be 0, if so this is assumed to be a system message)
	@text the text of the message
	
	returns the message create or en error (->Error != "" )
	*/

	public function createMessageInRoom($IdAuthor, $text) {
        // TODO: check for input sanity / avoid SQL injection
        // id is auto-generated (hopefully..)

        $IdAuthor = (int)$IdAuthor;
		
		$rMember=$this->singleLookup("select IdMember,IdRoom,StatusInRoom from chat_rooms_members where IdMember=".$_SESSION['IdMember']." and IdRoom=".$this->room->id) ;
		if ((empty($rMember->IdMember)) and ($IdAuthor>0)) {
			$rLastMessage->Error="Sorry ".$_SESSION['Username'].", You are not in room #".$this->room->id;
			return($rLastMessage) ;
		}
        $text = mysql_real_escape_string($text);
        $ss="INSERT INTO chat_messages SET  IdRoom = ".$this->room->id ;
		$ss=$ss.",IdAuthor   = $IdAuthor, text= '$text', created= NOW(),  updated= NOW() " ;
						
 		$result = $this->dao->query($ss);
		if (!$result) {
      		MOD_log::get()->write("Failed to insert message in room #".$this->room->id." $ss" ,"chat") ; 				
	   		throw new PException('Failed to insert mesage in room #'.$this->room->id);
		}
				
        // Return the last inserted message
		$rLastMessage=$this->singleLookup("
SELECT
    chat_messages.*,
    members.Username as username
FROM
    (chat_messages)
LEFT JOIN members
ON chat_messages.IdAuthor   = members.id 
WHERE
    chat_messages.id = LAST_INSERT_ID()
            "
        );

		// Mark that a member activity in the room (since he retrieves messages)
		$ss="REPLACE into chat_rooms_members (IdRoom,IdMember,LastWrite) values(".$this->room->id.",".$_SESSION["IdMember"].",now() )" ;
		$result = $this->dao->query($ss);
		if (!$result) {
			throw new PException('Failed to update the write activity of member '.$_SESSION["IdMember"].' in room #'.$this->room->id);
		}
		
		if ($_SESSION["Param"]->AjaxChatDebuLevel>=1) {
		   	MOD_log::get()->write("Has post Message_id #".$result->insertId()." in room #".$this->room->id ,"chat") ; 				
		}
        
		$rLastMessage->Error="" ; // no error
		return ($rLastMessage) ;

    } // end of createMessageInRoom
} 

?>
