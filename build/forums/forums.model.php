<?php

use App\Doctrine\NotificationStatusType;
use Foolz\SphinxQL\Drivers\Pdo\Connection;
use Foolz\SphinxQL\SphinxQL;

/**
* Forums model
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id: forums.model.php 32 2007-04-03 10:22:22Z marco_p $
*/

// Utility function to sort the languages
function cmpForumLang($a, $b)
{
    if ($a == $b) {
        return 0;
    }
    return (strtolower($a->Name) < strToLower($b->Name)) ? -1 : 1;
}

class Forums extends RoxModelBase {

    const CV_THREADS_PER_PAGE = 15;
    const CV_POSTS_PER_PAGE = 100;
    const CV_TOPMODE_CATEGORY = 1; // Says that the forum topmode is for categories
    const CV_TOPMODE_LASTPOSTS = 2; // Says that the forum topmode is for lastposts
    const CV_TOPMODE_LANDING = 3; // Says that we use the forums landing page for topmode
    const CV_TOPMODE_FORUM = 4; // Says that we use the forums main page for topmode
    const CV_TOPMODE_GROUPS = 5; // Says that we use the group forums overview page for topmode


    const NUMBER_LAST_POSTS_PREVIEW = 10; // Number of Posts shown as a help on the "reply" page

	public $THREADS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $words ; // a shortcut to words module
	public $ForumOrderList ; // The order of list in forum ascencding or desc this is a preference
    public $BW_Right;



/**
* GetLanguageChoosen function
*
* This return the language choosen by the user
* this function is supposed to be called after a new post, and editpost or a reply
* it return the language choosen if any
*/
function GetLanguageChoosen() {
	$DefLanguage=0 ;
   if ($this->session->has( 'IdLanguage' )) {
	   $DefLanguage=$this->session->get('IdLanguage') ;
	}
	if (isset($_POST['IdLanguage'])) { // This will allow to consider a Language specified in the form
	   $DefLanguage=$_POST['IdLanguage'] ;
	}
	return($DefLanguage) ;
} // end of GetLanguageChoosen


    /**
     * InsertInfTrad function
     *
     * This InsertInFTrad create a new translatable text in forum_trads
     * @$ss is for the content of the text
     * @$TableColumn refers to the table and coilumn the trad is associated to
     * @$IdRecord is the num of the record in this table
     * @$_IdMember ; is the id of the member who own the record
     * @$_IdLanguage
     * @$IdTrad  is probably useless (I don't remmber why I defined it)
     *
     *
     * Warning : as default language this function will use by priority :
     * 1) the content of $_IdLanguage if it is set to something else than -1
     * 2) the content of an optional $_POST[IdLanguage] if it is set
     * 3) the content of the current $this->session->get('IdLanguage') of the current membr if it set
     * 4) The default language (0)
     *
     * returns the id of the created trad
     * @param string $ss
     * @param string $TableColumn
     * @param int $IdRecord
     * @param int $_IdMember
     * @param int $_IdLanguage
     * @param int $IdTrad
     * @return int
     * @throws PException
     */
private function InsertInFTrad($ss, $TableColumn, $IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
    $this->words = new MOD_words();
	return ($this->words->InsertInFTrad($ss,$TableColumn,$IdRecord, $_IdMember, $_IdLanguage, $IdTrad)) ;
} // end of InsertInFTrad

    /**
     * ReplaceInFTrad function
     *
     * This ReplaceInFTrad replace or create translatable text in forum_trads
     * @$ss is for the content of the text
     * @$TableColumn refers to the table and column the trad is associated to
     * @$IdRecord is the num of the record in this table
     * $IdTrad is the record in forum_trads to replace (unique for each IdLanguage)
     * @$Owner ; is the id of the member who own the record
     *
     * Warning : as default language this function will use by priority :
     * 1) the content of $_IdLanguage if it is set to something else than -1
     * 2) the content of an optional $_POST[IdLanguage] if it is set
     * 3) the content of the current $this->session->get('IdLanguage') of the current membr if it set
     * 4) The default language (0)
     * @param string $ss
     * @param string $TableColumn
     * @param int $IdRecord
     * @param int $IdTrad
     * @param int $IdOwner
     * @return int
     * @throws PException
     */
private function ReplaceInFTrad($ss, $TableColumn, $IdRecord, $IdTrad = 0, $IdOwner = 0) {
    $this->words = new MOD_words();
	return ($this->words->ReplaceInFTrad($ss,$TableColumn,$IdRecord, $IdTrad, $IdOwner )) ;
} // end of ReplaceInFTrad

/**
* FindAppropriatedLanguage function will retrieve the appropriated default language
* for a member who want to reply to a thread (started with the#@IdPost post)
* this retriewal is made according to the language of the post, the current language of the user
*/
function FindAppropriatedLanguage($IdPost=0) {
    $ss="select `IdContent` FROM `forums_posts` WHERE `id`=".$IdPost ;
    $q = $this->dao->query($ss);
	$row= $q->fetch(PDB::FETCH_OBJ);

//	$q = $this->_dao->query($ss);
//	$row = $q->fetch(PDB::FETCH_OBJ);
	if (!isset($row->IdContent)) {
	   return (0) ;
	}
	else {
	   $IdTrad=$row->IdContent ;
	}

	// Try IdTrad with current language of the member
  	$query ="SELECT IdLanguage FROM `forum_trads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$this->session->get("IdLanguage") ;
	$q = $this->dao->query($query);
	$row = $q->fetch(PDB::FETCH_OBJ);
	if (isset ($row->IdLanguage)) {
	   return($row->IdLanguage) ;
	}

	// Try with the original language used for this post
	$query ="SELECT `IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad."  order by id asc limit 1" ;
	$q = $this->dao->query($query);
	$row = $q->fetch(PDB::FETCH_OBJ);
	if (isset ($row->IdLanguage)) {
	   return($row->IdLanguage) ;
	}

	return(0) ; // By default we will return english

} // end of FindAppropriatedLanguage


	/*
	* Constructor of forum model, prepare manything
	* and things relative to Visibility
	*
	*/
	public function __construct() {
        parent::__construct();
		$this->THREADS_PER_PAGE=Forums::CV_THREADS_PER_PAGE  ; //Variable because it can change wether the user is logged or no
		$this->POSTS_PER_PAGE=Forums::CV_POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no

        if ($this->session->has('IdMember')) {
            $member = $this->getLoggedInMember();

            switch($member->getPreference("PreferenceForumFirstPage")) {
                case "Pref_ForumFirstPageLastPost":
                    $this->setTopMode(Forums::CV_TOPMODE_FORUM) ;
                    break ;
                case "Pref_ForumFirstPageCategory":
                    $this->setTopMode(Forums::CV_TOPMODE_CATEGORY) ;
                    break ;
                default:
                    $this->setTopMode(Forums::CV_TOPMODE_LANDING) ;
                    break ;
            }
            $layoutbits = new MOD_layoutbits();
            $this->ForumOrderList = $layoutbits->GetPreference("PreferenceForumOrderListAsc", $member->id);
        } else {
            $this->setTopMode(Forums::CV_TOPMODE_FORUM);
        }

		if (!$this->session->has( 'IdMember' )) {
			$this->THREADS_PER_PAGE = 100; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE = self::CV_POSTS_PER_PAGE; // Variable because it can change wether the user is logged or no
		}

		$MyGroups = array();


		$this->words = new MOD_words();
		$this->BW_Right = MOD_right::get();
		$this->IdGroup = false; // By default no group
		$this->ByCategories = false; // toggle or not toglle the main view is TopCategories or TopLevel

		//	Decide if it is an active LoggeMember or not
		if (!$this->session->has("IdMember") ||
                !$this->session->has("MemberStatus") ||
                $this->session->get("MemberStatus") == 'Pending' ||
                $this->session->get("MemberStatus") == 'NeedMore' ) {
			$this->PublicThreadVisibility=" (ThreadVisibility = 'NoRestriction') AND (ThreadDeleted != 'Deleted')";
			$this->PublicPostVisibility = " (PostVisibility = 'NoRestriction') AND (PostDeleted != 'Deleted')";
			$this->ThreadGroupsRestriction = " (IdGroup IS NULL OR ThreadVisibility = 'NoRestriction')";
			$this->PostGroupsRestriction = " (IdGroup IS NULL OR PostVisibility = 'NoRestriction')" ;
		}
		else {
			$this->PublicThreadVisibility = "(ThreadVisibility != 'ModeratorOnly') AND (ThreadDeleted != 'Deleted')" ;
			$this->PublicPostVisibility = "(PostVisibility != 'ModeratorOnly') AND (PostDeleted !='Deleted')" ;
			$this->PostGroupsRestriction = " PostVisibility IN ('MembersOnly','NoRestriction') OR (PostVisibility='GroupOnly' AND (IdGroup IS NULL OR IdGroup in (0" ;
			$this->ThreadGroupsRestriction = " ThreadVisibility IN ('MembersOnly','NoRestriction') OR (ThreadVisibility = 'GroupOnly' and (IdGroup IS NULL OR IdGroup in (0" ;
			$qry = $this->dao->query("SELECT IdGroup FROM membersgroups WHERE IdMember = " . $this->session->get("IdMember") . " AND Status = 'In'");
			if (!$qry) {
				throw new PException('Failed to retrieve groups for member id =#'.$this->session->get("IdMember").' !');
			}
			while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
				$this->PostGroupsRestriction = $this->PostGroupsRestriction . "," . $rr->IdGroup;
				$this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . "," . $rr->IdGroup;
				array_push($MyGroups,$rr->IdGroup) ; // Save the group list
			}
			$this->PostGroupsRestriction = $this->PostGroupsRestriction . ")))";
			$this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . ")))";
		}

		// Prepares additional visibility options for moderator
		if ($this->BW_Right->HasRight("ForumModerator")) {
			$this->PublicPostVisibility = " PostVisibility IN ('NoRestriction', 'MembersOnly','GroupOnly','ModeratorOnly')";
			$this->PublicThreadVisibility = " ThreadVisibility IN ('NoRestriction', 'MembersOnly','GroupOnly','ModeratorOnly')";
			if ($this->BW_Right->HasRight("ForumModerator","AllGroups") or $this->BW_Right->HasRight("ForumModerator","All")) {
				$this->PostGroupsRestriction = " (1=1)";
				$this->ThreadGroupsRestriction = " (1=1)";
			}
		}
        $this->MyGroups = $MyGroups;
    } // __construct

	// This switch the preference ForumOrderList
	public function switchForumOrderList() {
        $ss="SELECT id FROM preferences WHERE codename ='PreferenceForumOrderListAsc'";
        $qq = $this->dao->query($ss);
        if (!$qq) {
            // We couldn't get a connection to the database...
            return;
        }
		if ($this->ForumOrderList=="Yes") {
			$this->ForumOrderList="No" ;
		}
		else {
			$this->ForumOrderList="Yes" ;
		}
		$row = $qq->fetch(PDB::FETCH_OBJ);
		if (null === $row)
        {
            // if the preference doesn't exist something is very, very wrong. return at your own risk...
            return;
        }
		$idPreference = $row->id;
        $idMember = $this->session->get('IdMember');

		$ss="SELECT mp.value, mp.id FROM memberspreferences mp WHERE mp.IdMember = " . $idMember . " AND mp.IdPreference = " . $idPreference;
        $result = $this->dao->query($ss);
		if (!$result) {
            // We couldn't get a connection to the database...
		    return;
        }

		$row = $result->fetch(PDB::FETCH_OBJ) ;
		if (null === $row) {
		    $query = "
                INSERT INTO
                    memberspreferences (`created`, `updated`, `IdPreference`, `IdMember`, `Value`)
                VALUES (NOW(), `created`, " .$idPreference . ", " . $idMember . ", '" . $this->dao->escape($this->ForumOrderList) . "' )
            ";
		}
		else {
		    $query = "
		        UPDATE
		            memberspreferences
		        SET Value='" . $this->dao->escape($this->ForumOrderList) . "' WHERE id= " . $row->id
            ;
		}
        $result = $this->dao->query($query);
	} // end of switchForumOrderList

    public function adjustThreadsCountToShow($step = 1) {
        $MAX_THREADS = 1000; //An upper limit just in case
        if (!$member = $this->getLoggedInMember()) {
            return false;
        }
        $vars =& PPostHandler::getVars();
        if (!isset($vars['agoragroupsthreadscountmoreless'])) {
            return false;
        }
        $command = $vars['agoragroupsthreadscountmoreless'];
        $layoutbits = new MOD_layoutbits();
        $forumthreads = intval($layoutbits->GetPreference("ForumThreadsOnLandingPage", $member->id));
        $groupsthreads = intval($layoutbits->GetPreference("GroupsThreadsOnLandingPage", $member->id));
        $membersmodel = new MembersModel();

        $query = "
            SELECT
                id
            FROM
                preferences
            WHERE
                CodeName = 'ForumThreadsOnLandingPage'
            LIMIT 1
            ";
        $row = $this->dao->query($query);
        $forumpref = $row->fetch(PDB::FETCH_OBJ);
        if ($forumpref === false) {
            throw new Exception('Database error: "ForumThreadsOnLandingPage"'
                . ' preference not found in "preferences" table');
        }

        $query = "
            SELECT
                id
            FROM
                preferences
            WHERE
                CodeName = 'GroupsThreadsOnLandingPage'
            LIMIT 1
            ";
        $row = $this->dao->query($query);
        $groupspref = $row->fetch(PDB::FETCH_OBJ);
        if ($groupspref === false) {
            throw new Exception('Database error: "GroupsThreadsOnLandingPage"'
                . ' preference not found in "preferences" table');
        }

        switch ($command) {
            case "moreagora":
                $membersmodel->set_preference($member->id, $forumpref->id, min($forumthreads + $step, $MAX_THREADS));
                break;
            case "lessagora":
                $membersmodel->set_preference($member->id, $forumpref->id, max($forumthreads - $step, 1));
                break;
            case "moregroups":
                $membersmodel->set_preference($member->id, $groupspref->id, min($groupsthreads + $step, $MAX_THREADS));
                break;
            case "lessgroups":
                $membersmodel->set_preference($member->id, $groupspref->id, max($groupsthreads - $step, 1));
                break;
        }

        return false;
    }



    // This switch the preference switchShowMyGroupsTopicsOnly
    public function switchShowMyGroupsTopicsOnly() {
        if (!$member = $this->getLoggedInMember()) {
            return false;
        }
        $owngroupsonly = $member->getPreference("ShowMyGroupsTopicsOnly", $default = "No");
        $this->ShowMyGroupsTopicsOnly = $owngroupsonly;
        if ($this->ShowMyGroupsTopicsOnly == "Yes") {
            $this->ShowMyGroupsTopicsOnly = "No" ;
        }
        else {
            $this->ShowMyGroupsTopicsOnly = "Yes" ;
        }

        // Fetch preference object
        $query = "
            SELECT
                id
            FROM
                preferences
            WHERE
                CodeName = 'ShowMyGroupsTopicsOnly'
            LIMIT 1
            ";
        $row = $this->dao->query($query);
        $preference = $row->fetch(PDB::FETCH_OBJ);
        if ($preference === false) {
            throw new Exception('Database error: "ShowMyGroupsTopicsOnly"'
                . ' preference not found in "preferences" table');
        }

        $ss = "
SELECT
    m.Value AS Value,
    m.id AS id,
    p.id AS IdPreferences
FROM
    memberspreferences AS m,
    preferences AS p
WHERE
    p.id = m.IdPreference
    AND m.IdMember = " . $this->session->get('IdMember') . "
    AND p.CodeName = 'ShowMyGroupsTopicsOnly'";

        $qq = $this->dao->query($ss);
        $rr = $qq->fetch(PDB::FETCH_OBJ) ;
        if (empty($rr->Value)) {
            $ss = "
INSERT INTO
    memberspreferences(
        created,
        IdPreference,
        IdMember,
        Value
    )
VALUES(
    now(),
    " . $preference->id . "," .
    $this->session->get('IdMember') . ",
    '" . $this->ShowMyGroupsTopicsOnly . "'
)" ;
        }
        else {
            $ss = "
UPDATE
    memberspreferences
SET
    Value='" . $this->ShowMyGroupsTopicsOnly . "'
WHERE
    id=" . $rr->id ;
        }

        $qq = $this->dao->query($ss);
        if (!$qq) {
            throw new PException('switchShowMyGroupsTopicsOnly ' . $ss . ' !');
        }
        return false;
    } // end of switchShowMyGroupsTopicsOnly


    public function checkGroupMembership($group_id) {
        if (in_array($group_id,$this->MyGroups)) {
            return true;
        }
        return false;
    } // end of checkGroupMembership

    private function boardTopLevelForum($showsticky = true) {
        $this->board = new Board($this->dao, 'Forum', '.', $this->getSession(),  false, null, false);
        $this->board->initThreads($this->getPage(), $showsticky);

    } // end of boardTopLevelForum

    private function boardTopLevelGroups($showsticky = true) {
        $this->board = new Board($this->dao, 'Groups', '.', $this->getSession(),  false, false, true);
        $this->board->initThreads($this->getPage(), $showsticky);

    } // end of boardTopLevelGroups

    private function boardTopLevelLanding($showsticky = true) {
        $User = $this->getLoggedInMember();
        if (!$User) {
            // Show informal message that the forums are limited to members only
            return false;
        }

        $MAX_THREADS = 1000; //An upper limit of threads th show just in case the preference goes silly
        $layoutbits = new MOD_layoutbits();

        $idMember = $this->getSession()->get('IdMember');
        $forumthreads = intval($layoutbits->getPreference("ForumThreadsOnLandingPage", $idMember));
        $groupsthreads = intval($layoutbits->getPreference("GroupsThreadsOnLandingPage", $idMember));

        $page_array = $this->getPageArray();

        if (isset($page_array[0]) && isset($page_array[1])) {
            $forumpage = $page_array[0];
            $groupspage = $page_array[1];
        } else {
            $forumpage = 1;
            $groupspage = 1;
        }


        $this->board = new Board($this->dao, 'Forums and Groups', '.', $this->getSession());

        $forum = new Board($this->dao, 'Forum', '.', $this->getSession(),  false, null, false);
        $forum->THREADS_PER_PAGE = max(1, min($forumthreads, $MAX_THREADS));
        $forum->initThreads($forumpage, $showsticky);
        $forumMaxPage = max(ceil($forum->getNumberOfThreads() / $forum->THREADS_PER_PAGE), 1);
        if ($forumpage > $forumMaxPage) {
            $forum->initThreads($forumMaxPage, $showsticky);
        }

        $groups = new Board($this->dao, 'Groups', '.', $this->getSession(),  false, false, true);
        $groups->THREADS_PER_PAGE = max(1, min($groupsthreads, $MAX_THREADS));
        $groups->initThreads($groupspage, false);

        $this->board->add($forum);
        $this->board->add($groups);
        return true;
    } // end of boardTopLevelLanding

    private function boardTopLevelLastPosts($showsticky = true) {
        $this->board = new Board($this->dao, 'Forums', '.', $this->getSession());
        $this->board->initThreads($this->getPage(), $showsticky);
    } // end of boardTopLevelLastPosts

    // This build the board for the $this->IdGroup
    private function boardGroup($showsticky = true) {

        $query = "SELECT `Name` FROM `groups` WHERE `id` = " . (int)$this->IdGroup;
        $gr = $this->dao->query($query);
        if (!$gr) {
            throw new PException('No such IdGroup=#'.$this->IdGroup);
        }
        $group = $gr->fetch(PDB::FETCH_OBJ);

        $subboards = array();
		$gtitle= $this->words->getSilent("ForumGroupTitle", $this->getGroupName($group->Name)) ;
        $this->board = new Board($this->dao, "", ".", $this->getSession(), $subboards, $this->IdGroup);
        $this->board->initThreads($this->getPage(), $showsticky);
    } // end of boardGroup

	/*
	@ $Name name of the group (direct from groups.Name
	*/
    public function getGroupName($Name) {
		return($Name) ;

	}

    /**
    * Fetch all required data for the view to display a forum
		* this data are stored in $this->board
    */
    public function prepareForum($showsticky = true) {
        if (!$this->IdGroup) {
            if ($this->TopMode==Forums::CV_TOPMODE_LASTPOSTS) {
				$this->boardTopLevelLastPosts($showsticky);
			}
			elseif ($this->TopMode==Forums::CV_TOPMODE_LANDING) {
				$this->boardTopLevelLanding($showsticky);
			}
			elseif ($this->TopMode==Forums::CV_TOPMODE_FORUM) {
				$this->boardTopLevelForum($showsticky);
			}
			elseif ($this->TopMode==Forums::CV_TOPMODE_GROUPS) {
				$this->boardTopLevelGroups($showsticky);
			}
			else {
				$this->boardTopLevelLanding($showsticky);
			}
		} elseif ($this->IdGroup) {
            $this->boardGroup($showsticky);
        } elseif (PVars::get()->debug) {
            throw new PException('Invalid Request');
        } else {
            PRequest::home();
        }
    } // end of prepareForum

    private $board;
    public function getBoard() {
        return $this->board;
    }

    public function createProcess() {
        if (!($User = $this->getLoggedInMember())) {
            return false;
        }

        $vars =& PPostHandler::getVars();

        $vars_ok = $this->checkVarsTopic($vars);
        if ($vars_ok) {
            $topicid = $this->newTopic($vars);
            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.$this->forums_uri.'s'.$topicid;
        } else {
            return false;
        }

    }

    /*
     * Fill the Vars in order to prepare edit a post
	  * this fetch the data which are then going to be display and then change
	  * by the user
     */
    public function getEditData($callbackId) {
        $query =
            "
SELECT
    `forums_posts`.`id` AS `postid`,
    `IdWriter`,
    `PostDeleted`,
    `PostVisibility`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forums_posts`.`threadid` as `threadid`,
    `message` AS `topic_text`,
		`OwnerCanStillEdit`,
	 `IdContent`,
    `title` AS `topic_title`, `first_postid`, `last_postid`, `IdTitle`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forums_threads`.`IdGroup`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`id`)
WHERE `forums_posts`.`id` = $this->messageId
and ($this->PublicPostVisibility)
            ";

       $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('getEditData :: Could not retrieve Postinfo!');
        }
        $vars =& PPostHandler::getVars($callbackId);
        $vars = $s->fetch(PDB::FETCH_ASSOC);
        $this->IdGroup = $vars['IdGroup'];
    } // end of get getEditData

    /*
     * Write in the database the changed data
	  * when a post is edited, this also write a log and
	  * this call editPost and may be editTopic which does the update in the database
	  * by the user
     */
    public function editProcess() {
        if (!($User = $this->getLoggedInMember())) {
            return false;
        }

        $vars =& PPostHandler::getVars();

        $query =
            "
SELECT
    `forums_posts`.`id` AS `postid`,
    `IdWriter`,
    `forums_posts`.`threadid`,
    `first_postid`,
	`OwnerCanStillEdit`,
    `PostDeleted`,
    `PostVisibility`,
    `ThreadVisibility`,
    `ThreadDeleted`,
	`forums_threads`.`IdGroup`,
    `last_postid`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`id`)
WHERE `forums_posts`.`id` = $this->messageId
            "
        ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Postinfo!');
        }
        $postinfo = $s->fetch(PDB::FETCH_OBJ);

//        if ($this->BW_Right->HasRight("ForumModerator","Edit") || ($User->hasRight('edit_own@forums') && $postinfo->authorid == $User->getId())) {
        if ($this->BW_Right->HasRight("ForumModerator","Edit") ||  ($postinfo->IdWriter == $this->session->get("IdMember") and $postinfo->OwnerCanStillEdit=="Yes")) {
            $is_topic = ($postinfo->postid == $postinfo->first_postid);

            if ($is_topic) {
                $vars_ok = $this->checkVarsTopic($vars);
            } else {
                $vars_ok = $this->checkVarsReply($vars);
            }
            if ($vars_ok) {
                $this->dao->query("START TRANSACTION");

                if ($is_topic) {
                    if (!isset($vars['ThreadVisibility'])) {
                        $vars['ThreadVisibility'] = 'MembersOnly';
                    }
                    $vars['PostVisibility'] = $vars['ThreadVisibility'];
                }

                if (!isset($vars['PostVisibility'])) {
                    $vars['PostVisibility'] = 'MembersOnly';
                }

                $this->editPost($vars, $User->getId());
                if ($is_topic) {
                    $this->editTopic($vars, $postinfo->threadid);
                }

                $this->dao->query("COMMIT");

                PPostHandler::clearVars();
                return PVars::getObj('env')->baseuri.$this->forums_uri.'s'.$postinfo->threadid;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } // end of editProcess

/**
* the function DofTradUpdate() update a forum translation
* @IdForumTrads is the primary key of the parameter to update
*/
	 public function DofTradUpdate($IdForumTrads,$P_Sentence,$P_IdLanguage=0) {
	 	 $id=(int)$IdForumTrads ;
        $s=$this->dao->query("select * from forum_trads where id=".$id);
		 $rBefore=$s->fetch(PDB::FETCH_OBJ) ;

// Save the previous version
		 $this->words->MakeRevision($id, "forum_trads",$this->session->get("IdMember"), $DoneBy = "DoneByModerator")  ;
		 $IdLanguage=(int)$P_IdLanguage ;
		 $Sentence= $this->dao->escape($P_Sentence);

        MOD_log::get()->write("Updating data for IdForumTrads=#".$id." Before [".addslashes($rBefore->Sentence)."] IdLanguage=".$rBefore->IdLanguage." <br />\nAfter [".$Sentence."] IdLanguage=".$IdLanguage, "ForumModerator");
		 $sUpdate="update forum_trads set Sentence='".$Sentence."',IdLanguage=".$IdLanguage.",IdTranslator=".$this->session->get("IdMember")." where id=".$id ;
        $s=$this->dao->query($sUpdate);
        if (!$s) {
            throw new PException('Failed for Update forum_trads.id=#'.$id);
        }

	 } // end of DofTradUpdate


/**
*	editPost write the data in of change post in the database
*	warning : dont start any transaction in it sinc ethere is already one
*  started by the caller
* this also write a log
*/
    private function editPost($vars, $editorid) {
        $query = "SELECT message,forums_posts.threadid,
    `PostVisibility`,
		OwnerCanStillEdit,IdWriter,forums_posts.IdFirstLanguageUsed as post_IdFirstLanguageUsed,forums_threads.IdFirstLanguageUsed as thread_IdFirstLanguageUsed,forums_posts.id,IdWriter,IdContent,forums_threads.IdTitle,forums_threads.first_postid from `forums_posts`,`forums_threads` WHERE forums_posts.threadid=forums_threads.id and forums_posts.id = ".$this->messageId ;
        $s=$this->dao->query($query);
        $rBefore=$s->fetch(PDB::FETCH_OBJ) ;

        $query = sprintf("UPDATE `forums_posts` SET `message` = '%s', `last_edittime` = NOW(), `last_editorid` = '%d', `edit_count` = `edit_count` + 1 WHERE `id` = '%d'",
        $this->dao->escape($this->cleanupText($vars['topic_text'])), $editorid, $this->messageId);
        $this->dao->query($query);
		$this->ReplaceInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])), "forums_posts.IdContent", $rBefore->id, $rBefore->IdContent, $rBefore->IdWriter) ;

		// case the update concerns the reference language of the posts
		if ($rBefore->post_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	$query="update forums_posts set message='".$this->dao->escape($this->cleanupText($vars['topic_text']))."' where id=".$this->messageId ;
        	$s=$this->dao->query($query);
		}

		// case the visibility has changed
		if ($rBefore->PostVisibility!=$vars['PostVisibility']) {
		 	$query="update forums_posts set PostVisibility='".$vars['PostVisibility']."' where id=".$this->messageId ;
        	$s=$this->dao->query($query);
			MOD_log::get()->write("Changing Post Visibility from <b>".$rBefore->PostVisibility."</b> to <b>".$vars['PostVisibility']."</b>", "Forum");
		}



		// If this is the first post, may be we can update the title
		if ($rBefore->first_postid==$rBefore->id) {
		 	$this->ReplaceInFTrad($this->dao->escape($this->cleanupText($vars['topic_title'])), "forums_threads.IdTitle", $rBefore->threadid, $rBefore->IdTitle, $rBefore->IdWriter) ;
		// case the update concerns the reference language of the threads
		 	if ($rBefore->thread_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	    $groupId = (isset($vars['IdGroup']))?$vars['IdGroup']:'NULL';
		 	   $query="update forums_threads set IdGroup=". $groupId.",title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forums_threads.id=".$rBefore->threadid ;
        	   $s=$this->dao->query($query);
		   }
		}

        // subscription if any, could be done out of transaction, this is not so important
        if ((isset($vars['NotifyMe'])) and ($vars['NotifyMe']=="on")) {
			if (!$this->IsThreadSubscribed($rBefore->threadid,$this->session->get("IdMember"))) {
                 $this->SubscribeThread($rBefore->threadid,$this->session->get("IdMember")) ;
			}
        }
        else {
			$vars['NotifyMe']="Not Asked" ;
			if ($this->IsThreadSubscribed($rBefore->threadid,$this->session->get("IdMember"))) {
                $this->UnsubscribeThreadDirect($rBefore->threadid,$this->session->get("IdMember")) ;
			}
        }

        $this->prepare_notification($this->messageId,"useredit") ; // Prepare a notification
        MOD_log::get()->write("Editing Post=#".$this->messageId." Text Before=<i>".addslashes($rBefore->message)."</i> <br /> NotifyMe=[".$vars['NotifyMe']."]", "Forum");
    } // editPost

    /**
    *	editTopic write the data in of change thread in the database
    *	warning : dont start any transaction in it since there is already one
    *  started by the caller
    * this also write a log
    */

    private function editTopic($vars, $threadid)     {
        $query = sprintf("
UPDATE `forums_threads`
SET `title` = '%s'
WHERE `id` = '%d' ",
            $this->dao->escape(strip_tags($vars['topic_title'])),
            $threadid
        );

        $this->dao->query($query);

        $s=$this->dao->query("select IdWriter,forums_threads.id as IdThread,forums_threads.IdTitle,forums_threads.IdFirstLanguageUsed as thread_IdFirstLanguageUsed
		from forums_threads,forums_posts
		where forums_threads.first_postid=forums_posts.id and forums_threads.id=".$threadid);
        if (!$s) {
            throw new PException('editTopic:: previous info for first post in the thread!');
        }
        $rBefore = $s->fetch(PDB::FETCH_OBJ);

		 $this->ReplaceInFTrad($this->dao->escape(strip_tags($vars['topic_title'])), "forums_threads.IdTitle", $rBefore->IdThread, $rBefore->IdTitle, $rBefore->IdWriter) ;

		 // case the update concerns the reference language of the posts
		if ($rBefore->thread_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	$query="update forums_threads set title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forums_threads.id=".$rBefore->IdThread ;
        	$s=$this->dao->query($query);
		}

         // Set ThreadVisibility
        $query = 'UPDATE forums_threads SET ThreadVisibility = "' . $vars['ThreadVisibility'] . '" WHERE forums_threads.id=' . $rBefore->IdThread;
        $s =$this->dao->query($query);

        MOD_log::get()->write("Editing Topic Thread=#".$threadid, "Forum");
    } // end of editTopic

    public function replyProcess($suggestion = false) {
        if (!($User  = $this->getLoggedInMember())) {
            return false;
        }

        $vars =& PPostHandler::getVars();
        $this->checkVarsReply($vars);
        $this->replyTopic($vars);

        PPostHandler::clearVars();
        return PVars::getObj('env')->baseuri.$this->forums_uri.'s'.$this->threadid;
    } // end of replyProcess

    public function reportpostProcess() {
        if (!($User  = $this->getLoggedInMember())) {
            return false;
        }

        $vars =& PPostHandler::getVars();
		$IdPost=$vars['IdPost'] ;

        $ss = "select threadid from forums_posts where id=".$IdPost ;
        $s = $this->dao->query($ss);
		$rr = $s->fetch(PDB::FETCH_OBJ) ;

		$this->threadid=$rr->threadid ;

		$PostComment=$vars['PostComment'] ;
		$Status=$vars['Status'] ;
		if (isset($vars['Type'])) {
            $Type=$vars['Type'] ;
        } else {
		    $Type = 'SeeText';
        }
		if (!empty($vars['IdReporter'])) {
			$IdReporter=$vars['IdReporter'] ;
		}
		else {
			$IdReporter=$this->session->get("IdMember") ;
		}

        $ss = "select reports_to_moderators.* from reports_to_moderators where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
        $s = $this->dao->query($ss);
		$OldReport = $s->fetch(PDB::FETCH_OBJ) ;


		$UsernameAddTime='On '.date("d-m-Y").' '.date("H:i"). ' (server time) <a href="members/'.$this->session->get("Username").'">'.$this->session->get("Username").'</a> wrote:<br/>' ;
		if (($this->BW_Right->HasRight("ForumModerator")) and (isset($OldReport->IdReporter))) {
			$PostComment=$UsernameAddTime.$this->cleanupText($vars['PostComment']) ;
			if (isset($OldReport->PostComment)) $PostComment=$PostComment."<hr />\n".$OldReport->PostComment ;
			$ss="update reports_to_moderators set  LastWhoSpoke='Moderator',PostComment='".$this->dao->escape($PostComment)."',IdModerator=".$this->session->get("IdMember").",Status='".$this->dao->escape($Status)."',Type='".$this->dao->escape($Type)."',IdModerator=".$this->session->get('IdMember')." where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
			$this->dao->query($ss);
		}
		else {
			if ($IdReporter!=$this->session->get("IdMember")) {
			    MOD_log::get()->write("Trying to trick report to moderator for post #".$IdPost,"Forum") ;
				die("Failed to report to moderator") ;
			}
			if (isset($OldReport->IdReporter)) {
				$PostComment=$UsernameAddTime.$this->cleanupText($vars['PostComment'])."<hr />\n".$OldReport->PostComment ;
				$ss="update reports_to_moderators set LastWhoSpoke='Member',PostComment='".$this->dao->escape($PostComment)."',Status='".$this->dao->escape($Status)."'"." where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
				$this->dao->query($ss);
			}
			else {
				$PostComment=$UsernameAddTime.$this->cleanupText($vars['PostComment']) ;
				$ss="
                    insert into reports_to_moderators(PostComment,created,IdPost,IdReporter,Status)
                        values('".$this->dao->escape($PostComment)."',now(),".$IdPost.",".$this->session->get("IdMember") .",'".$Status."')" ;
				$this->dao->query($ss);
			}
		}
	    MOD_log::get()->write("Adding to report for post #".$IdPost."<br />".$PostComment."<br />Status=".$Status,"Forum") ;
        PPostHandler::clearVars();
        return PVars::getObj('env')->baseuri.$this->forums_uri.'s'.$this->threadid.'/#'.$IdPost;
    } // end of reportpostProcess

	 /**
    * This will return the list of reports for a given post
    * @IdPost : Id of the post to process  with their status
    * @IdReporter : OPtional id of teh member for a specific report, in this case a record is returned7
	*				in other case an array of reports is returned
	*/
	public function GetReports($IdPost,$IdReporter=0) {
		$tt=array() ;
		if (empty($IdReporter)) {
			$ss = "select reports_to_moderators.*,Username from reports_to_moderators,members where IdPost=".$IdPost." and members.id=IdReporter" ;
			$s = $this->dao->query($ss);
			while ($rr = $s->fetch(PDB::FETCH_OBJ)) {
				array_push($tt,$rr) ;
			}
			return($tt) ;
		}
		else {
			$ss = "select IdReporter from reports_to_moderators where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
			$s = $this->dao->query($ss);
			array_push($tt,$s->fetch(PDB::FETCH_OBJ)) ;
			return($tt)  ;
		}
	} // end of GetReports
	 /**
    * This will prepare the additional data to process a report
    * @IdPost : Id of the post to process
	* @IdWriter is the id of the writer
	 */
    public function prepareReportPost($IdPost,$IdWriter) {
        $query = "select * from reports_to_moderators where IdPost=".$IdPost." and IdReporter=".$IdWriter ;
        $s = $this->dao->query($query);
		$Report = $s->fetch(PDB::FETCH_OBJ) ;
		return($Report) ;
	}	// end of prepareReportPost

	/**
		This function loads the list of links to reports
		@IdMember : if to 0 it means for all moderators, if not 0 it mean for a given moderator
		@StatusList : is the list of reports status to consider, if empty it means all status

		returns an array
	*/
	public function prepareReportList($IdMember,$StatusList) { // This retrieve all the reports for the a member or all members

		$ss = "select reports_to_moderators.*,Username from reports_to_moderators,members where members.id=IdReporter " ;
		if (!empty($StatusList)) {
			$ss=$ss." and reports_to_moderators.Status in ".$StatusList ;
		}

		$tt=array() ;
		if (!empty($IdMember)) {
			$ss=$ss." and reports_to_moderators.IdModerator=".$IdMember ;
		}
		$ss=$ss." order by reports_to_moderators.updated" ;
        $s = $this->dao->query($ss);
		while ($rr = $s->fetch(PDB::FETCH_OBJ)) {
			array_push($tt,$rr) ;
		}
		return($tt) ;
	} // end of prepareReportList

	/**
		This function count the list of links to reports
		@IdMember : if to 0 it means for all moderators, if not 0 it mean for a given moderator
		@StatusList : is the list of reports status to consider, if empty it means all status
		returns and integer
	*/
	public function countReportList($IdMember,$StatusList) { // This count all the reports for and optional members or all members according to their styatus
		$ss = "select count(*) as cnt from reports_to_moderators,members where members.id=IdReporter " ;
		if (!empty($StatusList)) {
			$ss=$ss." and reports_to_moderators.Status in ".$StatusList ;
		}
        $s = $this->dao->query($ss);
		if ($rr = $s->fetch(PDB::FETCH_OBJ)) {
			return($rr->cnt) ;
		}
		return(0) ;
	} // end of countReportList



	/**
    * This will prepare a post for a full edit moderator action
    * @IdPost : Id of the post to process
	 */
    public function prepareModeratorEditPost($IdPost, $moderator = false) {
        $DataPost = new StdClass();
	 	$DataPost->IdPost=$IdPost ;
		$DataPost->Error="" ; // This will receive the error sentence if any

        $query = "select forums_posts.*,members.Status as memberstatus,members.UserName as UserNamePoster from forums_posts,members where forums_posts.id=".$IdPost." and IdWriter=members.id" ;
        $s = $this->dao->query($query);
		$DataPost->Post = $s->fetch(PDB::FETCH_OBJ) ;

		if (!isset($DataPost->Post)) {
		 	$DataPost->Error="No Post for Post=#".$IdPost ;
			return($DataPost) ;
		}

        if (!$moderator) {
            if ($DataPost->Post->PostVisibility == 'GroupOnly') {
                // first check if post was made in a group and if the current member is a member of that group
                $query = "SELECT IdGroup FROM forums_posts fp, forums_threads ft WHERE fp.id = " . $this->dao->escape($IdPost) . " AND fp.threadId = ft.id";
                $s = $this->dao->query($query);
                $row = $s->fetch(PDB::FETCH_OBJ);
                $IdGroup = $row->IdGroup;
                if ($IdGroup <> 0) {
                    $group = $this->createEntity('Group')->findByid($IdGroup);
                    $member = $this->getLoggedInMember();
                    // Can't use $group->isMember() for some reason
                    $query = " SELECT * FROM membersgroups WHERE IdMember = " . $member->id . " AND IdGroup = " . $IdGroup;
                    $s = $this->dao->query($query);
                    $row = $s->fetch(PDB::FETCH_OBJ);
                    if (!$row) {
                        $DataPost->Error="NoGroupMember";
                        return($DataPost) ;
                    }
                }
            }
            if ($DataPost->Post->PostVisibility == 'ModeratorOnly') {
                $DataPost->Error = "NoModerator";
                return $DataPost;
            }
        }

        // retrieve all trads for content
        $query = "select forum_trads.*,EnglishName,ShortCode,forum_trads.id as IdForumTrads from forum_trads,languages where IdLanguage=languages.id and IdTrad=".$DataPost->Post->IdContent." order by forum_trads.created asc" ;
        $s = $this->dao->query($query);
		 $DataPost->Post->Content=array() ;
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   $DataPost->Post->Content[]=$row ;
		 }


        $query = "select * from forums_threads where id=".$DataPost->Post->threadid ;
        $s = $this->dao->query($query);
		 if (!isset($DataPost->Post)) {
		 	$DataPost->Error="No Thread=#".$DataPost->Post->threadid ;
			return($DataPost) ;
		 }
		 $DataPost->Thread = $s->fetch(PDB::FETCH_OBJ) ;


// retrieve all trads for Title
        $query = "select forum_trads.*,EnglishName,ShortCode,forum_trads.id as IdForumTrads from forum_trads,languages where IdLanguage=languages.id and IdTrad=".$DataPost->Thread->IdTitle." order by forum_trads.created asc" ;
        $s = $this->dao->query($query);
		 $DataPost->Thread->Title=array() ;
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   array_push($DataPost->Thread->Title,$row) ;
		 }

		$DataPost->PossibleGroups=$this->ModeratorGroupChoice() ;
		return ($DataPost) ;
	 } // end of prepareModeratorEditPost


// This is what is called by the Full Moderator edit
// ---> ($vars["submit"]=="update thread")) means the Stick Value or the expire date of the thread have been updated and also the Group
// ---> ($vars["submit"]=="add translated title")) means that a new translated title is made available
// ---> ($vars["submit"]=="add translated post")) means that a new translated post is made available
// ---> ($vars["submit"]=="update post")) means the CanOwnerEdit has been updated
// ---> isset($vars["IdForumTrads"]) means that  on of the trad of the forum has been changed (title of one of the post)
// ---> ($vars["submit"]=="delete Tag")) means that the Tag IdTag is to be deleted
// ---> ($vars["submit"]=="Add Tag")) means that the Tag IdTag is to be added
    public function ModeratorEditPostProcess() {
     if (!($User  = $this->getLoggedInMember())) {
        return false;
     }

     $vars =& PPostHandler::getVars();
     if (isset($vars["submit"]) and ($vars["submit"]=="update thread")) { // if an effective update was chosen for a forum trads
        $IdThread=(int)$vars["IdThread"] ;
        $IdGroup=(int)$vars["IdGroup"] ;
        $ThreadVisibility=$vars["ThreadVisibility"] ;
        $ThreadDeleted=$vars["ThreadDeleted"] ;
        $WhoCanReply=$vars["WhoCanReply"] ;
        $expiredate="'".$vars["expiredate"]."'"  ;
        $stickyvalue=(int)$vars["stickyvalue"];
        if (empty($expiredate)) {
           $expiredate="NULL" ;
        }
        MOD_log::get()->write("Updating Thread=#".$IdThread." IdGroup=#".$IdGroup." Setting expiredate=[".$expiredate."] stickyvalue=".$stickyvalue." ThreadDeleted=".$ThreadDeleted." ThreadVisibility=".$ThreadVisibility." WhoCanReply=".$WhoCanReply,"ForumModerator");
        $sql="update forums_threads set ";
         if ($IdGroup === 0) {
             $sql .= "IdGroup = NULL";
         } else {
             $sql .= "IdGroup=" . $IdGroup;
         }
         $sql .= ",stickyvalue=".$stickyvalue.",expiredate=".$expiredate.",ThreadVisibility='".$ThreadVisibility."',ThreadDeleted='".$ThreadDeleted."',WhoCanReply='".$WhoCanReply."' where id=".$IdThread;

//			die ($sql) ;
        $this->dao->query($sql);
     }

		 if (isset($vars["submit"]) and ($vars["submit"]=="add translated title")) { // if a new translation is to be added for a title
		 	$IdThread=(int)$vars["IdThread"] ;
			$qry=$this->dao->query("select * from forum_trads where IdTrad=".$vars["IdTrad"]." and IdLanguage=".$vars["IdLanguage"]);
			$rr=$qry->fetch(PDB::FETCH_OBJ) ;
			if (empty($rr->id)) { // Only proceed if no such a title exists
		 		$ss=$vars["NewTranslatedTitle"]  ;
				$this->InsertInFTrad($ss,"forums_threads.IdTitle",$IdThread, $this->session->get("IdMember"), $vars["IdLanguage"],$vars["IdTrad"]) ;
				MOD_log::get()->write("Updating Thread=#".$IdThread." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
			}
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		 if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
		 	$OwnerCanStillEdit="'".$vars["OwnerCanStillEdit"]."'"  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."]","ForumModerator");
			$this->dao->query("update forums_posts set OwnerCanStillEdit=".$OwnerCanStillEdit." where id=".$IdPost);
		 }

		 if (isset($vars["submit"]) and ($vars["submit"]=="add translated post")) { // if a new translation is to be added for a title
		 		$IdPost=(int)$vars["IdPost"] ;
				$qry=$this->dao->query("select * from forum_trads where IdTrad=".$vars["IdTrad"]." and IdLanguage=".$vars["IdLanguage"]);
				$rr=$qry->fetch(PDB::FETCH_OBJ) ;
				if (empty($rr->id)) { // Only proceed if no such a post exists
		 			$ss=$this->dao->escape($vars["NewTranslatedPost"])  ;
					$this->InsertInFTrad($ss,"forums_posts.IdContent",$IdPost, $this->session->get("IdMember"), $vars["IdLanguage"],$vars["IdTrad"]) ;
       		MOD_log::get()->write("Updating Post=#".$IdPost." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
				}
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
            $IdThread=(int)$vars["IdThread"] ;
		 	$OwnerCanStillEdit=$vars["OwnerCanStillEdit"]  ;
		 	$PostVisibility=$vars["PostVisibility"]  ;
		 	$PostDeleted=$vars["PostDeleted"]  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."] PostVisibility=[".$PostVisibility."] PostDeleted=[".$PostDeleted."] ","ForumModerator");
			$this->dao->query("update forums_posts set OwnerCanStillEdit='".$OwnerCanStillEdit."',PostVisibility='".$PostVisibility."',PostDeleted='".$PostDeleted."' where id=".$IdPost);
                // Update last post id
                $query = "
                    SELECT
                        id
                    FROM
                       forums_posts
                    WHERE
                        threadid = " . $IdThread . "
                        AND PostDeleted = 'NotDeleted'
                    ORDER BY create_time DESC
                    LIMIT 1";
                $s = $this->dao->query($query);
                $row = $s->fetch(PDB::FETCH_OBJ);
                $id = $row->id;
                $update = "
                    UPDATE
                        forums_threads
                    SET
                        last_postid = " . $id . "
                    WHERE
                        id = " . $IdThread;
                $this->dao->query($update);
		}

 		if (isset($vars["IdForumTrads"])) { // if an effective update was chosen for a forum trads
		 			$this->DofTradUpdate($vars["IdForumTrads"],$vars["Sentence"],$vars["IdLanguage"]) ; // update the corresponding translations
		 }

     PPostHandler::clearVars();

     return PVars::getObj('env')->baseuri.$this->forums_uri.'modfulleditpost/'.$IdPost;
 		} // end of ModeratorEditPostProcess

    public function delProcess() {
        if (!($User  = $this->getLoggedInMember())) {
            return false;
        }

        if ($this->BW_Right->HasRight("ForumModerator","Delete")) {
            $this->dao->query("START TRANSACTION");
            $query = sprintf(
                "
SELECT
    `forums_posts`.`threadid`,
    `forums_threads`.`first_postid`,
    `forums_threads`.`last_postid`,
    `forums_threads`.`expiredate`,
    `forums_threads`.`stickyvalue`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`id`)
WHERE `forums_posts`.`id` = '%d'
                ",
                $this->messageId
            );
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could not retrieve Threadinfo!');
            }
            $topicinfo = $s->fetch(PDB::FETCH_OBJ);

            if ($topicinfo->first_postid == $this->messageId) { // Delete the complete topic

                $query =
                    "
UPDATE `forums_threads`
SET `first_postid` = NULL, `last_postid` = NULL
WHERE `id` = '$topicinfo->threadid'
                    "
                ;
                $this->dao->query($query);

                $query =
                    "
DELETE FROM `forums_posts`
WHERE `threadid` = '$topicinfo->threadid'
                    "
                ;
                $this->dao->query($query);
                MOD_log::get()->write("deleting posts where Thread=#". $topicinfo->threadid, "Forum");

                // Prepare a notification (before the delete !)
                $this->prepare_notification($this->messageId,"deletethread") ;

                $query =
                    "
DELETE FROM `forums_threads`
WHERE `id` = '$topicinfo->threadid'
                    "
                ;
                $this->dao->query($query);

                $redir = 'forums';
            } else { // Delete a single post
                /*
                * Check if we are deleting the very last post of a topic
                * if so, we have to update the `last_postid` field of the `forums_threads` table
                */
                if ($topicinfo->last_postid == $this->messageId) {
                    $query =
                        "
UPDATE `forums_threads`
SET `last_postid` = NULL
WHERE `id` = '$topicinfo->threadid'
                        "
                    ;
                    $this->dao->query($query);
                }
                MOD_log::get()->write("deleting single post where Post=#". $this->messageId, "Forum");

                $this->prepare_notification($this->messageId,"deletepost") ; // Prepare a notification (before the delete !)

                $query =
                    "
DELETE FROM `forums_posts`
WHERE `id` = '$this->messageId'
                    "
                ;
                $this->dao->query($query);

                if ($topicinfo->last_postid == $this->messageId) {
                    $query =
                        "
SELECT `forums_posts`.`id` AS `postid`
FROM `forums_posts`
WHERE `threadid` = '$topicinfo->threadid'
ORDER BY `create_time` DESC LIMIT 1
                        "
                    ;
                    $s = $this->dao->query($query);
                    if (!$s) {
                        throw new PException('Could not retrieve Postinfo!');
                    }
                    $lastpost = $s->fetch(PDB::FETCH_OBJ);

                    $lastpostupdate = sprintf(", `last_postid` = '%d'", $lastpost->postid);
                } else {
                    $lastpostupdate = '';
                }

                $query =
                    "
UPDATE `forums_threads`
SET `replies` = (`replies` - 1) $lastpostupdate
WHERE `id` = '$topicinfo->threadid'
                    "
                ;
                $this->dao->query($query);

                $redir = $this->forums_uri.'s'.$topicinfo->threadid;
            }

            $this->dao->query("COMMIT");
        }


        header('Location: '.PVars::getObj('env')->baseuri.$redir);
        PPHP::PExit();
    }


    private function checkVarsReply(&$vars) {
        $errors = array();

        if (!isset($vars['topic_text']) || empty($vars['topic_text'])) {
            $errors[] = 'text';
        }
        if ($errors) {
            $vars['errors'] = $errors;
            return false;
        }

        return true;
    }

    public function checkVarsTopic(&$vars) {
        $errors = array();

        if (!isset($vars['topic_title']) || empty($vars['topic_title'])) {
            $errors[] = 'title';
        }
        if (!isset($vars['topic_text']) || empty($vars['topic_text'])) {
            $errors[] = 'text';
        }

        if ($errors) {
            $vars['errors'] = $errors;
            return false;
        }

        return true;
    }

    private function replyTopic(&$vars) {
        if (!($User  = $this->getLoggedInMember())) {
            throw new PException('User gone missing...');
        }
        $IdGroup = 0;
        if (isset($vars['IdGroup'])) {
            $IdGroup = $vars['IdGroup'];
        }
        if (isset($vars['PostVisibility'])) {
            $postVisibility = $vars['PostVisibility'];
        } else {
            // Someone unchecked the box for group only posts
            $postVisibility = 'MembersOnly';
        }

        $this->dao->query("START TRANSACTION");
        $query = sprintf(
            "
INSERT INTO `forums_posts` ( `threadid`, `create_time`, `message`,`IdWriter`,`IdFirstLanguageUsed`,`PostVisibility`)
VALUES ('%d', NOW(), '%s','%d',%d,'%s')
            ",
            $this->threadid,
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            $this->session->get("IdMember"), $this->GetLanguageChoosen(), $postVisibility
        );

        $result = $this->dao->query($query);


        $postid = $result->insertId();

		 // Now create the text in forum_trads
 		 $this->InsertInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])),"forums_posts.IdContent",$postid) ;

        $query =
            "
UPDATE `forums_threads`
SET `last_postid` = '$postid', `replies` = `replies` + 1
WHERE `id` = '$this->threadid'
            "
        ;
        $this->dao->query($query);

        $this->dao->query("COMMIT");


        // subscription if any is out of transaction, this is not so important
        if ((isset($vars['NotifyMe'])) and ($vars['NotifyMe']=="on")) {
           if (!$this->IsThreadSubscribed($this->threadid,$this->session->get("IdMember"))) {
                 $this->SubscribeThread($this->threadid,$this->session->get("IdMember")) ;
           }
        }
        else {
           $vars['NotifyMe']="Not Asked" ;
           if ($this->IsThreadSubscribed($this->threadid,$this->session->get("IdMember"))) {
                 $this->UnsubscribeThreadDirect($this->threadid,$this->session->get("IdMember")) ;
           }
        }


        MOD_log::get()->write("Replying new Post=#". $postid." in Thread=#".$this->threadid." NotifyMe=[".$vars['NotifyMe']."]", "Forum");
        $this->prepare_notification($postid,"reply") ; // Prepare a notification

        return $postid;
    } // end of replyTopic

    /**
    * Create a new Topic (with initial first post)
    * @return int topicid Id of the newly created topic
    */
    public  function newTopic(&$vars) {
        if (!($User  = $this->getLoggedInMember())) {
            throw new PException('User gone missing...');
        }
        $IdGroup = null;
        if (isset($vars['IdGroup']) && $vars['IdGroup'] !== 0) {
            $IdGroup = $vars['IdGroup'];
        }
        if (isset($vars['ThreadVisibility'])) {
            $ThreadVisibility = $vars['ThreadVisibility'];
        } else {
            // Someone unchecked the box for group only posts
            if (isset($vars['groupOnly']) && ($vars['groupOnly'] === '1'))
            {
                $ThreadVisibility = 'GroupOnly';
            } else {
                $ThreadVisibility = 'MembersOnly';
            }
        }

        /** @var PDBStatement_mysqli $statement */
        $statement = $this->dao->prepare("
            INSERT INTO `forums_posts` (
                `create_time`, `message`,`IdWriter`,`IdFirstLanguageUsed`,`PostVisibility`)
            VALUES (NOW(), ?, ?, ?, ?)
            ");
        $text = $this->cleanupText($vars['topic_text']);
        $userId = $User->getId();
        $memberId = $this->session->get("IdMember");
        $language = $this->GetLanguageChoosen();
        $statement->bindParam(1, $text);
        $statement->bindParam(2, $memberId);
        $statement->bindParam(3, $language);
        $statement->bindParam(4,$ThreadVisibility);

        $result = $statement->execute();
        $postId = $statement->insertId();

        $this->InsertInFTrad(
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            "forums_posts.IdContent",
            $postId,
            $memberId,
            $language
        );

        $statement = $this->dao->prepare("
            INSERT INTO `forums_threads` (
                `title`, `first_postid`, `last_postid`,
                `IdFirstLanguageUsed`,`IdGroup`,`ThreadVisibility`)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

 		$title = strip_tags($vars['topic_title']);
 		$statement->bindParam(1, $title);
 		$statement->bindParam(2, $postId);
 		$statement->bindParam(3, $postId);
        $statement->bindParam(8, $language);
        $statement->bindParam(9, $IdGroup);
        $statement->bindParam(10, $ThreadVisibility);

        $result = $statement->execute();
        $threadId = $statement->insertId();

		$ss=$this->dao->escape(strip_tags(($vars['topic_title']))) ;
 		$this->InsertInFTrad($ss,"forums_threads.IdTitle",$threadId) ;

 		$statement = $this->dao->prepare("UPDATE `forums_posts` SET `threadId` = ? WHERE `Id` = ?");
        $statement->bindParam(1, $threadId);
        $statement->bindParam(2, $postId);

        $result = $statement->execute();

        // subscription if any is out of transaction, this is not so important
        if ((isset($vars['NotifyMe'])) and ($vars['NotifyMe']=="on")) {
                 $this->SubscribeThread($threadId,$this->session->get("IdMember")) ;
        }
        else {
             $vars['NotifyMe']="Not Asked" ;
        }

        $this->prepare_notification($postId,"newthread") ; // Prepare a notification
        MOD_log::get()->write("New Thread new Tread=#".$threadId." Post=#". $postId." IdGroup=#".$IdGroup." NotifyMe=[".$vars['NotifyMe']."] initial Visibility=".$ThreadVisibility, "Forum");

        return $threadId;
    } // end of NewTopic

    private $topic;

    /**
     * function prepareTopic prepares the detail of a topic for display according to threadid
     * if @$WithDetail is set to true, additional details (available languages and original author are displayed)
     */
    public function prepareTopic($WithDetail=false) {
        $this->topic = new Topic();
        $this->topic->WithDetail = $WithDetail;

        // Topic Data
        $query = "
           SELECT
               `forums_threads`.`title`,
               `forums_threads`.`IdTitle`,
               `forums_threads`.`replies`,
               `forums_threads`.`id` as IdThread,
               `forums_threads`.`views`,
               `forums_threads`.`ThreadVisibility`,
               `forums_threads`.`ThreadDeleted`,
               `forums_threads`.`WhoCanReply`,
               `forums_threads`.`first_postid`,
               `forums_threads`.`expiredate`,
               `forums_threads`.`stickyvalue`,
               `forums_threads`.`IdGroup`,
                `groups`.`Name` AS `GroupName`
            FROM
                `forums_threads`
            LEFT JOIN `groups` ON (`forums_threads`.`IdGroup` = `groups`.`id`)
            WHERE
                `forums_threads`.`id` = '$this->threadid'
                AND ($this->PublicThreadVisibility)
                AND ($this->ThreadGroupsRestriction)
        ";

        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Thread=#".$this->threadid." !');
        }

        $topicinfo = $s->fetch(PDB::FETCH_OBJ);
		// Check if any result was found
		if (!$topicinfo) {
			$topicinfo = new stdClass;
		}
		if (isset($topicinfo->WhoCanReply)) {
			if ($topicinfo->WhoCanReply=="MembersOnly") {
				$topicinfo->CanReply=true ;
			}
			else if ($topicinfo->WhoCanReply=="GroupsMembersOnly") {
				if ($topicinfo->IdGroup==0) {
					$topicinfo->CanReply=true ;
				}
				else {
					$topicinfo->CanReply=in_array($topicinfo->IdGroup,$this->MyGroups) ; // Set to true only if current member is member of the group
				}
			}
			else if ($topicinfo->WhoCanReply=="ModeratorOnly") {
				if ($this->BW_Right->HasRight("ForumModerator")) {
					$topicinfo->CanReply=true ;
				}
				else {
					$topicinfo->CanReply=false ;
				}
			}
			else {
				$topicinfo->CanReply=false ;
			}
		}

        $this->topic->topicinfo = $topicinfo;
        $this->topic->IdThread=$this->threadid ;

        $from = $this->POSTS_PER_PAGE * ($this->getPage() - 1);
        $order = ($this->ForumOrderList == "Yes") ? "ASC" : "DESC";

        $query = sprintf("
            SELECT
                `forums_posts`.`id` AS `postid`,
                forums_posts.id as IdPost,
                UNIX_TIMESTAMP(create_time) AS posttime,
                message,
                IdContent,
                IdWriter,
                geonames.name AS city,
                geonamescountries.name AS country,
                forums_posts.threadid,
                OwnerCanStillEdit,
                members.Username as OwnerUsername,
                PostVisibility,
                PostDeleted,
                forums_threads.IdGroup
            FROM
                forums_threads,
                forums_posts
            LEFT
                JOIN members ON forums_posts.IdWriter = members.id
            LEFT JOIN
                addresses AS a ON a.IdMember = members.id AND a.rank = 0
            LEFT JOIN
                geonames ON a.IdCity = geonames.geonameId
            LEFT JOIN
                geonamescountries ON geonames.country = geonamescountries.country
            WHERE
                forums_posts.threadid = '%d'
                AND forums_posts.threadid=forums_threads.id
                AND ({$this->PublicPostVisibility})
                AND ({$this->ThreadGroupsRestriction})
            ORDER BY
                posttime {$order}
            LIMIT %d, %d",
            $this->threadid,$from,$this->POSTS_PER_PAGE
        );

        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Posts)!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			if ($WithDetail) { // if details are required retrieve all the translated text for posts (sentence, owner, modification time and translator name) of this thread
				$sw = $this->dao->query("select  forum_trads.IdLanguage,UNIX_TIMESTAMP(forum_trads.created) as trad_created, UNIX_TIMESTAMP(forum_trads.updated) as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,languages.ShortCode,languages.EnglishName,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername
										from forum_trads,languages,members as mOwner, members as mTranslator
										where languages.id=forum_trads.IdLanguage and forum_trads.IdTrad=".$row->IdContent." and mOwner.id=IdOwner and mTranslator.id=IdTranslator order by forum_trads.id asc");
				while ($roww = $sw->fetch(PDB::FETCH_OBJ)) {
					$row->Trad[]=$roww ;
				}
			}
			$this->topic->posts[] = $row;
        } // end  // Now retrieve all the Posts of this thread

        // Check if the current user has subscribe to this thread or not (to display the proper option, subscribe or unsubscribe)
        if ($this->session->has( "IdMember" )) {
			$memberId = $this->session->get("IdMember");
			$query = "
SELECT
    `members_threads_subscribed`.`id` AS IdSubscribe,
    `members_threads_subscribed`.`UnSubscribeKey` AS IdKey,
    `members_threads_subscribed`.`notificationsEnabled` AS notificationsEnabled
FROM members_threads_subscribed
WHERE IdThread = {$this->threadid}
AND IdSubscriber = {$memberId}";
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could if has subscribed to Thread=#".$this->threadid." !');
            }
            $row = $s->fetch(PDB::FETCH_OBJ) ;
            if (isset($row->IdSubscribe)) {
				$this->topic->notificationsEnabled = $row->notificationsEnabled;
                $this->topic->IdSubscribe= $row->IdSubscribe ;
                $this->topic->IdKey= $row->IdKey ;
            }
            if (isset($this->topic->topicinfo->IdGroup)) {
                // Check if member has enabled group mails
                $group = $this->createEntity('Group', $this->topic->topicinfo->IdGroup);
                $member = $this->createEntity('Member', $this->session->get("IdMember"));
                $membership = $this->createEntity('GroupMembership')->getMembership($group, $member);
                if ($membership) {
					$this->topic->isGroupSubscribed = ($membership->IacceptMassMailFromThisGroup == 'yes');
					if (!isset($row->IdSubscribe)) {
						$this->topic->notificationsEnabled = ($membership->notificationsEnabled);
					}
                }
			}
		}

        // Increase the number of views
        $query = "
UPDATE `forums_threads`
SET `views` = (`views` + 1)
WHERE `id` = '$this->threadid' LIMIT 1
            "     ;
        $this->dao->query($query);

    } // end of prepareTopic

    public function initLastPosts() {
        $query = sprintf("
SELECT
    `forums_posts`.`id` AS `postid`,
    UNIX_TIMESTAMP(`create_time`) AS `posttime`,
    `message`,
	`IdContent`,
    `members`.`Username` AS `OwnerUsername`,
    `IdWriter`,
	 forums_threads.`id` as `threadid`,
    `PostVisibility`,
    `PostDeleted`,
    `ThreadDeleted`,
	`OwnerCanStillEdit`,
    `geonames`.`name` as `city`,
    `geonamescountries`.`name` as `country`,
    `IdGroup`
FROM forums_posts, forums_threads, members, addresses
LEFT JOIN `geonames` ON (addresses.IdCity = `geonames`.`geonameId`)
LEFT JOIN `geonamescountries` ON (geonames.country = `geonamescountries`.`country`)
WHERE `forums_posts`.`threadid` = '%d' AND `forums_posts`.`IdWriter` = `members`.`id`
AND addresses.IdMember = members.id AND addresses.rank = 0
 AND `forums_posts`.`threadid`=`forums_threads`.`id`
	and ({$this->PublicPostVisibility})
	and ({$this->PublicThreadVisibility})
	and ({$this->PostGroupsRestriction})
	and ThreadDeleted <> 'Deleted'
	And PostDeleted <> 'Deleted'
ORDER BY `posttime` DESC
LIMIT %d
            ",
            $this->threadid,
            Forums::NUMBER_LAST_POSTS_PREVIEW
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Posts!');
        }
        $this->topic->posts = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
          	$sw = $this->dao->query("select  forum_trads.IdLanguage,UNIX_TIMESTAMP(forum_trads.created) as trad_created, UNIX_TIMESTAMP(forum_trads.updated) as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,languages.ShortCode,languages.EnglishName,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,languages,members as mOwner, members as mTranslator
			                           where languages.id=forum_trads.IdLanguage and forum_trads.IdTrad=".$row->IdContent." and mOwner.id=IdOwner and mTranslator.id=IdTranslator order by forum_trads.id asc");
        	  while ($roww = $sw->fetch(PDB::FETCH_OBJ)) {
			    		$row->Trad[]=$roww ;
						}
            $this->topic->posts[] = $row;
        }
    } // end of initLastPosts

    private function updateSubscriptions($memberId, $enable) {
        // update subscription (keep old assignments through negating if disabling)
        // members_threads_subscribed
        $query ="
            UPDATE
                members_threads_subscribed
            SET
                notificationsEnabled = '" . ($enable ? 1 : 0) . "'
            WHERE
                IdSubscriber = " . $memberId;
        $this->dao->query($query);
		$this->updateGroupNotifications($memberId, $enable);
        $this->dao->query($query);
    }

	private function updateGroupNotifications($memberId, $enable) {
		$query = "
            UPDATE
                membersgroups
            SET
                notificationsEnabled = '" . ($enable ? 1 : 0) . "'
            WHERE
                IdMember = " . $memberId . "
                AND IacceptMassMailFromThisGroup = 'Yes'
        ";
		$this->dao->query($query);
	}

	private function updateGroupNotification($groupId, $memberId, $enable) {
		$query = "
            UPDATE
                membersgroups
            SET
                notificationsEnabled = '" . ($enable ? 1 : 0) . "'
            WHERE
            	IdGroup = " . $groupId . "
                AND IdMember = " . $memberId . "
                AND IacceptMassMailFromThisGroup = 'Yes'
        ";
		$this->dao->query($query);
	}

	public function disableSubscriptions() {
		$member = $this->getLoggedInMember();
		if ($member) {
			$this->updateSubscriptions($member->id, false);
		}
	}

	public function enableSubscriptions() {
		$member = $this->getLoggedInMember();
		if ($member) {
			$this->updateSubscriptions($member->id, true);
		}
	}

	public function disableGroup($IdGroup) {
		$member = $this->getLoggedInMember();
		if ($member) {
			$this->updateGroupNotification($IdGroup, $member->id, false);
		}
	}

	public function enableGroup($IdGroup) {
		$member = $this->getLoggedInMember();
		if ($member) {
			$this->updateGroupNotification($IdGroup, $member->id, true);
		}
	}

	public function subscribeGroup($IdGroup) {
		$member = $this->getLoggedInMember();
		if ($member) {
			$group = $this->createEntity('Group', $IdGroup);
			if (!($membership = $this->createEntity('GroupMembership')->getMembership($group, $member)))
			{
				return false;
			}

			$membership->updateMembership('yes', $membership->Comment);
		}
	}

	public function unsubscribeGroup($IdGroup) {
		$member = $this->getLoggedInMember();
		if ($member) {
			$group = $this->createEntity('Group', $IdGroup);
			if (!($membership = $this->createEntity('GroupMembership')->getMembership($group, $member)))
			{
				return false;
			}

			$membership->updateMembership('no', $membership->Comment);
		}
	}

	/**
     * This function retrieve the subscriptions for the member $cid and/or the the thread IdThread and/or theIdTag
     * @$cid : either the IdMember or the username of the member we are searching the subscription
     * this $cid and $IdThread and $IdTag parameters are only used if the current member has moderator rights
     * It returns a $TResults structure
     * Very important  : member who are not moderators cannot see other people subscriptions
     * @param bool $IdThread
     * @param bool $IdTag
     * @return StdClass
     * @throws PException
     */
    public function searchSubscriptions() {
        $member= $this->getLoggedInMember();
        if (!$member) {
            return array();
        }
        $TResults = new StdClass();
        $query = "
SELECT
    `members_threads_subscribed`.`id` as IdSubscribe,
    `members_threads_subscribed`.`created` AS `subscribedtime`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forums_threads`.`id` as IdThread,
    `forums_threads`.`title`,
    `forums_threads`.`IdTitle`,
    `members_threads_subscribed`.`ActionToWatch`,
    `members_threads_subscribed`.`UnSubscribeKey`,
    `members_threads_subscribed`.`notificationsEnabled`
FROM `forums_threads`,`members_threads_subscribed`
WHERE `forums_threads`.`id` = `members_threads_subscribed`.`IdThread`
AND `members_threads_subscribed`.`IdSubscriber`= {$member->id}
ORDER BY `subscribedtime` DESC
                ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve members_threads_subscribed sts via searchSubscription !');
        }

        $TResults->TData = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $TResults->TData[] = $row;
        }

        $query = "
            SELECT
                Name, IdGroup, IdMember, IacceptMassMailFromThisGroup As AcceptMails, notificationsEnabled
            FROM
                `membersgroups` mg,
                `groups` g
            WHERE
                g.id = mg.IdGroup
                AND IdMember = '{$member->id}'
                AND Status = 'In'
            ORDER BY
                Name";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could load group memberships');
        }
        $TResults->Groups = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $TResults->Groups[] = $row;
        }
        return $TResults;
    } // end of searchSubscriptions


    /**
     * This function remove the subscription marked by IdSubscribe
     * @IdSubscribe is the primary key of the members_threads_subscribed area to remove
     * @Key is  the key to check to be sure it is not an abuse of url
     * It returns a $res=1 if ok
     */
    public function UnsubscribeThread($IdSubscribe=0,$Key="") {
        $query = sprintf(
            "
SELECT
    members_threads_subscribed.id AS IdSubscribe,
    IdThread,
    IdSubscriber,
    Username from members,
    members_threads_subscribed
WHERE members.id=members_threads_subscribed.IdSubscriber
AND members_threads_subscribed.id=%d
AND UnSubscribeKey='%s'
            ",
            $IdSubscribe,$this->dao->escape($Key)
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->UnsubscribeThread Could not retrieve the subscription !');
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        if (!isset($row->IdSubscribe)) {
            MOD_log::get()->write("No entry found while Trying to unsubscribe thread  IdSubscribe=#".$IdSubscribe." IdKey=".$Key, "Forum");
            return(false) ;
        }
        $query = sprintf(
            "
DELETE
FROM members_threads_subscribed
WHERE id=%d
AND UnSubscribeKey='%s'
            ",
            $IdSubscribe,
            $this->dao->escape($Key)
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->UnsubscribeThread delete failed !');
        }
        if ($this->session->has( "IdMember" )) {
            MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from Thread=#".$row->IdThread, "Forum");
            if ($this->session->get("IdMember")!=$row->IdSubscriber) { // If it is not the member himself, log a forum action in addition
                MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from Thread=#".$row->IdThread, "ForumModerator");
            }
        }
        else {
            MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from Thread=#".$row->IdThread." without beeing logged", "Forum");
        }
        return(true) ;
    } // end of UnsubscribeThread

    /**
     * This function remove the subscription without checking the key
     *
     * @param unknown_type $IdThread the id of the thread to unsubscribe to
     * @param unknown_type $ParamIdMember the member to unsubscribe, if 0, the current member will eb used
     * @return unknown
     */
    public function UnsubscribeThreadDirect($IdThread=0,$ParamIdMember=0) {
        $IdMember=$ParamIdMember ;
        if ($this->session->has( "IdMember" ) and $IdMember==0) {
            $IdMember=$this->session->get("IdMember") ;
        }

			 if ($IdMember==0) { // No need to do something if no member is logged
			 		return ;
			 }

        $query = sprintf(
            "
DELETE
FROM members_threads_subscribed
WHERE IdSubscriber=%d
AND IdThread=%d
            ",
            $IdMember,
            $IdThread
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->UnsubscribeThreadDirect failed to delete !');
        }
            MOD_log::get()->write("Unsubscribing direct (By NotifyMe) member=#".$IdMember." from Thread=#".$IdThread, "Forum");
        return(true) ;
    } // end of UnsubscribeThreadDirect


    /**
     * This function allow to subscribe to a thread
     *
     * @$IdThread : The thread we want the user to subscribe to
     * @$ParamIdMember optional IdMember, by default set to 0 in this case current logged member will be used
     * It also check that member is not yet subscribing to thread
     */
    public function SubscribeThread($IdThread,$ParamIdMember=0) {
        $IdMember=$ParamIdMember ;
        if ($this->session->has( "IdMember" ) and $IdMember==0) {
            $IdMember=$this->session->get("IdMember") ;
        }

        // Check if there is a previous Subscription
        if ($this->IsThreadSubscribed($IdThread,$this->session->get("IdMember"))) {
            MOD_log::get()->write("Allready subscribed to Thread=#".$IdThread, "Forum");
            return(false) ;
        }
        $key=MD5(rand(100000,900000)) ;
        $query = "INSERT INTO
                members_threads_subscribed(IdThread,IdSubscriber,UnSubscribeKey,notificationsEnabled)
                VALUES(".$IdThread.",".$this->session->get("IdMember").",'".$this->dao->escape($key)."', 1)" ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->SubscribeThread failed !');
        }
        $IdSubscribe= $s->insertId() ;
        MOD_log::get()->write("Subscribing to Thread=#".$IdThread." IdSubscribe=#".$IdSubscribe, "Forum");
    } // end of UnsubscribeThread

    /**
     * This function allows to enable a thread that has been disabled
     *
     * @$IdThread : The thread we want the user to subscribe to
     * @$ParamIdMember optional IdMember, by default set to 0 in this case current logged member will be used
     * It also check that member is not yet subscribing to thread
     */
    public function EnableThread($IdThread) {
        $member = $this->getLoggedInMember();
        if (!$member) {
            return;
        }

        // Check if there is a previous Subscription
        if ($this->IsThreadSubscribed($IdThread,$member->id)) {
            $query = "
                UPDATE
                    members_threads_subscribed
                SET
                    notificationsEnabled = '1'
                WHERE
                    IdThread = " . $IdThread . "
                    AND IdSubscriber = " . $member->id;
            $this->dao->query($query);
        }
    } // end of EnableThread

	/**
	 * This function allows to disable notifications for a thread if the group has been subscribed
	 *
	 * @$IdThread : The thread we want the user to disable
	 */
	public function DisableThread($IdThread) {
		$member = $this->getLoggedInMember();
		if (!$member) {
			return;
		}

		// Make sure there is something to disable
		if (!$this->IsThreadSubscribed($IdThread,$member->id)) {
			$this->SubscribeThread($IdThread);
		}

		// if there was already a disable notification this won't change it.
		$query = "
            UPDATE
                members_threads_subscribed
            SET
                notificationsEnabled = '0'
            WHERE
                IdThread = " . $IdThread . "
                AND IdSubscriber = " . $member->id;
		$this->dao->query($query);
	} // end of DisableThread

    // This function retrieve search post of the member $cid
    //@$cid : either the IdMember or the username of the member we are searching the post
    public function searchUserposts($cid=0) {
        $IdMember=0 ;
        if (is_numeric($cid)) {
           $IdMember=$cid ;
        }
        else
        {
            if (!($member = $this->createEntity('Member')->findByUsername($cid)))
            {
                throw new PException('Could not retrieve members id via username !');
            }
            $IdMember = $member->id;
        }

        $query = sprintf(
            "SELECT    `forums_posts`.`id` AS `postid`,`forums_posts`.`id` as IdPost, UNIX_TIMESTAMP(`create_time`) AS `posttime`,  `message`,
    `OwnerCanStillEdit`,`IdContent`,  `forums_threads`.`id` AS `threadid`,   `forums_threads`.`title`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `PostVisibility`,
    `PostDeleted`,
    `ThreadDeleted`,
    `forums_threads`.`IdTitle`,`forums_threads`.`IdGroup`,   `IdWriter`,   `members`.`Username` AS `OwnerUsername`, `groups`.`Name` AS `GroupName`,    `geonames`.`country`
		FROM (forums_posts, members, forums_threads, addresses)
LEFT JOIN `groups` ON (`forums_threads`.`IdGroup` = `groups`.`id`)
LEFT JOIN `geonames` ON (addresses.IdCity = geonames.geonameId)
WHERE `forums_posts`.`IdWriter` = %d AND `forums_posts`.`IdWriter` = `members`.`id`
AND `forums_posts`.`threadid` = `forums_threads`.`id`
AND addresses.IdMember = members.id AND addresses.rank = 0
AND ($this->PublicThreadVisibility)
AND ($this->PublicPostVisibility)
AND ($this->PostGroupsRestriction)
ORDER BY `posttime` DESC",    $IdMember   );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Posts via searchUserposts !');
        }
        $posts = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
          	$sw = $this->dao->query("select forum_trads.IdLanguage,UNIX_TIMESTAMP(forum_trads.created) as trad_created, UNIX_TIMESTAMP(forum_trads.updated) as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,languages.ShortCode,languages.EnglishName,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,languages,members as mOwner,members as mTranslator    where languages.id=forum_trads.IdLanguage and forum_trads.IdTrad=".$row->IdContent." and mTranslator.id=IdTranslator  and mOwner.id=IdOwner order by forum_trads.id asc");
        	  while ($roww = $sw->fetch(PDB::FETCH_OBJ)) {
			    			$row->Trad[]=$roww ;
			  		}
          	$posts[] = $row;

        } //

        return $posts;
    } // end of searchUserposts

    public function getTopic() {
        return $this->topic;
    }

    /**
    * Check if it's a topic or a forum
    * @return bool true on topic
    * @return bool false on forum
    */
    public function isTopic() {
        return (bool) $this->threadid;
    }

    private $threadid = 0;
    private $page = 1;
    private $page_array = array();
    private $messageId = 0;
    private $TopMode=Forums::CV_TOPMODE_LANDING; // define that we use the landing page for top mode

    public function setTopMode($Mode) {
        $this->TopMode = $Mode ;
    }
    public function getTopMode() {
        return $this->TopMode;
    }
    public function setGroupId($IdGroup) {
        $this->IdGroup = (int) $IdGroup;
    }
    public function setThreadId($threadid) {
        $this->threadid = (int) $threadid;
    }
    public function getThreadId() {
        return $this->threadid;
    }
    public function getIdGroup() {
        return $this->IdGroup;
    }
    public function getPage() {
        return $this->page;
    }
    public function setPage($page) {
        $this->page = (int) $page;
    }
    public function getPageArray() {
        return $this->page_array;
    }
    public function setPageArray($page_array) {
        $this->page_array = $page_array;
    }
    public function pushToPageArray($page) {
        $this->page_array[] = $page;
    }
    public function setMessageId($messageid) {
        $this->messageId = (int) $messageid;
    }
    public function getMessageId() {
        return $this->messageId;
    }

    public function getIdContent() { // Return the IdContent (IdTrad for the id of the post, according to currently set $this->messageId
				$IdContent=-1 ;
        $query = "select `IdContent` from `forums_posts` where `id`=".$this->messageId ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum-> getIdContent failed for this->messageId='.$this->messageId);
        }
				$row = $s->fetch(PDB::FETCH_OBJ) ;
				if (isset ($row->IdContent)) {
					$IdContent=$row->IdContent ;
				}
        return $IdContent;
    }

    /*
     * cleanupText
     *
     * @param string $txt
     * @access private
     * @return string
     */
    private function cleanupText($txt)
    {
        $purifier = MOD_htmlpure::get()->getForumsHtmlPurifier();

        return $purifier->purify($txt);
    } // end of cleanupText

    function GetLanguageName($IdLanguage) {
        $query="select id as IdLanguage,Name,EnglishName,ShortCode,WordCode from languages where id=".($IdLanguage)
            . " AND IsWrittenLanguage = 1";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve IdLanguage in GetLanguageName entries');
        } else {
            $row = $s->fetch(PDB::FETCH_OBJ) ;
            $row->Name = $this->getWords()->getSilent($row->WordCode) . " (" . $row->Name . ")";
            return($row) ;
        }
        return("not Found") ;
    } // end of GetLanguageName


    // This fonction will prepare a list of language to choose
    // @DefIdLanguage : an optional language to use
	 // return an array of object with LanguageName and IdLanguage
	 public function LanguageChoices($DefIdLanguage=-1) {


	 		$tt=array() ;
			$allreadyin=array() ;
			$ii=0 ;

// First proposed will deflanguage
//			if (!empty($DefIdLanguage) and ($DefIdLanguage>=0)) {
			if (($DefIdLanguage>=0)) {
			   $row=$this->GetLanguageName($DefIdLanguage) ;
		   	   array_push($allreadyin,$row->IdLanguage) ;
			   array_push($tt, "CurrentLanguage");
		   	   array_push($tt,$row) ;
			}
			// Then next will be english (if not allready in the list)
			if (!in_array(0,$allreadyin)) {
			   $row=$this->GetLanguageName(0) ;
		   	   array_push($allreadyin,$row->IdLanguage) ;
			   array_push($tt, "DefaultLanguage");
		   	   array_push($tt,$row) ;
			}
			// Then next will the current user language
			if (($this->session->has( "IdLanguage" ) and (!in_array($this->session->get("IdLanguage"),$allreadyin)))) {
			   $row=$this->GetLanguageName($this->session->get("IdLanguage")) ;
		   	   array_push($allreadyin,$row->IdLanguage) ;
			   array_push($tt, "UILanguage");
		   	   array_push($tt,$row);
			}

			array_push($tt, "AllLanguages");
			// then now all available languages
			$query="select id as IdLanguage,Name,EnglishName,ShortCode,WordCode from languages where id>0"
			    . " AND IsWrittenLanguage = 1";
          	$s = $this->dao->query($query);
          	$langarr = array();
        	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			   if (!in_array($row->IdLanguage,$allreadyin)) {
			   	  array_push($allreadyin,$row->IdLanguage) ;
			   	  $row->Name = $this->getWords()->getSilent($row->WordCode) . " (" . trim($row->Name) . ")";
			  	  $langarr[] = $row;
			   }
			}
			// now sort langarr and append to the $tt array
			usort($langarr, "cmpForumLang");
			return($tt + $langarr) ; // returs the array of structures
    } // end of LanguageChoices

    // This fonction will prepare a list of group in an array that the moderator can use
	 public function ModeratorGroupChoice() {
	 		$tt=array() ;

			$query="select groups.id as IdGroup,Name,count(*) as cnt from groups,membersgroups
										 WHERE membersgroups.IdGroup=groups.id group by groups.id order by Name ";
      $s = $this->dao->query($query);
      while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$row->GroupName=$row->Name=$this->getGroupName($row->Name);
	  	  array_push($tt,$row) ;
			}
			return($tt) ; // returs the array of structures

	 } // end of ModeratorGroupChoices

    // This fonction will prepare a list of group in an array that the user can use
		// (according to his member ship)
	 public function GroupChoice() {
 		$tt=array() ;

		$query="select groups.id as IdGroup,Name,count(*) as cnt from groups,membersgroups,members
										 WHERE membersgroups.IdGroup=groups.id and members.id=membersgroups.IdMember and
										  members.Status in ('Active','ActiveHidden') and members.id=".$this->session->get('IdMember')." and membersgroups.Status='In' group by groups.id order by groups.id ";
     	$s = $this->dao->query($query);
     	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$row->GroupName=$this->getGroupName($row->Name);
			array_push($tt,$row) ;
		}
		return($tt) ; // returs the array of structures

	 } // end of GroupChoices

    /**
	Return true if the Member is not allowed to see the given post because of groups restrictions
	@$IdMember : Id of the member
	@$rPost data about the post (Thread and Post visibility, id of the group and delettion status)
	return true or false
	!! Becareful, in case the member has moderator right, his moderator rights will not be considerated
	*/
public function NotAllowedForGroup($IdMember, $rPost) {
		if ($rPost->ThreadDeleted=='Deleted') return (true) ; //Deleted thread: no notifications
		if ($rPost->PostDeleted=='Deleted') return (true) ; //Deleted post: no notifications
		if ($rPost->IdGroup==0) return(false) ; // No group defined, noification can be allowed

		// In case there is a restriction to moderator, check if the member is a moderator
		if (($rPost->PostVisibility=='ModeratorOnly') or ($rPost->ThreadVisibility=='ModeratorOnly'))
			if ($this->BW_Right->HasRight("ForumModerator"))  {
				return (false) ; // Moderator are allowed
			}
			else {
				return(true) ;
			}

		if (($rPost->PostVisibility=='GroupOnly') or ($rPost->ThreadVisibility=='GroupOnly')) { // If there is a group restriction, we need to check the membership of the member
		    $query = "select IdGroup from membersgroups where IdMember=".$IdMember." and IdGroup=".$rPost->IdGroup." and Status='In'";
			$qry = $this->dao->query("select IdGroup from membersgroups where IdMember=".$IdMember." and IdGroup=".$rPost->IdGroup." and Status='In'");
			if (!$qry) {
				throw new PException('Failed to retrieve groupsmembership for member id =#'.$IdMember.'  !');
			}
			$rr=$qry->fetch(PDB::FETCH_OBJ) ;
			if (isset($rr->IdGroup)) {	// If the guy is member of the group
				return(false) ; // He is allowed to see
			}
			else {
				return(true) ;
			}
		}

		// Other cases no reason to restrict because of some group restriction
		return (false) ;
	} // end of NotAllowedForGroup


	/**
	 * Handle forum notifications
	 */
    private function prepare_notification($postId, $type) {
        // Get post details
        $query = "
            SELECT
                p.threadid as threadId,
                t.IdGroup as groupId,
                p.PostVisibility,
                p.PostDeleted,
                t.ThreadVisibility,
                t.ThreadDeleted
            FROM
                forums_posts p,
                forums_threads t
            WHERE
                p.threadid = t.id
                AND p.id = '" . $this->dao->escape($postId) ."'";

        $res = $this->dao->query($query);
        if (!$res) {
            // just don't set notifications
            return;
        }
        $post = $res->fetch(PDB::FETCH_OBJ);

        // Some checks before we take the long way
        if (($post->PostDeleted == 'deleted') || ($post->ThreadDeleted == 'deleted')) {
            return;
        }

        $members = array(); // collects all members that get a notification (to avoid several notifications for the same post)

        // first we get all open (ToSend) post notifications from the database and build
        // a list of members that don't need another reminder
        $query = "
            SELECT
                DISTINCT IdMember
            FROM
                posts_notificationqueue p
            WHERE
                p.IdPost = $postId
                AND Status = 'ToSend'
            ORDER BY
                IdMember
            ";
        $res = $this->dao->query($query);
        if ($res) {
            while ($row = $res->fetch(PDB::FETCH_OBJ)) {
                $members[] = $row->IdMember;
            }
        }

        // get group members in case of a group post to limit subscriptions to tags and threads
		$group = false;
        $groupMembers = array();
        if ($post->groupId != 0) {
            $group = $this->createEntity('Group')->findById($post->groupId);
            $memberEntities = $group->getMembers();
            foreach($memberEntities as $groupMember) {
                $groupMembers[] = $groupMember->getPKValue();
            }
        }

        if ($group != false) {
            // We reuse the $group entity from above
            $subscriberEntities = $group->getEmailAcceptingMembers();

            $membersTemp = array();
            foreach($subscriberEntities as $subscriber) {
                $memberId = $subscriber->getPKValue();
                if ($memberId == 0) continue;
                if (array_search($memberId, $members) === false) {
                    $membersTemp[] = $memberId;
                    $members[] = $memberId;
                }
            }
            if (!empty($membersTemp)) {
                $count = 0;
                $query = "
                    INSERT INTO
                        posts_notificationqueue (
                            `IdMember`,
                            `IdPost`,
                            `Status`,
                            `created`,
                            `Type`,
                            `TableSubscription`,
                            `IdSubscription`
                        )
                    VALUES ";
                foreach($membersTemp as $member) {
                    $query .= "(" . $member . ", " . $postId . ", '" . NotificationStatusType::SCHEDULED . "', NOW(), '" . $type . "', 'membersgroups', 0), ";
                    $count++;
                }
                if ($count > 0) {
                    $query = substr($query, 0, -2);
                    $this->dao->query($query);
                }
            }
        }

		// Set notifications for subscribed threads
		$query = "
            SELECT
            	IdSubscriber as subscriber,
                notificationsEnabled,
                members_threads_subscribed.id as subscriptionId
            FROM
                members_threads_subscribed
            WHERE IdThread = '" . $this->dao->escape($post->threadId) . "'";
		$res = $this->dao->query($query);
		if (!$res) {
			// just don't write notifications
			return;
		}

		$membersTemp = array();
		while ($row = $res->fetch(PDB::FETCH_OBJ)) {
			if ($row->subscriber > 0) {
                // did member disable notifications for this thread?
                if ($row->notificationsEnabled) {
                    // only add gets notification don't add one
                    if (array_search($row->subscriber, $members) === false) {
                        $membersTemp[$row->subscriber] = $row->subscriptionId;
                    }
                } else {
                    if (array_search($row->subscriber, $members) !== false) {
                        unset($membersTemp[$row->subscriber]);
                    }
                }
            }
		}

		if (!empty($membersTemp)) {
			$count = 0;
			$query = "
                INSERT INTO
                    posts_notificationqueue (
                        `IdMember`,
                        `IdPost`,
                        `Status`,
                        `created`,
                        `Type`,
                        `TableSubscription`,
                        `IdSubscription`
                    )
                VALUES ";
			foreach($membersTemp as $member => $subscriptionId) {
			    // current member doesn't get a notification yet
			    if (array_search($member, $members) === false) {
			        // Thread notifications might need to be limited to group members only
                    if (($post->groupId == 0) || ($post->PostVisibility != 'GroupOnly' && $post->ThreadVisibility != 'GroupOnly')
                        || (array_search($member, $groupMembers) !== false)) {
                        $query .= "(" . $member . ", " . $postId . ", '" . NotificationStatusType::SCHEDULED . "', NOW(), '" . $type . "', 'members_threads_subscribed', '" . $this->dao->escape($subscriptionId) . "'), ";
                        $members[] = $member;
                        $count++;
                    }
                }
			}
			if ($count > 0) {
				$query = substr($query, 0, -2);
				$this->dao->query($query);
			}
		}
	}

    // This function IsGroupSubscribed return true of the member is subscribing to the IdGroup
    // @$IdGroup : The thread we want to know if the user is subscribing too
    // @$ParamIdMember optional IdMember, by default set to 0 in this case current logged membver will be used
    public function IsGroupSubscribed($IdGroup=0,$ParamIdMember=0) {
       $IdMember=$ParamIdMember ;
       if ($this->session->has( "IdMember" ) and $IdMember==0) {
                 $IdMember=$this->session->get("IdMember") ;
       }

       // Check if there is a previous Subscription
       $query = sprintf("select members_groups_subscribed.id as IdSubscribe,IdThread,IdSubscriber from members_groups_subscribed where IdGroup=%d and IdSubscriber=%d",$IdGroup,$IdMember);
       $s = $this->dao->query($query);
       if (!$s) {
              throw new PException('IsGroupSubscribed Could not check previous subscription !');
       }
       $row = $s->fetch(PDB::FETCH_OBJ) ;
       return (isset($row->IdSubscribe))  ;
    } // end of IsGroupSubscribed

    public function IsThreadSubscribed($IdThread=0,$ParamIdMember=0) {
       $IdMember=$ParamIdMember ;
       if ($this->session->has( "IdMember" ) and $IdMember==0) {
                 $IdMember=$this->session->get("IdMember") ;
       }

       // Check if there is a previous Subscription
       $query = sprintf("select members_threads_subscribed.id as IdSubscribe,IdThread,IdSubscriber from members_threads_subscribed where IdThread=%d and IdSubscriber=%d",$IdThread,$IdMember);
       $s = $this->dao->query($query);
       if (!$s) {
              throw new PException('IsThreadSubscribed Could not check previous subscription !');
       }
       $row = $s->fetch(PDB::FETCH_OBJ) ;
       return (isset($row->IdSubscribe))  ;
    } // end of IsThreadSubscribed

    // This function isMembersForumPostsPagePublic return true if the members allows other members to see his forum posts page: /forums/member/username
    // @$userId : The user we want to know if his forum page is public
    public function isMembersForumPostsPagePublic($userId = 0) {
       $member = $this->createEntity("Member", $userId);
       $usersForumPostsPagePublic = $member->getPreference("MyForumPostsPagePublic", $default = "No");
       if ($usersForumPostsPagePublic == "Yes") {
           return true;
       }
       return false;
    } // end of isMembersForumPostsPagePublic



    public function GetThreadVisibility($IdThread) {
        $query = "SELECT ThreadVisibility FROM forums_threads WHERE id = " . intval($IdThread);
        $s = $this->dao->query($query);
        if (!$s) {
            // Couldn't fetch the result from the DB assume 'MembersOnly'
            return "MembersOnly";
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        return ($row->ThreadVisibility);
    }

    public function GetPostVisibility($IdPost) {
        $query = "SELECT PostVisibility FROM forums_posts WHERE id = " . intval($IdPost);
        $s = $this->dao->query($query);
        if (!$s) {
            // Couldn't fetch the result from the DB assume 'MembersOnly'
            return "MembersOnly";
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        return ($row->PostVisibility);
    }

    public function GetGroupEntity($IdGroup) {
        return $this->createEntity('Group')->findById($IdGroup);
    }

    public function getTinyMCEPreference() {
        $member = $this->getLoggedInMember();
        return $member->getPreference("PreferenceDisableTinyMCE", $default = "No");
    }

	/**
	 * @param $keywords Keywords to search for in the Sphinx index
	 * @return array
	 */
	public function searchForums($keywords) {
        $sphinx = new MOD_sphinx();

		$results = array( 'count' => 0);
		$member = $this->getLoggedInMember();
		if (!$member) {
			$results['errors'][] = 'ForumSearchNotLoggedIn';
		} else {
			$groupEntities = $member->getGroups();
			$groups = array( 0 );
			foreach($groupEntities as $group) {
				$groups[] = $group->id;
			}

            $sphinxClient = $sphinx->getSphinxForums();
            $sphinxClient->setSelect('*');
			$sphinxClient->SetFilter('IdGroup', $groups);
            $sphinxClient->SetSortMode(SPH_SORT_ATTR_DESC, 'created' );
            $resultsThreads = $sphinxClient->Query($sphinxClient->EscapeString($keywords), 'forums');

			if ($resultsThreads) {
				$results['count'] = $resultsThreads['total'];
				if ($resultsThreads['total'] <> 0) {
					$threadIds = array();
					foreach ($resultsThreads['matches'] as $match) {
						$threadIds[] = $match['id'];
					}
					$this->board->initThreads($this->getPage(), false, $threadIds);
				} else {
					$results['errors'][] = 'ForumSearchNoResults';
				}
			} else {
				$results['errors'][] = 'ForumSearchNoSphinx';
			}
		}
		return $results;
	}

	/**
	 * Checks for correctness of search box vars (not empty)
	 *
	 * @param $vars
	 * @return bool
	 */
	private function _checkVarsSearch($vars) {
		return true;
	}

	/**
	 * Fetches matching threads/posts from the Sphinx index
	 *
	 * @return mixed Either false if there was a problem with the search box content or a list of matches.
	 */
	public function searchProcess() {
	    $User = $this->getLoggedInMember();
		if (!$User) {
			return false;
		}

		$vars =& PPostHandler::getVars();

		$vars_ok = $this->_checkVarsSearch($vars);
		if ($vars_ok) {
			$keyword = htmlspecialchars($vars['fs-keyword']);
			PPostHandler::clearVars();
			return PVars::getObj('env')->baseuri.$this->forums_uri.'search/'. $keyword;
		}
		return false;
	}
} // end of class Forums


class Topic {
    public $topicinfo;
    public $posts = array();
}

class Board implements Iterator {
	public $THREADS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no

    public function __construct(&$dao, $boardname, $link, $session, $board_description=false, $IdGroup=false, $no_forumsgroup=false) {
		$this->THREADS_PER_PAGE=Forums::CV_THREADS_PER_PAGE  ; //Variable because it can change wether the user is logged or no
		$this->POSTS_PER_PAGE=Forums::CV_POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no

		$this->BW_Right = MOD_right::get();
		$this->session = $session;

		if (!$this->session->has( 'IdMember' )) {
			$this->THREADS_PER_PAGE=100  ; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE=self::CV_POSTS_PER_PAGE ; // Variable because it can change wether the user is logged or no
		}

        $this->dao =& $dao;

        $this->boardname = $boardname;
        $this->board_description = $board_description;
        $this->link = $link;
        $this->IdGroup = $IdGroup;

        $this->PublicThreadVisibility = "(ThreadVisibility!='ModeratorOnly') and (ThreadDeleted!='Deleted')";
        $this->PublicPostVisibility = " (PostDeleted!='Deleted')";

        //if the member prefers to see only posts to his/her groups
        $roxmodel = new RoxModelBase();
        $member = $roxmodel->getLoggedInMember();
        $owngroupsonly = $member->getPreference("ShowMyGroupsTopicsOnly", $default = "No");
        $this->owngroupsonly = $owngroupsonly;
        if ($this->IdGroup === null) {
            $this->PostGroupsRestriction = " ((PostVisibility IN ('MembersOnly','NoRestriction') or (PostVisibility = 'GroupOnly')) AND (IdGroup IS NULL))";
            $this->ThreadGroupsRestriction = " ((ThreadVisibility IN ('MembersOnly','NoRestriction') OR (ThreadVisibility = 'GroupOnly')) AND (IdGroup IS NULL))";
        } elseif ($this->IdGroup > 0) {
            $this->PostGroupsRestriction = " (IdGroup= " . (int)$this->IdGroup . ") ";
            $this->ThreadGroupsRestriction = " (IdGroup= " . (int)$this->IdGroup . ") ";
        } else {
            if ($owngroupsonly == "Yes" && ($this->IdGroup === false || !isset($this->IdGroup))) {
                // 0 is the group id for topics without an explicit group, we don't want them in this case. Lazy hack to avoid changing more than necessary: replace 0 with -1
                $this->PostGroupsRestriction = " ((((IdGroup IN (-1";
                $this->ThreadGroupsRestriction = " ((((IdGroup IN (-1";
            } else {
                $this->PostGroupsRestriction = " ((PostVisibility IN ('MembersOnly','NoRestriction') or (PostVisibility = 'GroupOnly' AND (IdGroup IS NULL OR IdGroup IN (0";
                $this->ThreadGroupsRestriction = " ((ThreadVisibility IN ('MembersOnly','NoRestriction') OR (ThreadVisibility = 'GroupOnly' AND (IdGroup IS NULL OR IdGroup IN (0";
            }
            $query = "SELECT IdGroup FROM membersgroups WHERE IdMember=" . $this->session->get("IdMember") . " AND Status = 'In'";
            $qry = $this->dao->query($query);
            if (!$qry) {
                throw new PException('Failed to retrieve groups for member id =#' . $this->session->get("IdMember") . ' !');
            }
            while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
                $this->PostGroupsRestriction = $this->PostGroupsRestriction . "," . $rr->IdGroup;
                $this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . "," . $rr->IdGroup;
            }

            if ($no_forumsgroup) {
                $this->PostGroupsRestriction = $this->PostGroupsRestriction . ")))) AND (NOT IdGroup IS NULL))";
                $this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . ")))) AND (NOT IdGroup IS NULL))";
            } else {
                $this->PostGroupsRestriction = $this->PostGroupsRestriction . ")))) AND (IDGroup IS NULL))";
                $this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . ")))) AND (IDGroup IS NULL))";
            }
        }

        // Prepares additional visibility options for moderator
		if ($this->BW_Right->HasRight("ForumModerator")) {
			$this->PublicPostVisibility = " PostVisibility IN ('NoRestriction', 'MembersOnly','GroupOnly','ModeratorOnly')";
			$this->PublicThreadVisibility = " ThreadVisibility IN ('NoRestriction', 'MembersOnly','GroupOnly','ModeratorOnly')";
			if ($this->BW_Right->HasRight("ForumModerator","AllGroups") or $this->BW_Right->HasRight("ForumModerator","All")) {
			    if ($IdGroup === null) {
                    $this->PostGroupsRestriction = " (IdGroup IS NULL)";
                    $this->ThreadGroupsRestriction = " (IdGroup IS NULL)";
                } elseif ($no_forumsgroup) {
                        $this->PostGroupsRestriction = " (IdGroup != 0)";
                        $this->ThreadGroupsRestriction = " (IdGroup != 0)";
                }
                else {
                    $this->PostGroupsRestriction = " (1=1)";
                    $this->ThreadGroupsRestriction = " (1=1)";
                }
            }
		}
    }

    private $dao;
    private $numberOfThreads;
    private $totalThreads;

    /**
        this filtres the list of thread results according to the presence of :
        $this->IdGroup ;
    */
	public function FilterThreadListResultsWithIdCriteria($ids = array()) {
        $wherethread="" ;

		if (count($ids) <> 0) {
			$wherethread = " AND `forums_threads`.`id` in ('" . implode("', '", $ids) . "') ";
			return $wherethread;
		}

        if (isset($this->IdGroup)) {
            if (is_numeric($this->IdGroup)) {
                $wherethread .= sprintf("AND `forums_threads`.`IdGroup` = '%d' ", $this->IdGroup);
            } elseif ($this->IdGroup === null) {
                $wherethread .= "AND `forums_threads`.`IdGroup` IS NULL ";
            }
        }
		$wherethread=$wherethread." AND (".$this->PublicThreadVisibility.")" ;
		$wherethread=$wherethread." AND (".$this->ThreadGroupsRestriction.")" ;
		return($wherethread) ;
	} // end of FilterThreadListResultsWithIdCriteria

	/**
	 * Initializes the thread storages.
	 *
	 * If ids is set only threads with the given ids will be loaded
	 *
	 * @param int $page
	 * @param bool $showsticky
	 * @param array $ids
	 * @throws PException
	 */
    public function initThreads($page = 1, $showsticky = true, $ids = array()) {

		$this->threads = array();
        $wherethread=$this->FilterThreadListResultsWithIdCriteria($ids) ;

        if ($showsticky) {
            $orderby = " ORDER BY `stickyvalue` ASC,`last_create_time` DESC";
        } else {
            $orderby = " ORDER BY `last_create_time` DESC";
        }

		$query = "SELECT COUNT(*) AS `number` FROM `forums_threads` WHERE 1 ".$wherethread;
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}
		$row = $s->fetch(PDB::FETCH_OBJ);
		$this->numberOfThreads = $row->number;

		if ($page == 0) {
		    $from = 0;
        } else {
		    $from = $this->THREADS_PER_PAGE * ($page - 1);
        }

		$query = "SELECT SQL_CALC_FOUND_ROWS `forums_threads`.`id`,
		 		  `forums_threads`.`id` as IdThread, `forums_threads`.`title`,
				  `forums_threads`.`IdTitle`,
				  `forums_threads`.`IdGroup`,
				  `forums_threads`.`replies`,
		          `forums_threads`.`stickyvalue`,
		          `groups`.`Name` as `GroupName`,
                  `ThreadVisibility`,
	              `ThreadDeleted`,
				  `forums_threads`.`views`,
				  `first`.`id` AS `first_postid`,
				  `first`.`idWriter` AS `first_authorid`,
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`id` AS `last_postid`,
				  `last`.`idWriter` AS `last_authorid`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
		$query .= "`first_member`.`Username` AS `first_author`,`last_member`.`Username` AS `last_author`" ;
		$query .= "FROM `forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`id`)" ;
		$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
		$query .= "LEFT JOIN `forums_posts` AS `last` ON last.id =
		( SELECT    MAX(id)
              FROM      forums_posts fp
              WHERE fp.threadid = `forums_threads`.`id`
              AND fp.PostDeleted = 'NotDeleted'
              ) ";
		$query .= "LEFT JOIN `members` AS `first_member` ON (`first`.`IdWriter` = `first_member`.`id`)" ;
		$query .= "LEFT JOIN `members` AS `last_member` ON (`last`.`IdWriter` = `last_member`.`id`)" ;
		$query .= " WHERE 1 ".$wherethread . $orderby . " LIMIT ".$from.", ".$this->THREADS_PER_PAGE ;


		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $this->threads[] = $row;
        }

		$sFounRow = $this->dao->query("SELECT FOUND_ROWS() AS `found_rows`");
		if (!$sFounRow) {
			throw new PException('Could not retrieve number of rows!');
		}
        $rowFounRow = $sFounRow->fetch(PDB::FETCH_OBJ);
        $this->totalThreads = $rowFounRow->found_rows;

    } // end of initThreads

    private $threads = array();
    public function getThreads() {
        return $this->threads;
    }

    private $boardname;
    public function getBoardName() {
        return $this->boardname;
    }

    private $board_description;

    private $link;

    public function getNumberOfThreads() {
        return $this->numberOfThreads;
    }

    public function getTotalThreads() {
        return $this->totalThreads;
    }

    private $subboards = array();

    // Add a subboard
    public function add(Board $board) {
        $this->subboards[] = $board;
    }

    public function hasSubBoards() {
        return (bool)(count($this->subboards) > 0);
    }

    public function rewind() {
        reset($this->subboards);
    }

    public function current() {
        $var = current($this->subboards);
        return $var;
    }

    public function key() {
        $var = key($this->subboards);
        return $var;
    }

    public function next() {
        $var = next($this->subboards);
        return $var;
    }

    public function valid() {
        $var = $this->current() !== false;
        return $var;
    }
}
