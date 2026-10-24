<?php

use App\Doctrine\NotificationStatusType;
use Manticoresearch\Search;
use Manticoresearch\Client;
use Manticoresearch\Query\BoolQuery;
use Manticoresearch\Query\Equals;
use Manticoresearch\Query\In;
use Manticoresearch\Query\MatchPhrase;

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
    return (strtolower((string) $a->Name) < strToLower((string) $b->Name)) ? -1 : 1;
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
	// Returns a language ShortCode (posts and threads reference language.ShortCode)
	return $this->words->GetLanguageChoosen();
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
    $ss="select `IdContent`, `ShortCode` FROM `forum_post` WHERE `id`=" . (int) $IdPost;
    $q = $this->dao->query($ss);
	$post = $q ? $q->fetch(PDB::FETCH_OBJ) : false;
	if (!$post) {
	   return ('en') ;
	}

	// Prefer a translation in the current language of the member
	if (!empty($post->IdContent)) {
		$language = $this->dao->escape($this->words->languageShortCode());
		$query = "SELECT `ShortCode` FROM `forum_trads` WHERE `IdTrad`=" . (int) $post->IdContent . " AND `ShortCode`='" . $language . "'";
		$q = $this->dao->query($query);
		$row = $q ? $q->fetch(PDB::FETCH_OBJ) : false;
		if ($row && isset($row->ShortCode)) {
		   return($row->ShortCode) ;
		}
	}

	// Otherwise the original language of the post
	return($post->ShortCode ?: 'en') ;

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

            match ($member->getPreference("PreferenceForumFirstPage")) {
                "Pref_ForumFirstPageLastPost" => $this->setTopMode(Forums::CV_TOPMODE_FORUM),
                "Pref_ForumFirstPageCategory" => $this->setTopMode(Forums::CV_TOPMODE_CATEGORY),
                default => $this->setTopMode(Forums::CV_TOPMODE_LANDING),
            };
            $layoutbits = new MOD_layoutbits();
            $this->ForumOrderList = $layoutbits->GetPreference("PreferenceForumOrderListAsc", $member->id);
        } else {
            $this->setTopMode(Forums::CV_TOPMODE_FORUM);
        }

		if (!$this->session->has( 'IdMember' )) {
			$this->THREADS_PER_PAGE = 100; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE = self::CV_POSTS_PER_PAGE; // Variable because it can change wether the user is logged or no
		}

		$MyGroups = [];


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

        match ($command) {
            "moreagora" => $membersmodel->set_preference($member->id, $forumpref->id, min($forumthreads + $step, $MAX_THREADS)),
            "lessagora" => $membersmodel->set_preference($member->id, $forumpref->id, max($forumthreads - $step, 1)),
            "moregroups" => $membersmodel->set_preference($member->id, $groupspref->id, min($groupsthreads + $step, $MAX_THREADS)),
            "lessgroups" => $membersmodel->set_preference($member->id, $groupspref->id, max($groupsthreads - $step, 1)),
            default => false,
        };

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

        $subboards = [];
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
    `forum_post`.`id` AS `postid`,
    `IdWriter`,
    `PostDeleted`,
    `PostVisibility`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forum_post`.`threadid` as `threadid`,
    `message` AS `topic_text`,
		`OwnerCanStillEdit`,
	 `IdContent`,
    `title` AS `topic_title`, `first_postid`, `last_postid`, `IdTitle`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forum_thread`.`IdGroup`
FROM `forum_post`
LEFT JOIN `forum_thread` ON (`forum_post`.`threadid` = `forum_thread`.`id`)
WHERE `forum_post`.`id` = $this->messageId
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
    `forum_post`.`id` AS `postid`,
    `IdWriter`,
    `forum_post`.`threadid`,
    `first_postid`,
	`OwnerCanStillEdit`,
    `PostDeleted`,
    `PostVisibility`,
    `ThreadVisibility`,
    `ThreadDeleted`,
	`forum_thread`.`IdGroup`,
    `last_postid`
FROM `forum_post`
LEFT JOIN `forum_thread` ON (`forum_post`.`threadid` = `forum_thread`.`id`)
WHERE `forum_post`.`id` = $this->messageId
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
		 $ShortCode=$this->dao->escape($this->words->languageShortCode($P_IdLanguage)) ;
		 $Sentence= $this->dao->escape($P_Sentence);

        MOD_log::get()->write("Updating data for IdForumTrads=#".$id." Before [".addslashes((string) $rBefore->Sentence)."] language=".$rBefore->ShortCode." <br />\nAfter [".$Sentence."] language=".$ShortCode, "ForumModerator");
		 $sUpdate="update forum_trads set Sentence='".$Sentence."',ShortCode='".$ShortCode."',IdTranslator=".(int) $this->session->get("IdMember").", updated=NOW() where id=".$id ;
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
        $query = "SELECT message,forum_post.threadid,
    `PostVisibility`,
		OwnerCanStillEdit,IdWriter,forum_post.ShortCode as post_language,forum_thread.ShortCode as thread_language,forum_post.id,IdWriter,IdContent,forum_thread.IdTitle,forum_thread.first_postid from `forum_post`,`forum_thread` WHERE forum_post.threadid=forum_thread.id and forum_post.id = ".$this->messageId ;
        $s=$this->dao->query($query);
        $rBefore=$s->fetch(PDB::FETCH_OBJ) ;

        $query = sprintf("UPDATE `forum_post` SET `message` = '%s', `last_edittime` = NOW(), `last_editorid` = '%d', `edit_count` = `edit_count` + 1 WHERE `id` = '%d'",
        $this->dao->escape($this->cleanupText($vars['topic_text'])), $editorid, $this->messageId);
        $this->dao->query($query);
		$this->ReplaceInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])), "forum_post.IdContent", $rBefore->id, $rBefore->IdContent, $rBefore->IdWriter) ;
        $this->updatePostInManticoreIndex($vars);
		// case the update concerns the reference language of the posts
		if ($rBefore->post_language==$this->GetLanguageChoosen()) {
		 	$query="update forum_post set message='".$this->dao->escape($this->cleanupText($vars['topic_text']))."' where id=".$this->messageId ;
        	$s=$this->dao->query($query);
		}

		// case the visibility has changed
		if ($rBefore->PostVisibility!=$vars['PostVisibility']) {
		 	$query="update forum_post set PostVisibility='".$vars['PostVisibility']."' where id=".$this->messageId ;
        	$s=$this->dao->query($query);
			MOD_log::get()->write("Changing Post Visibility from <b>".$rBefore->PostVisibility."</b> to <b>".$vars['PostVisibility']."</b>", "Forum");
		}



		// If this is the first post, may be we can update the title
		if ($rBefore->first_postid==$rBefore->id) {
		 	$this->ReplaceInFTrad($this->dao->escape($this->cleanupText($vars['topic_title'])), "forum_thread.IdTitle", $rBefore->threadid, $rBefore->IdTitle, $rBefore->IdWriter) ;
		// case the update concerns the reference language of the threads
		 	if ($rBefore->thread_language==$this->GetLanguageChoosen()) {
		 	    $groupId = empty($vars['IdGroup']) ? 'NULL' : (int) $vars['IdGroup'];
		 	   $query="update forum_thread set IdGroup=". $groupId.",title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forum_thread.id=".$rBefore->threadid ;
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
        MOD_log::get()->write("Editing Post=#".$this->messageId." Text Before=<i>".addslashes((string) $rBefore->message)."</i> <br /> NotifyMe=[".$vars['NotifyMe']."]", "Forum");
    } // editPost

    /**
    *	editTopic write the data in of change thread in the database
    *	warning : dont start any transaction in it since there is already one
    *  started by the caller
    * this also write a log
    */

    private function editTopic($vars, $threadid)     {
        $query = sprintf("
UPDATE `forum_thread`
SET `title` = '%s'
WHERE `id` = '%d' ",
            $this->dao->escape(strip_tags((string) $vars['topic_title'])),
            $threadid
        );

        $this->dao->query($query);

        $s=$this->dao->query("select IdWriter,forum_thread.id as IdThread,forum_thread.IdTitle,forum_thread.ShortCode as thread_language
		from forum_thread,forum_post
		where forum_thread.first_postid=forum_post.id and forum_thread.id=".(int) $threadid);
        if (!$s) {
            throw new PException('editTopic:: previous info for first post in the thread!');
        }
        $rBefore = $s->fetch(PDB::FETCH_OBJ);

		 $this->ReplaceInFTrad($this->dao->escape(strip_tags((string) $vars['topic_title'])), "forum_thread.IdTitle", $rBefore->IdThread, $rBefore->IdTitle, $rBefore->IdWriter) ;

		 // case the update concerns the reference language of the posts
		if ($rBefore->thread_language==$this->GetLanguageChoosen()) {
		 	$query="update forum_thread set title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forum_thread.id=".$rBefore->IdThread ;
        	$s=$this->dao->query($query);
		}

         // Set ThreadVisibility
        $query = 'UPDATE forum_thread SET ThreadVisibility = "' . $this->dao->escape($vars['ThreadVisibility']) . '" WHERE forum_thread.id=' . (int) $rBefore->IdThread;
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

        $ss = "select threadid from forum_post where id=".$IdPost ;
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
		$tt=[] ;
		if (empty($IdReporter)) {
			$ss = "select reports_to_moderators.*,Username from reports_to_moderators,member where IdPost=".$IdPost." and member.id=IdReporter" ;
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

		$ss = "select reports_to_moderators.*,Username from reports_to_moderators,member where member.id=IdReporter " ;
		if (!empty($StatusList)) {
			$ss=$ss." and reports_to_moderators.Status in ".$StatusList ;
		}

		$tt=[] ;
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
		$ss = "select count(*) as cnt from reports_to_moderators,member where member.id=IdReporter " ;
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

        $query = "select forum_post.*,member.Status as memberstatus,member.UserName as UserNamePoster from forum_post,member where forum_post.id=".$IdPost." and IdWriter=member.id" ;
        $s = $this->dao->query($query);
		$DataPost->Post = $s->fetch(PDB::FETCH_OBJ) ;

		if (!isset($DataPost->Post)) {
		 	$DataPost->Error="No Post for Post=#".$IdPost ;
			return($DataPost) ;
		}

        if (!$moderator) {
            if ($DataPost->Post->PostVisibility == 'GroupOnly') {
                // first check if post was made in a group and if the current member is a member of that group
                $query = "SELECT IdGroup FROM forum_post fp, forum_thread ft WHERE fp.id = " . $this->dao->escape($IdPost) . " AND fp.threadId = ft.id";
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
        $query = "select forum_trads.*,language.Name,forum_trads.ShortCode as IdLanguage,forum_trads.id as IdForumTrads from forum_trads,language where forum_trads.ShortCode=language.ShortCode and IdTrad=".(int) $DataPost->Post->IdContent." order by forum_trads.created asc" ;
        $s = $this->dao->query($query);
		 $DataPost->Post->Content=[] ;
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   $DataPost->Post->Content[]=$row ;
		 }


        $query = "select * from forum_thread where id=".$DataPost->Post->threadid ;
        $s = $this->dao->query($query);
		 if (!isset($DataPost->Post)) {
		 	$DataPost->Error="No Thread=#".$DataPost->Post->threadid ;
			return($DataPost) ;
		 }
		 $DataPost->Thread = $s->fetch(PDB::FETCH_OBJ) ;


// retrieve all trads for Title
        $query = "select forum_trads.*,language.Name,forum_trads.ShortCode as IdLanguage,forum_trads.id as IdForumTrads from forum_trads,language where forum_trads.ShortCode=language.ShortCode and IdTrad=".(int) $DataPost->Thread->IdTitle." order by forum_trads.created asc" ;
        $s = $this->dao->query($query);
		 $DataPost->Thread->Title=[] ;
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
        $ThreadVisibility=$this->dao->escape($vars["ThreadVisibility"]) ;
        $ThreadDeleted=$this->dao->escape($vars["ThreadDeleted"]) ;
        $WhoCanReply=$this->dao->escape($vars["WhoCanReply"]) ;
        $stickyvalue=(int)$vars["stickyvalue"];
        if (empty($vars["expiredate"])) {
           $expiredate="NULL" ;
        } else {
           $expiredate="'".$this->dao->escape($vars["expiredate"])."'"  ;
        }
        MOD_log::get()->write("Updating Thread=#".$IdThread." IdGroup=#".$IdGroup." Setting expiredate=[".$expiredate."] stickyvalue=".$stickyvalue." ThreadDeleted=".$ThreadDeleted." ThreadVisibility=".$ThreadVisibility." WhoCanReply=".$WhoCanReply,"ForumModerator");
        $sql="update forum_thread set ";
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
			$qry=$this->dao->query("select * from forum_trads where IdTrad=".(int) $vars["IdTrad"]." and ShortCode='".$this->dao->escape($this->words->languageShortCode($vars["IdLanguage"]))."'");
			$rr=$qry->fetch(PDB::FETCH_OBJ) ;
			if (empty($rr->id)) { // Only proceed if no such a title exists
		 		$ss=$vars["NewTranslatedTitle"]  ;
				$this->InsertInFTrad($ss,"forum_thread.IdTitle",$IdThread, $this->session->get("IdMember"), $vars["IdLanguage"],$vars["IdTrad"]) ;
				MOD_log::get()->write("Updating Thread=#".$IdThread." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
			}
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		 if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
		 	$OwnerCanStillEdit="'".$vars["OwnerCanStillEdit"]."'"  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."]","ForumModerator");
			$this->dao->query("update forum_post set OwnerCanStillEdit=".$OwnerCanStillEdit." where id=".$IdPost);
		 }

		 if (isset($vars["submit"]) and ($vars["submit"]=="add translated post")) { // if a new translation is to be added for a title
		 		$IdPost=(int)$vars["IdPost"] ;
				$qry=$this->dao->query("select * from forum_trads where IdTrad=".(int) $vars["IdTrad"]." and ShortCode='".$this->dao->escape($this->words->languageShortCode($vars["IdLanguage"]))."'");
				$rr=$qry->fetch(PDB::FETCH_OBJ) ;
				if (empty($rr->id)) { // Only proceed if no such a post exists
		 			$ss=$this->dao->escape($vars["NewTranslatedPost"])  ;
					$this->InsertInFTrad($ss,"forum_post.IdContent",$IdPost, $this->session->get("IdMember"), $vars["IdLanguage"],$vars["IdTrad"]) ;
       		MOD_log::get()->write("Updating Post=#".$IdPost." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
                $this->updatePostInManticoreIndex($vars);
				}
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
            $IdThread=(int)$vars["IdThread"] ;
		 	$OwnerCanStillEdit=$vars["OwnerCanStillEdit"]  ;
		 	$PostVisibility=$vars["PostVisibility"]  ;
		 	$PostDeleted=$vars["PostDeleted"]  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."] PostVisibility=[".$PostVisibility."] PostDeleted=[".$PostDeleted."] ","ForumModerator");
			$this->dao->query("update forum_post set OwnerCanStillEdit='".$this->dao->escape($OwnerCanStillEdit)."',PostVisibility='".$this->dao->escape($PostVisibility)."',PostDeleted='".$this->dao->escape($PostDeleted)."' where id=".$IdPost);
			// keep the search index in line, so deleted or hidden posts don't show up in searches
			$this->updatePostInManticoreIndex(['IdPost' => $IdPost], [
				'post_deleted' => (string) $PostDeleted,
				'post_visibility' => (string) $PostVisibility,
			]);
                // Update last post id
                $query = "
                    SELECT
                        id
                    FROM
                       forum_post
                    WHERE
                        threadid = " . $IdThread . "
                        AND PostDeleted = 'NotDeleted'
                    ORDER BY create_time DESC
                    LIMIT 1";
                $s = $this->dao->query($query);
                $row = $s->fetch(PDB::FETCH_OBJ);
                if (null === $row) {
                    // last post of thread was deleted
                    $this->dao->query("update forum_thread set ThreadDeleted='Deleted' where id=".$IdThread);
                } else {
                    $id = $row->id;
                    $update = "
                    UPDATE
                        forum_thread
                    SET
                        last_postid = " . $id . "
                    WHERE
                        id = " . $IdThread;
                    $this->dao->query($update);
                }
		}

 		if (isset($vars["IdForumTrads"])) { // if an effective update was chosen for a forum trads
		 	$this->DofTradUpdate($vars["IdForumTrads"],$vars["Sentence"],$vars["IdLanguage"]) ; // update the corresponding translations
            $this->updatePostInManticoreIndex($vars);
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
    `forum_post`.`threadid`,
    `forum_thread`.`first_postid`,
    `forum_thread`.`last_postid`,
    `forum_thread`.`expiredate`,
    `forum_thread`.`stickyvalue`
FROM `forum_post`
LEFT JOIN `forum_thread` ON (`forum_post`.`threadid` = `forum_thread`.`id`)
WHERE `forum_post`.`id` = '%d'
                ",
                $this->messageId
            );
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could not retrieve Threadinfo!');
            }
            $topicinfo = $s->fetch(PDB::FETCH_OBJ);

            $threadId = (int) $topicinfo->threadid;
            $postId = (int) $this->messageId;

            // forum_thread.first_postid/last_postid must reference existing posts and queued
            // notifications and subscriptions reference posts and threads. So no notification
            // about the deletion can be queued any more, and the rows are removed in this order.
            if ($topicinfo->first_postid == $this->messageId) { // Delete the complete topic
                $postIds = [];
                $s = $this->dao->query("SELECT `id` FROM `forum_post` WHERE `threadid` = " . $threadId);
                while ($s && ($row = $s->fetch(PDB::FETCH_OBJ))) {
                    $postIds[] = (int) $row->id;
                }
                $postList = implode(',', $postIds ?: [0]);

                $this->dao->query("DELETE FROM `posts_notificationqueue` WHERE `IdPost` IN (" . $postList . ")");
                $this->dao->query("DELETE FROM `members_threads_subscribed` WHERE `IdThread` = " . $threadId);
                $this->dao->query("UPDATE `forum_post` SET `threadid` = NULL WHERE `threadid` = " . $threadId);
                $this->dao->query("DELETE FROM `forum_thread` WHERE `id` = " . $threadId);
                $this->dao->query("DELETE FROM `forum_post` WHERE `id` IN (" . $postList . ")");
                MOD_log::get()->write("deleting Thread=#" . $threadId . " with posts #" . $postList, "Forum");

                $redir = 'forums';
            } else { // Delete a single post
                $lastpostupdate = '';
                if ($topicinfo->last_postid == $this->messageId) {
                    // the thread has to point at the previous post before this one can go
                    $s = $this->dao->query("
                        SELECT `id` FROM `forum_post`
                        WHERE `threadid` = " . $threadId . " AND `id` <> " . $postId . "
                        ORDER BY `create_time` DESC, `id` DESC LIMIT 1");
                    if (!$s) {
                        throw new PException('Could not retrieve Postinfo!');
                    }
                    $lastpost = $s->fetch(PDB::FETCH_OBJ);
                    $lastpostupdate = sprintf(", `last_postid` = %d", $lastpost->id);
                }

                $this->dao->query("
                    UPDATE `forum_thread`
                    SET `replies` = GREATEST(`replies` - 1, 0) " . $lastpostupdate . "
                    WHERE `id` = " . $threadId);

                MOD_log::get()->write("deleting single post where Post=#". $postId, "Forum");
                $this->dao->query("DELETE FROM `posts_notificationqueue` WHERE `IdPost` = " . $postId);
                $this->dao->query("DELETE FROM `forum_post` WHERE `id` = " . $postId);

                $redir = $this->forums_uri.'s'.$threadId;
            }

            $this->dao->query("COMMIT");
        }


        header('Location: '.PVars::getObj('env')->baseuri.$redir);
        PPHP::PExit();
    }


    private function checkVarsReply(&$vars) {
        $errors = [];

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
        $errors = [];

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
            $vars['ThreadVisibility'] = $vars['PostVisibility'] = $postVisibility = 'MembersOnly';
        }

        $this->dao->query("START TRANSACTION");
        $query = sprintf(
            "
INSERT INTO `forum_post` ( `threadid`, `create_time`, `message`,`IdWriter`,`ShortCode`,`PostVisibility`, `PostDeleted`, `OwnerCanStillEdit`, `IdContent`,`edit_count`)
VALUES ('%d', NOW(), '%s','%d','%s','%s','NotDeleted','Yes',0, 0)
            ",
            $this->threadid,
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            $this->session->get("IdMember"), $this->dao->escape($this->GetLanguageChoosen()), $this->dao->escape($postVisibility)
        );

        $result = $this->dao->query($query);


        $postId = $result->insertId();

		 // Now create the text in forum_trads
 		 $this->InsertInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])),"forum_post.IdContent",$postId) ;

        $query =
            "
UPDATE `forum_thread`
SET `last_postid` = '$postId', `replies` = `replies` + 1
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

        $this->addPostToManticoreIndex($vars, $postId, $this->threadid, $this->session->get("IdMember"), $this->GetLanguageChoosen());

        MOD_log::get()->write("Replying new Post=#". $postId." in Thread=#".$this->threadid." NotifyMe=[".$vars['NotifyMe']."]", "Forum");
        $this->prepare_notification($postId,"reply") ; // Prepare a notification

        return $postId;
    } // end of replyTopic

    /**
    * Create a new Topic (with initial first post)
    * @return int topicid Id of the newly created topic
    */
    public  function newTopic(&$vars) {
        if (!($User  = $this->getLoggedInMember())) {
            throw new PException('User gone missing...');
        }
        // forum_thread.IdGroup references groups.id: "no group" has to be NULL, not 0
        $IdGroup = null;
        if (!empty($vars['IdGroup'])) {
            $IdGroup = (int) $vars['IdGroup'];
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
            $vars['ThreadVisibility'] = $ThreadVisibility;
        }

        /** @var PDBStatement_mysqli $statement */
        $statement = $this->dao->prepare("
            INSERT INTO `forum_post`
                (
                    `create_time`,
                    `message`,
                    `IdWriter`,
                    `ShortCode`,
                     `PostVisibility`,
                     `PostDeleted`,
                     `OwnerCanStillEdit`,
                     `IdContent`,
                     `edit_count`
                 )
            VALUES (NOW(), ?, ?, ?, ?, 'NotDeleted', 'Yes', 0, 0)
            ");
        $text = $this->cleanupText($vars['topic_text']);
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
            "forum_post.IdContent",
            $postId,
            $memberId,
            $language
        );

        $statement = $this->dao->prepare("
            INSERT INTO `forum_thread` (
                `title`, `first_postid`, `last_postid`,
                `ShortCode`,`IdGroup`,`ThreadVisibility`, `IdTitle`, `replies`, `views`, `stickyvalue`,
                `WhoCanReply`, `ThreadDeleted`)
            VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, 'MembersOnly', 'NotDeleted')
        ");

 		$title = strip_tags((string) $vars['topic_title']);
 		$statement->bindParam(1, $title);
 		$statement->bindParam(2, $postId);
 		$statement->bindParam(3, $postId);
        $statement->bindParam(4, $language);
        $statement->bindParam(5, $IdGroup);
        $statement->bindParam(6, $ThreadVisibility);

        $result = $statement->execute();
        $threadId = $statement->insertId();

		$ss=$this->dao->escape(strip_tags(((string) $vars['topic_title']))) ;
 		$this->InsertInFTrad($ss,"forum_thread.IdTitle",$threadId) ;

 		$statement = $this->dao->prepare("UPDATE `forum_post` SET `threadId` = ? WHERE `Id` = ?");
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

        $this->addPostToManticoreIndex($vars, $postId, $threadId, $memberId, $language);

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
               `forum_thread`.`title`,
               `forum_thread`.`IdTitle`,
               `forum_thread`.`replies`,
               `forum_thread`.`id` as IdThread,
               `forum_thread`.`views`,
               `forum_thread`.`ThreadVisibility`,
               `forum_thread`.`ThreadDeleted`,
               `forum_thread`.`WhoCanReply`,
               `forum_thread`.`first_postid`,
               `forum_thread`.`expiredate`,
               `forum_thread`.`stickyvalue`,
               `forum_thread`.`IdGroup`,
                `groups`.`Name` AS `GroupName`
            FROM
                `forum_thread`
            LEFT JOIN `groups` ON (`forum_thread`.`IdGroup` = `groups`.`id`)
            WHERE
                `forum_thread`.`id` = '$this->threadid'
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
				$topicinfo->CanReply=$this->getLoggedInMember()->Status !== \App\Doctrine\MemberStatusType::ACCOUNT_ACTIVATED;
			}
			else if ($topicinfo->WhoCanReply=="GroupsMembersOnly") {
				if ($topicinfo->IdGroup==0) {
					$topicinfo->CanReply=$this->getLoggedInMember()->Status !== \App\Doctrine\MemberStatusType::ACCOUNT_ACTIVATED ;
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
                `forum_post`.`id` AS `postid`,
                forum_post.id as IdPost,
                UNIX_TIMESTAMP(create_time) AS posttime,
                message,
                IdContent,
                IdWriter,
                g.name AS city,
                c.name AS country,
                forum_post.threadid,
                OwnerCanStillEdit,
                member.Username as OwnerUsername,
                PostVisibility,
                PostDeleted,
                forum_thread.IdGroup
            FROM
                forum_thread,
                forum_post
            LEFT
                JOIN member ON forum_post.IdWriter = member.id
            LEFT JOIN
                address AS a ON a.member_id = member.id AND a.active = 1
            LEFT JOIN
                geo__names g ON a.location = g.geoname_id
            LEFT JOIN
                geo__names c ON g.country = c.geoname_id
            WHERE
                forum_post.threadid = '%d'
                AND forum_post.threadid=forum_thread.id
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
				$sw = $this->dao->query("select forum_trads.ShortCode as IdLanguage,UNIX_TIMESTAMP(forum_trads.created) as trad_created, UNIX_TIMESTAMP(forum_trads.updated) as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,language.ShortCode,language.Name,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,language,member as mOwner, member as mTranslator where language.ShortCode=forum_trads.ShortCode and forum_trads.IdTrad=".(int) $row->IdContent." and mOwner.id=IdOwner and mTranslator.id=IdTranslator order by forum_trads.id asc");
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
UPDATE `forum_thread`
SET `views` = (`views` + 1)
WHERE `id` = '$this->threadid' LIMIT 1
            "     ;
        $this->dao->query($query);

    } // end of prepareTopic

    public function initLastPosts() {
        $query = sprintf("
SELECT
    `forum_post`.`id` AS `postid`,
    UNIX_TIMESTAMP(`create_time`) AS `posttime`,
    `message`,
	`IdContent`,
    `member`.`username` AS `OwnerUsername`,
    `IdWriter`,
	 forum_thread.`id` as `threadid`,
    `PostVisibility`,
    `PostDeleted`,
    `ThreadDeleted`,
	`OwnerCanStillEdit`,
    `geo__names`.`name` as `city`,
    `country_names`.`name` as `country`,
    `IdGroup`
FROM forum_post, forum_thread, member, address
LEFT JOIN `geo__names` ON (address.location = `geo__names`.`geoname_id`)
LEFT JOIN `geo__names` AS `country_names` ON (geo__names.country = `country_names`.`geoname_id`)
WHERE `forum_post`.`threadid` = '%d' AND `forum_post`.`IdWriter` = `member`.`id`
AND address.member_id = member.id AND address.active = 1
 AND `forum_post`.`threadid`=`forum_thread`.`id`
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
        $this->topic->posts = [];
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
          	$sw = $this->dao->query("select forum_trads.ShortCode as IdLanguage,UNIX_TIMESTAMP(forum_trads.created) as trad_created, UNIX_TIMESTAMP(forum_trads.updated) as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,language.ShortCode,language.Name,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,language,member as mOwner, member as mTranslator where language.ShortCode=forum_trads.ShortCode and forum_trads.IdTrad=".(int) $row->IdContent." and mOwner.id=IdOwner and mTranslator.id=IdTranslator order by forum_trads.id asc");
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

			$membership->updateMembership('yes', $membership->comment);
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

			$membership->updateMembership('no', $membership->comment);
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
            return [];
        }
        $TResults = new StdClass();
        $query = "
SELECT
    `members_threads_subscribed`.`id` as IdSubscribe,
    `members_threads_subscribed`.`created` AS `subscribedtime`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forum_thread`.`id` as IdThread,
    `forum_thread`.`title`,
    `forum_thread`.`IdTitle`,
    `members_threads_subscribed`.`ActionToWatch`,
    `members_threads_subscribed`.`UnSubscribeKey`,
    `members_threads_subscribed`.`notificationsEnabled`
FROM `forum_thread`,`members_threads_subscribed`
WHERE `forum_thread`.`id` = `members_threads_subscribed`.`IdThread`
AND `members_threads_subscribed`.`IdSubscriber`= {$member->id}
ORDER BY `subscribedtime` DESC
                ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve members_threads_subscribed sts via searchSubscription !');
        }

        $TResults->TData = [];
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
        $TResults->Groups = [];
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
    Username from member,
    members_threads_subscribed
WHERE member.id=members_threads_subscribed.IdSubscriber
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
        $key=MD5(random_int(100000,900000)) ;
        $query = "INSERT INTO
                members_threads_subscribed(IdThread,IdSubscriber,UnSubscribeKey,notificationsEnabled,created)
                VALUES(".$IdThread.",".$this->session->get("IdMember").",'".$this->dao->escape($key)."', 1,NOW())" ;
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
            "SELECT    `forum_post`.`id` AS `postid`,`forum_post`.`id` as IdPost, `forum_post`.`id`, UNIX_TIMESTAMP(`create_time`) AS `posttime`, UNIX_TIMESTAMP(`create_time`) AS `created`, `message`, `forum_thread`.`id` AS `IdThread`, `member`.`Username`, `geo__names`.`name` AS `city`, `country_names`.`name` AS `country`,
    `OwnerCanStillEdit`,`IdContent`,  `forum_thread`.`id` AS `threadid`,   `forum_thread`.`title`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `PostVisibility`,
    `PostDeleted`,
    `ThreadDeleted`,
    `forum_thread`.`IdTitle`,`forum_thread`.`IdGroup`,   `IdWriter`,   `member`.`Username` AS `OwnerUsername`, `groups`.`Name` AS `GroupName`
		FROM (forum_post, member, forum_thread)
LEFT JOIN `groups` ON (`forum_thread`.`IdGroup` = `groups`.`id`)
LEFT JOIN `address` ON (address.member_id = member.id AND address.active = 1)
LEFT JOIN `geo__names` ON (address.location = geo__names.geoname_id)
LEFT JOIN `geo__names` AS `country_names` ON (geo__names.country = country_names.geoname_id)
WHERE `forum_post`.`IdWriter` = %d AND `forum_post`.`IdWriter` = `member`.`id`
AND `forum_post`.`threadid` = `forum_thread`.`id`
AND ($this->PublicThreadVisibility)
AND ($this->PublicPostVisibility)
AND ($this->PostGroupsRestriction)
ORDER BY `posttime` DESC",    $IdMember   );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Posts via searchUserposts !');
        }
        $posts = [];
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
          	$sw = $this->dao->query("select forum_trads.ShortCode as IdLanguage,UNIX_TIMESTAMP(forum_trads.created) as trad_created, UNIX_TIMESTAMP(forum_trads.updated) as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,language.ShortCode,language.Name,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,language,member as mOwner, member as mTranslator where language.ShortCode=forum_trads.ShortCode and forum_trads.IdTrad=".(int) $row->IdContent." and mTranslator.id=IdTranslator  and mOwner.id=IdOwner order by forum_trads.id asc");
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
    private $page_array = [];
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
        $query = "select `IdContent` from `forum_post` where `id`=".$this->messageId ;
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

    /**
     * Returns the language as an object with ShortCode, IdLanguage (= ShortCode, used as the
     * value of the language selects) and a display Name, or null if the language is unknown.
     * 0 means English (the legacy default language).
     */
    function GetLanguageName($language) {
        $shortCode = (0 === $language || '0' === $language) ? 'en' : $this->words->languageShortCode($language);
        $query = "select ShortCode, ShortCode AS IdLanguage, Name from language where ShortCode='"
            . $this->dao->escape($shortCode) . "'";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve language in GetLanguageName entries');
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        if (!$row) {
            return null;
        }
        $row->Name = $this->getWords()->getSilent('lang_' . $row->ShortCode) . " (" . trim((string) $row->Name) . ")";
        return($row) ;
    } // end of GetLanguageName


    // This fonction will prepare a list of language to choose
    // @DefIdLanguage : an optional language (ShortCode) to propose first, -1 for none
	 // return an array of object with Name and IdLanguage (a ShortCode)
	 public function LanguageChoices($DefIdLanguage=-1) {
			$tt=[] ;
			$allreadyin=[] ;

			$propose = function (string $marker, $language) use (&$tt, &$allreadyin) {
				$row = $this->GetLanguageName($language);
				if ($row && !in_array($row->IdLanguage, $allreadyin, true)) {
					$allreadyin[] = $row->IdLanguage;
					$tt[] = $marker;
					$tt[] = $row;
				}
			};

			// First proposed will be the given language, then English, then the current user language
			if (-1 !== $DefIdLanguage && '-1' !== $DefIdLanguage && null !== $DefIdLanguage) {
				$propose("CurrentLanguage", $DefIdLanguage);
			}
			$propose("DefaultLanguage", 'en');
			$propose("UILanguage", $this->words->languageShortCode());

			$tt[] = "AllLanguages";
			// then now all available languages
			$query="select ShortCode AS IdLanguage, Name, ShortCode from language where IsWrittenLanguage = 1";
          	$s = $this->dao->query($query);
          	$langarr = [];
        	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			   if (!in_array($row->IdLanguage,$allreadyin, true)) {
			   	  $allreadyin[] = $row->IdLanguage;
			   	  $row->Name = $this->getWords()->getSilent('lang_' . $row->ShortCode) . " (" . trim((string) $row->Name) . ")";
			  	  $langarr[] = $row;
			   }
			}
			// now sort langarr and append to the $tt array
			usort($langarr, "cmpForumLang");
			return array_merge($tt, $langarr) ; // returs the array of structures
    } // end of LanguageChoices

    // This fonction will prepare a list of group in an array that the moderator can use
	 public function ModeratorGroupChoice() {
	 		$tt=[] ;

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
 		$tt=[] ;

		$query="select groups.id as IdGroup,groups.Name,count(*) as cnt from groups,membersgroups,member
										 WHERE membersgroups.IdGroup=groups.id and member.id=membersgroups.IdMember and
										  member.Status in ('Active','ActiveHidden') and member.id=".$this->session->get('IdMember')." and membersgroups.Status='In' group by groups.id order by groups.id ";
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
                forum_post p,
                forum_thread t
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

        $members = []; // collects all members that get a notification (to avoid several notifications for the same post)

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
        $groupMembers = [];
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

            $membersTemp = [];
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

		$membersTemp = [];
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

       // members_groups_subscribed is gone: group mails are a setting of the membership
       $query = sprintf("select id as IdSubscribe from membersgroups where IdGroup=%d and IdMember=%d and Status='In' and IacceptMassMailFromThisGroup='yes'",$IdGroup,$IdMember);
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
        $query = "SELECT ThreadVisibility FROM forum_thread WHERE id = " . intval($IdThread);
        $s = $this->dao->query($query);
        if (!$s) {
            // Couldn't fetch the result from the DB assume 'MembersOnly'
            return "MembersOnly";
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        return ($row->ThreadVisibility);
    }

    public function GetPostVisibility($IdPost) {
        $query = "SELECT PostVisibility FROM forum_post WHERE id = " . intval($IdPost);
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
	public function searchForums($keywords, $currentPage, $items = 30) {
        $hasForumModeratorRights = $this->BW_Right->HasRight("ForumModerator");

		$results = ['count' => 0];
		if ('' === trim((string) $keywords)) {
			$results['errors'][] = 'ForumSearchNoResults';
			return $results;
		}
		$member = $this->getLoggedInMember();
        $groupEntities = $member->getGroups();
        $groups = [0];
        foreach ($groupEntities as $group) {
            $groups[] = (int) $group->id;
        }

        $client = new Client($this->getManticoreConfig());
        $query = new Search($client);
        $query
            ->setTable('forum_rt')
            ->limit(1000)
            ->sort(['post_id' => 'DESC'])
        ;

        $matchQuery = new MatchPhrase($keywords, 'content');

        $boolQuery = new BoolQuery();
        $boolQuery->must($matchQuery);

        if (!$hasForumModeratorRights) {
            $groupPosts = new BoolQuery();
            $groupPosts
                ->must(new In('group', $groups))
                ->must(new Equals('post_visibility', 'GroupOnly'))
            ;

            $visibility = new BoolQuery();
            $visibility
                ->should(new Equals('post_visibility', 'NoRestriction'))
                ->should(new Equals('post_visibility', 'MembersOnly'))
                ->should($groupPosts)
            ;

            $publiclyVisiblePosts = new BoolQuery();
            $publiclyVisiblePosts
                ->mustNot(new Equals('post_deleted', 'Deleted'))
                ->mustNot(new Equals('thread_deleted', 'Deleted'))
                ->must($visibility)
            ;

            $limitQuery = new BoolQuery();
            $limitQuery
                ->must($publiclyVisiblePosts)
            ;

            $boolQuery->must($limitQuery);
        }

        $query->search($boolQuery);

        $queryResult = $query->get();
        $queryResult->rewind();

        $manticoreResult = [];
        while ($queryResult->valid()) {
            $hit = $queryResult->current();
            $data = $hit->getData();
            if ($hit->getScore() > 0 && !isset($manticoreResult[$data['post_id']])) {
                $manticoreResult[$data['post_id']] = $data;
                $manticoreResult[$data['post_id']]['score'] = $hit->getScore();
            }
            $queryResult->next();
        }

        if (!empty($manticoreResult)) {
            $languageId = $this->dao->escape($this->words->languageShortCode());
            $postIds = [];
            foreach ($manticoreResult as $match) {
                $postIds[] = $match['post_id'];
            }
            $separatedPostIds = implode(',', $postIds);
            $separatedGroupIds = implode(',', $groups);
            $offset = ($currentPage - 1) * $items;
            $query = "
                SELECT SQL_CALC_FOUND_ROWS
                    `forum_post`.`id`,
                    `member`.`Username`,
                    `forum_post`.`message`,
                    `forum_trads`.`Sentence`,
                    `forum_thread`.`id` AS `IdThread`,
                    `forum_thread`.`title`,
                    `forum_thread`.`IdGroup`,
                    `groups`.`Name` AS `GroupName`,
                    `forum_post`.`PostVisibility`,
                    `forum_thread`.`ThreadVisibility`,
                    `forum_thread`.`ThreadDeleted`,
                     UNIX_TIMESTAMP(`forum_post`.`create_time`) AS `created`,
                    geo__names.name AS city,
                    country_names.name AS country
                FROM
                    `forum_post`
                LEFT JOIN
                    `forum_thread` ON (`forum_post`.`threadid` = `forum_thread`.`id`)
                LEFT JOIN
                    `groups` ON (`groups`.`id` = `forum_thread`.`IdGroup`)
                LEFT JOIN
                    `forum_trads` ON (`forum_trads`.`IdTrad` = `forum_post`.`IdContent` AND `forum_trads`.`ShortCode` = '{$languageId}')
                LEFT JOIN
                    `member` ON (`forum_post`.`IdWriter` = `member`.`id`)
                LEFT JOIN
                    `address` ON `member`.`id` = `address`.`member_id` and `address`.`active` = 1 
                LEFT JOIN
                    `geo__names` ON `address`.location = `geo__names`.`geoname_id`
                LEFT JOIN
                    `geo__names` AS `country_names` ON `geo__names`.`country` = `country_names`.`geoname_id`
                WHERE
                    `forum_post`.`id` IN ({$separatedPostIds})
                    AND (`forum_thread`.`IdGroup` IN ({$separatedGroupIds}) OR `forum_thread`.`IdGroup` IS NULL)
                ORDER BY `created` DESC
                LIMIT {$items} OFFSET {$offset}
            ";
            $posts = $this->bulkLookup($query);
            $results['posts'] = $posts;

            $query = "
                SELECT
                    count(`forum_post`.`id`) AS `count`
                FROM
                    `forum_post`
                LEFT JOIN `forum_thread` on `forum_post`.`threadId` = `forum_thread`.`id`
                WHERE
                    `forum_post`.`id` IN ({$separatedPostIds})
                    AND (`forum_thread`.`IdGroup` IN ({$separatedGroupIds}) OR `forum_thread`.`IdGroup` IS NULL)
            ";
            $count = $this->singleLookup($query);
            $results['count'] = $count->count;
        } else {
            $results['errors'][] = 'ForumSearchNoResults';
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
			$keyword = htmlspecialchars((string) $vars['fs-keyword']);
			PPostHandler::clearVars();
			return PVars::getObj('env')->baseuri.$this->forums_uri.'search/'. $keyword;
		}
		return false;
	}

    private function getManticoreConfig(): array
    {
        $env = PVars::getObj('env');

        return ['host' => $env->manticore_host, 'port' => (int) $env->manticore_port];
    }

    private function addPostToManticoreIndex($vars, $postId, $threadId, $memberId, $languageShortCode)
    {
        $client = new Client($this->getManticoreConfig());
        $index = $client->table('forum_rt');
        $index->addDocument([
            'post_id' => (int) $postId,
            'post_deleted' => 'NotDeleted',
            'post_visibility' =>  $vars['PostVisibility'] ?? $vars['ThreadVisibility'],
            'thread_id' =>  (int) $threadId,
            'thread_deleted' => 'NotDeleted',
            'thread_visibility' => $vars['ThreadVisibility'] ?? $this->GetThreadVisibility($threadId),
            'content' => $vars['topic_text'],
            'group' => (int) ($vars['IdGroup'] ?? 0),
            'author' => (int) $memberId,
            'locale' => $this->words->languageShortCode($languageShortCode),
        ]);
    }

    /**
     * Updates the search index document of a post: its text (topic_text, a moderator's
     * Sentence or NewTranslatedPost) and/or the given attributes (e.g. post_deleted).
     */
    private function updatePostInManticoreIndex($vars, array $attributes = [])
    {
        $postId = (int) ($vars['IdPost'] ?? $this->messageId);
        $threadId = (int) ($vars['IdThread'] ?? 0);
        $vars['Sentence'] = $vars['Sentence'] ?? $vars['NewTranslatedPost'] ?? $vars['topic_text'] ?? null;
        if (null === $vars['Sentence'] && empty($attributes)) {
            return;
        }

        $client = new Client($this->getManticoreConfig());
        // Find document in index
        $query = new Search($client);
        $query
            ->setTable('forum_rt')
            ->filter('post_id', 'equals', $postId)
        ;
        if ($threadId > 0) {
            $query->filter('thread_id', 'equals', $threadId);
        }

        $results = $query->get();
        $results->rewind();
        if (!$results->valid()) {
            // Post isn't in the search index (yet), nothing to update
            return;
        }
        $hit = $results->current();
        $data = $hit->getData();
        if (null !== $vars['Sentence']) {
            $data['content'] = $vars['Sentence'];
        }
        $data = array_merge($data, $attributes);

        // Then replace with new content
        $index = $client->table('forum_rt');
        $index->replaceDocument($data, $hit->getId());
    }
} // end of class Forums


class Topic {
    public $topicinfo;
    public $posts = [];
}

class Board implements Iterator {
	public $THREADS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no

    public function __construct(&$dao, private $boardname, private $link, $session, private $board_description=false, $IdGroup=false, $no_forumsgroup=false) {
		$this->THREADS_PER_PAGE=Forums::CV_THREADS_PER_PAGE  ; //Variable because it can change wether the user is logged or no
		$this->POSTS_PER_PAGE=Forums::CV_POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no

		$this->BW_Right = MOD_right::get();
		$this->session = $session;

		if (!$this->session->has( 'IdMember' )) {
			$this->THREADS_PER_PAGE=100  ; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE=self::CV_POSTS_PER_PAGE ; // Variable because it can change wether the user is logged or no
		}

        $this->dao =& $dao;
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
	public function FilterThreadListResultsWithIdCriteria($ids = []) {
        $wherethread="" ;

		if (count($ids) <> 0) {
			$wherethread = " AND `forum_thread`.`id` in ('" . implode("', '", $ids) . "') ";
			return $wherethread;
		}

        if (isset($this->IdGroup)) {
            if (is_numeric($this->IdGroup)) {
                $wherethread .= sprintf("AND `forum_thread`.`IdGroup` = '%d' ", $this->IdGroup);
            } elseif ($this->IdGroup === null) {
                $wherethread .= "AND `forum_thread`.`IdGroup` IS NULL ";
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
    public function initThreads($page = 1, $showsticky = true, $ids = []) {

		$this->threads = [];
        $wherethread=$this->FilterThreadListResultsWithIdCriteria($ids) ;

        if ($showsticky) {
            $orderby = " ORDER BY `stickyvalue` ASC,`last_create_time` DESC";
        } else {
            $orderby = " ORDER BY `last_create_time` DESC";
        }

		$query = "SELECT COUNT(*) AS `number` FROM `forum_thread` WHERE 1 ".$wherethread;
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

		$query = "SELECT SQL_CALC_FOUND_ROWS `forum_thread`.`id`,
		 		  `forum_thread`.`id` as IdThread, `forum_thread`.`title`,
				  `forum_thread`.`IdTitle`,
				  `forum_thread`.`IdGroup`,
				  `forum_thread`.`replies`,
		          `forum_thread`.`stickyvalue`,
		          `groups`.`Name` as `GroupName`,
                  `ThreadVisibility`,
	              `ThreadDeleted`,
				  `forum_thread`.`views`,
				  `first`.`id` AS `first_postid`,
				  `first`.`idWriter` AS `first_authorid`,
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`id` AS `last_postid`,
				  `last`.`idWriter` AS `last_authorid`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
		$query .= "`first_member`.`Username` AS `first_author`,`last_member`.`Username` AS `last_author`" ;
		$query .= "FROM `forum_thread` LEFT JOIN `forum_post` AS `first` ON (`forum_thread`.`first_postid` = `first`.`id`)" ;
		$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forum_thread`.`IdGroup`)" ;
		$query .= "LEFT JOIN `forum_post` AS `last` ON last.id =
		( SELECT    MAX(id)
              FROM      forum_post fp
              WHERE fp.threadid = `forum_thread`.`id`
              AND fp.PostDeleted = 'NotDeleted'
              ) ";
		$query .= "LEFT JOIN `member` AS `first_member` ON (`first`.`IdWriter` = `first_member`.`id`)" ;
		$query .= "LEFT JOIN `member` AS `last_member` ON (`last`.`IdWriter` = `last_member`.`id`)" ;
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

    private $threads = [];
    public function getThreads() {
        return $this->threads;
    }
    public function getBoardName() {
        return $this->boardname;
    }

    public function getNumberOfThreads() {
        return $this->numberOfThreads;
    }

    public function getTotalThreads() {
        return $this->totalThreads;
    }

    private $subboards = [];

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
