<?php
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
    const CV_POSTS_PER_PAGE = 200;
    const CV_TOPMODE_CATEGORY=1; // Says that the forum topmode is for categories
    const CV_TOPMODE_LASTPOSTS=2; // Says that the forum topmode is for lastposts

    const NUMBER_LAST_POSTS_PREVIEW = 5; // Number of Posts shown as a help on the "reply" page
	
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
   if (isset($_SESSION['IdLanguage'])) {
	   $DefLanguage=$_SESSION['IdLanguage'] ;
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
* 3) the content of the current $_SESSION['IdLanguage'] of the current membr if it set
* 4) The default language (0)
*
* returns the id of the created trad
* 
*/ 
function InsertInFTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
    $this->words = new MOD_words;
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
* 3) the content of the current $_SESSION['IdLanguage'] of the current membr if it set
* 4) The default language (0)
* 
*/ 
function ReplaceInFTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
    $this->words = new MOD_words;
	return ($this->words->ReplaceInFTrad($ss,$TableColumn,$IdRecord, $IdTrad, $IdOwner )) ;
} // end of ReplaceInFTrad

/**
* this function returns a structure with the result of a vote for a given IdPost
* according to the current logged member
**/
function GetPostVote($IdPost) { 
	if (empty($_SESSION["IdMember"])) {
		return ;
	}
	$IdMember=$_SESSION["IdMember"] ;
	$Res->PossibleAction='none' ;
    $member = $this->getLoggedInMember();
    $post = $this->createEntity('Post',$IdPost);
    $MyVote = $this->createEntity('PostVote')->findVote($post, $member);
	//$MyVote=$this->singleLookup("select * from forums_posts_votes where IdPost=".$IdPost." and IdContributor=".$IdMember) ;
	$PossibleChoice=array('Yes','DontKnow','DontCare','No') ;
	$Res->PossibleChoice=$PossibleChoice ;
	if ($MyVote or ($_SESSION["MemberStatus"]!="Active")) { // Members who are not cative cannot vote (for example admin who has status ActiveHidden)
		if ($MyVote->Choice) {
			$Res->Choice=$MyVote->Choice ;
		}
		$Res->PossibleAction="ShowResult" ;
		$Res->Total=0 ;
        $votes = $this->createEntity('PostVote')->getResultForPost($post);
		foreach ($PossibleChoice as $cc)
        {
            $Res->$cc = $votes[$cc];
			$Res->Total += $votes[$cc] ;
		}
	}
	else {
		$Res->PossibleAction="ProposeVote" ;
	}
	
//	print_r($Res) ; die(0) ;
	return($Res) ;
} // end of GetPostVote

/**
* this function returns the Id of the thread corresponding to a certain $IdPost
**/
function GetIdThread($IdPost) {
	$rr=$this->singleLookup("select threadid as IdThread from forums_posts where id=".$IdPost) ;
	if (!empty($rr->IdThread)) {
		return($rr->IdThread) ;
	}
	else {
		return(0) ;
	}
} //GetIdThread

/**
* this function allows to vote for the given IdPost with teh value $Value
**/
function VoteForPost($IdPost,$Value) { 
	if (empty($_SESSION["IdMember"])) {
		return ;
	}
	
	$IdMember=$_SESSION["IdMember"] ;
	$MyVote=$this->singleLookup("select * from forums_posts_votes where IdPost=".$IdPost." and IdContributor=".$IdMember) ;
	if (!empty($MyVote->IdPost)) {
		$ss="update forums_posts_votes set NbUpdates=NbUpdates+1,Choice='".$Value."' where IdPost=".$IdPost." and IdContributor=".$IdMember ;
		$qq = $this->dao->query($ss);
		if (!$qq) {
            throw new PException('Update VoteForPost failed '.$ss.' !');
		}
	    MOD_log::get()->write("Updating vote for forum post #".$IdPost,"Forum") ; 				
	}
	else {
		$ss="insert into forums_posts_votes(IdPost,IdContributor,Choice,created) values(".$IdPost.",".$IdMember.",'".$Value."',now())" ;
		$qq = $this->dao->query($ss);
		if (!$qq) {
            throw new PException('Insert VoteForPost failed '.$ss.' !');
		}
	    MOD_log::get()->write("inserting vote for forum post #".$IdPost,"Forum") ; 				
	}
	
	
} // end of VoteForPost

/**
* this function allows to vote for the given IdPost with teh value $Value
**/
function DeleteVoteForPost($IdPost) { 
	if (empty($_SESSION["IdMember"])) {
		return ;
	}
	
	$IdMember=$_SESSION["IdMember"] ;
	$MyVote=$this->singleLookup("select * from forums_posts_votes where IdPost=".$IdPost." and IdContributor=".$IdMember) ;
	if (!empty($MyVote->IdPost)) {
		$ss="delete from  forums_posts_votes where IdPost=".$IdPost." and IdContributor=".$IdMember ;
		$qq = $this->dao->query($ss);
		if (!$qq) {
            throw new PException('Delete VoteForPost failed '.$ss.' !');
		}
	    MOD_log::get()->write("Deleting vote for forum post #".$IdPost,"Forum") ; 				
	}
	
} // end of DeleteVoteForPost

/**
* FindAppropriatedLanguage function will retrieve the appropriated default language 
* for a member who want to reply to a thread (started with the#@IdPost post)
* this retriewal is made according to the language of the post, the current language of the user
*/
function FindAppropriatedLanguage($IdPost=0) {
   $ss="select `IdContent` FROM `forums_posts` WHERE `id`=".$IdPost ;
	$q=mysql_query($ss) ;
	$row=mysql_fetch_object($q) ;
	
//	$q = $this->_dao->query($ss);
//	$row = $q->fetch(PDB::FETCH_OBJ);
	if (!isset($row->IdContent)) {
	   return (0) ;
	}
	else {
	   $IdTrad=$row->IdContent ;
	}

	// Try IdTrad with current language of the member
  	$query ="SELECT IdLanguage FROM `forum_trads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$_SESSION["IdLanguage"] ;
	$q = mysql_query($query);
	$row = mysql_fetch_object($q) ;
	if (isset ($row->IdLanguage)) {
	   return($row->IdLanguage) ;
	}

	// Try with the original language used for this post	
	$query ="SELECT `IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad."  order by id asc limit 1" ;
	$q = mysql_query($query);
	$row = mysql_fetch_object($q) ;
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
		
        $layoutbits = new MOD_layoutbits();
		
		switch($layoutbits->GetPreference("PreferenceForumFirstPage")) {
			case "Pref_ForumFirstPageLastPost":
				$this->setTopMode(Forums::CV_TOPMODE_LASTPOSTS) ;
				break ;
			case "Pref_ForumFirstPageCategory":
				$this->setTopMode(Forums::CV_TOPMODE_CATEGORY) ;
				break ;
			default:
				$this->setTopMode(Forums::CV_TOPMODE_LASTPOSTS) ;
				break ;
		}
		
		if (!isset($_SESSION['IdMember'])) {
			$this->THREADS_PER_PAGE = 100; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE = 200; // Variable because it can change wether the user is logged or no
		}
		
		$MyGroups = array();

		
		$this->words = new MOD_words();
		$this->BW_Right = MOD_right::get();
		$this->IdGroup = 0; // By default no group
		$this->ByCategories = false; // toggle or not toglle the main view is TopCategories or TopLevel
		$this->ForumOrderList = $layoutbits->GetPreference("PreferenceForumOrderListAsc");

		//	Decide if it is an active LoggeMember or not
		if ((empty($_SESSION["IdMember"]) or empty($_SESSION["MemberStatus"]) or ($_SESSION["MemberStatus"] == 'Pending') or $_SESSION["MemberStatus"] == 'NeedMore') ) {
			$this->PublicThreadVisibility=" (ThreadVisibility = 'NoRestriction') AND (ThreadDeleted != 'Deleted')";
			$this->PublicPostVisibility = " (PostVisibility = 'NoRestriction') AND (PostDeleted != 'Deleted')";
			$this->ThreadGroupsRestriction = " (IdGroup = 0 OR ThreadVisibility = 'NoRestriction')";
			$this->PostGroupsRestriction = " (IdGroup = 0 OR PostVisibility = 'NoRestriction')" ;
		}
		else {
			$this->PublicThreadVisibility = "(ThreadVisibility != 'ModeratorOnly') AND (ThreadDeleted != 'Deleted')" ;
			$this->PublicPostVisibility = "(PostVisibility != 'ModeratorOnly') AND (PostDeleted !='Deleted')" ;
			$this->PostGroupsRestriction = " PostVisibility IN ('MembersOnly','NoRestriction') OR (PostVisibility='GroupOnly' AND IdGroup in(0" ;
			$this->ThreadGroupsRestriction = " ThreadVisibility IN ('MembersOnly','NoRestriction') OR (ThreadVisibility = 'GroupOnly' and IdGroup in(0" ;
			$qry = $this->dao->query("SELECT IdGroup FROM membersgroups WHERE IdMember = " . $_SESSION["IdMember"] . " AND Status = 'In'");
			if (!$qry) {
				throw new PException('Failed to retrieve groups for member id =#'.$_SESSION["IdMember"].' !');
			}
			while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
				$this->PostGroupsRestriction = $this->PostGroupsRestriction . "," . $rr->IdGroup;
				$this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . "," . $rr->IdGroup;
				array_push($MyGroups,$rr->IdGroup) ; // Save the group list
			}	;
			$this->PostGroupsRestriction = $this->PostGroupsRestriction . "))";
			$this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . "))";
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
		if ($this->ForumOrderList=="Yes") {
			$this->ForumOrderList="No" ;
		}
		else {
			$this->ForumOrderList="Yes" ;
		}
		$ss="select Value,memberspreferences.id as id,IdMember,preferences.id as IdPreference from (preferences) " ;
		$ss=$ss." left join memberspreferences on preferences.id=memberspreferences.IdPreference and memberspreferences.IdMember=".$_SESSION['IdMember'] ;
		$ss=$ss." where codeName='PreferenceForumOrderListAsc'" ;
		
		$qq = $this->dao->query($ss);
		$rr=$qq->fetch(PDB::FETCH_OBJ) ;
		if (empty($rr->Value)) {
			$ss="insert into memberspreferences(created,IdPreference,IdMember,Value) " ;
			$ss=$ss." values(now(),".$rr->IdPreference.",".$_SESSION['IdMember'].",'".$this->ForumOrderList."')" ;
		}
		else {
			$ss="update memberspreferences set Value='".$this->ForumOrderList."' where id=".$rr->id ;
		}
		$qq = $this->dao->query($ss);
		if (!$qq) {
            throw new PException('switchForumOrderList '.$ss.' !');
		}
        MOD_log::get()->write("Switching PreferenceForumOrderListAsc to [".$this->ForumOrderList."]", "ForumModerator");
	} // end of switchForumOrderList


    // This switch the preference switchShowMyGroupsTopicsOnly
    public function switchShowMyGroupsTopicsOnly() {
        if (!$member = $this->getLoggedInMember()) {
            return;
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
    AND m.IdMember = " . $_SESSION['IdMember'] . "  
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
    $_SESSION['IdMember'] . ", 
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
        header('Location: ' . PVars::getObj('env')->baseuri . 'forums');
        PPHP::PExit();
    } // end of switchShowMyGroupsTopicsOnly
    

    public function checkGroupMembership($group_id) {
        if (in_array($group_id,$this->MyGroups)) {
            return true;
        }
        return false;
    } // end of checkGroupMembership
 
    public static $continents = array(
        'AF' => 'Africa',
        'AN' => 'Antarctica',
        'AS' => 'Asia',
        'EU' => 'Europe',
        'NA' => 'North America',
        'SA' => 'South Amercia',
        'OC' => 'Oceania'
    );
    
    private function boardTopLevelLastPosts($showsticky = true) {
        if ($this->tags) {
            $subboards = array();
            $taginfo = $this->getTagsNamed();
            
            $url = 'forums';
            
            $subboards[$url] = 'Forums';
            
            for ($i = 0; $i < count($this->tags) - 1; $i++) {
                if (isset($taginfo[$this->tags[$i]])) {
                    $url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
                    $subboards[$url] = $taginfo[$this->tags[$i]];
                }
            }
            
            if ((count($this->tags)>0)and isset($taginfo[0])) {
               $title = $this->words->getFormatted("Forum_label_tag").":".$taginfo[$this->tags[count($this->tags) -1]];
               $href = $url.'/t'.$this->tags[count($this->tags) -1].'-'.$title;
            }
            else {
               $title = "no tags";
               $href = $url.'/t'.'-'.$title;
            }
            
			 
            $this->board = new Board($this->dao, $title, $href, $subboards, $this->tags, $this->continent);
        } else {
            $this->board = new Board($this->dao, 'Forums', '.');
            foreach (Forums::$continents as $code => $name) {
                $this->board->add(new Board($this->dao, $name, 'k'.$code.'-'.$name));
            }
        }
        $this->board->initThreads($this->getPage(), $showsticky);
    } // end of boardTopLevelLastPosts
    

/**
* This retrieve the list of categories
* and the X last post under categories
*/ 
    private function boardTopLevelCategories() {
					
        if ($this->tags) {
			$this->boardTopLevelLastPosts() ;
			return ;
        } 
		$this->board=new Board($this->dao, 'Forums', '.');
		
 		$query="select id as IdTagCategory,IdName,IdDescription from forums_tags where Type='Category' order by tag_position asc " ;
		$scat = $this->dao->query($query);
		if (!$scat) {
            throw new PException('boardTopLevelCategories::Could not retrieve the categories tags!');
		}
		

		$ListBoard=array() ;
		$CategoryList="" ;
		// for all the tags which are categories
		while ($rowcat = $scat->fetch(PDB::FETCH_OBJ)) {
			if ($CategoryList!="") {
				$CategoryList.="," ;
			}
			$CategoryList.=$rowcat->IdTagCategory ;

		// We are going to seek for the X last post which have this tag
			$tt=array() ;
			array_push($tt,$rowcat) ;
			$board=new Board($this->dao, 'Forums', '.',null,$tt);
			$rowcat->board=$board ;


			$rowcat->threads=$board->LoadThreads($rowcat->IdTagCategory);

			array_push( $ListBoard,$rowcat) ;
		}
			
		$rowcat->threads=$board->LoadThreads(0,$CategoryList); // Load some post without categories
		array_push( $ListBoard,$rowcat) ;
		
		$this->ListBoards=$ListBoard ;
    } // end of boardTopLevelCategories
/**

*/

    private function boardContinent()  {
        if (!isset(Forums::$continents[$this->continent]) || !Forums::$continents[$this->continent]) {
            throw new PException('Invalid Continent');
        }
        
        $subboards = array('forums/' => 'Forums');
        
        $url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent];
        $href = $url;
        if ($this->tags) {
            $taginfo = $this->getTagsNamed();
            
            $subboards[$url] = Forums::$continents[$this->continent];
            
            for ($i = 0; $i < count($this->tags) - 1; $i++) {
                if (isset($taginfo[$this->tags[$i]])) {
                    $url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
                    $subboards[$url] = $taginfo[$this->tags[$i]];
                }
            }
            
            $title = $taginfo[$this->tags[count($this->tags) -1]];
            
        } else {
            $title = Forums::$continents[$this->continent];
        }
        
        $this->board = new Board($this->dao, $title, $href, $subboards, $this->tags, $this->continent);
        
        $countries = $this->getAllCountries($this->continent);
        foreach ($countries as $code => $country) {
            $this->board->add(new Board($this->dao, $country, 'c'.$code.'-'.$country));
        }
        $this->board->initThreads($this->getPage());
    } // end of boardContinent
    
    public function getAllCountries($continent) {
        $query = sprintf(
            "
SELECT `iso_alpha2`, `name` 
FROM `geonames_countries` 
WHERE `continent` = '%s'
ORDER BY `name` ASC
            ",
            $continent
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve countries!');
        }
        $countries = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $countries[$row->iso_alpha2] = $row->name;
        }
        return $countries;    
    }
    
    private function boardAdminCode() {
        $query = sprintf(
            "
SELECT `name`, `continent` 
FROM `geonames_countries` 
WHERE `iso_alpha2` = '%s'
            ",
            $this->countrycode
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('No such Country');
        }
        $countrycode = $s->fetch(PDB::FETCH_OBJ);
        
        $navichain = array('forums/' => 'Forums', 
            'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/' => Forums::$continents[$this->continent],
            'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/' => $countrycode->name);
    
        $query = sprintf(
            "
SELECT `name`
FROM `geonames_admincodes` 
WHERE `country_code` = '%s' AND `admin_code` = '%s'
            ",
            $this->countrycode,
            $this->admincode
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('No such Admincode');
        }
        $admincode = $s->fetch(PDB::FETCH_OBJ);

		if (!isset($admincode->name)) { // Added by JeanYves to trap what might be a geoname problem which creates phperrorlogs
		    MOD_log::get()->write("Forum::boardAdminCode Problem with geo [".$query."] as failed for country [".$countrycode->name."]","Bug") ; 				
		}

        $url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/a'.$this->admincode.'-'.$admincode->name;
        $href = $url;
        if ($this->tags) {
            $taginfo = $this->getTagsNamed();
            
            
            $navichain[$url] = $admincode->name;
            
            for ($i = 0; $i < count($this->tags) - 1; $i++) {
                if (isset($taginfo[$this->tags[$i]])) {
                    $url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
                    $navichain[$url] = $taginfo[$this->tags[$i]];
                }
            }
            
            $title = $taginfo[$this->tags[count($this->tags) -1]];
        } else {
            $title = $admincode->name;
        }

        $this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode, $this->admincode);
        
        $locations = $this->getAllLocations($this->countrycode, $this->admincode);
        foreach ($locations as $geonameid => $name) {
            $this->board->add(new Board($this->dao, $name, 'g'.$geonameid.'-'.$name));
        }
        $this->board->initThreads($this->getPage());
    } // end of boardAdminCode
    
    public function getAllLocations($countrycode, $admincode)
    {
        $query = sprintf(
            "
SELECT `geonameid`, `name` 
FROM `geonames_cache` 
WHERE `fk_countrycode` = '%s' AND `fk_admincode` = '%s'
ORDER BY `population` DESC
LIMIT 100
            ",
            $countrycode,
            $admincode
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Districts!');
        }
        $locations = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $locations[$row->geonameid] = $row->name;
        }
        natcasesort($locations);
        return $locations;        
    }
    

// This build the borad for the $this->Country
    private function boardCountry()    {
        $query = sprintf(
            "
SELECT `name`, `continent` 
FROM `geonames_countries` 
WHERE `iso_alpha2` = '%s'
            ",
            $this->countrycode
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('No such Country');
        }
        $countrycode = $s->fetch(PDB::FETCH_OBJ);
        
        $navichain = array('forums/' => 'Forums', 
            'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/' => Forums::$continents[$this->continent]);
        
        $url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name;
        $href = $url;
        if ($this->tags) {
            $taginfo = $this->getTagsNamed();
            
            
            $navichain[$url] = $countrycode->name;
            
            for ($i = 0; $i < count($this->tags) - 1; $i++) {
                if (isset($taginfo[$this->tags[$i]])) {
                    $url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
                    $navichain[$url] = $taginfo[$this->tags[$i]];
                }
            }
            
            $title = $taginfo[$this->tags[count($this->tags) -1]];
        } else {
            $title = $countrycode->name;
        }
        
        
        $this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode);
        
        $admincodes = $this->getAllAdmincodes($this->countrycode);
        foreach ($admincodes as $code => $name) {
            $this->board->add(new Board($this->dao, $name, 'a'.$code.'-'.$name));
        }
        
        $this->board->initThreads($this->getPage());
    } // end of boardCountry
    
// This build the board for the $this->IdGroup
    private function boardGroup($showsticky = true) {

        $query = sprintf("SELECT `Name` FROM `groups` WHERE `id` = %d",$this->IdGroup);
        $gr = $this->dao->query($query);
        if (!$gr) {
            throw new PException('No such IdGroup=#'.$this->IdGroup);
        }
        $group = $gr->fetch(PDB::FETCH_OBJ);

        $subboards = array();
		$gtitle= $this->words->getFormatted("ForumGroupTitle", $this->getGroupName($group->Name)) ;
        if ($this->tags) {
            $taginfo = $this->getTagsNamed();
            $url = 'forums';
            
            $subboards[$url] = 'Forums';
            
            for ($i = 0; $i < count($this->tags) - 1; $i++) {
                if (isset($taginfo[$this->tags[$i]])) {
                    $url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
                    $subboards[$url] = $taginfo[$this->tags[$i]];
                }
            }
            
						
            if (count($this->tags)>0 && $this->tags[0]) {
               $title =$gtitle." ".$taginfo[$this->tags[count($this->tags) -1]];
               $href = $url.'/t'.$this->tags[count($this->tags) -1].'-'.$title;
            }
            else {
               $title =  $gtitle." "."no tags";
               $href = $url.'/t'.'-'.$title;
            }
            
			 
            $this->board = new Board($this->dao, $title, $href, $subboards, $this->tags, $this->continent,false,false,false,false,$this->IdGroup);
            $this->board->initThreads($this->getPage(), $showsticky);
        } else {
            $this->board = new Board($this->dao, $gtitle, ".", $subboards, $this->tags, $this->continent,false,false,false,false,$this->IdGroup);
//            foreach (Forums::$continents as $code => $name) {
//                $this->board->add(new Board($this->dao, $name, 'k'.$code.'-'.$name));
//            }
            $this->board->initThreads($this->getPage(), $showsticky);
        }
    } // end of boardGroup
    
	/*
	@ $Name name of the group (direct from groups.Name
	*/
    public function getGroupName($Name) {
//		return($this->words->getFormatted("Group_" . $Name)) ;
		return($Name) ;
	
	}
	
    public function getAllAdmincodes($country_code)
    {
        $query = sprintf(
            "
SELECT `admin_code`, `name` 
FROM `geonames_admincodes` 
WHERE `country_code` = '%s'
ORDER BY `name` ASC
            ",
            $country_code
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Districts!');
        }
        $admincodes = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $admincodes[$row->admin_code] = $row->name;
        }
        return $admincodes;
    }
    
    private function boardLocation()    {
        $query = sprintf(
            "
SELECT `name`, `continent` 
FROM `geonames_countries` 
WHERE `iso_alpha2` = '%s'
            ",
            $this->countrycode
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('No such Country');
        }
        $countrycode = $s->fetch(PDB::FETCH_OBJ);

    
        $query = sprintf(
            "
SELECT `name` 
FROM `geonames_admincodes` 
WHERE `country_code` = '%s' AND `admin_code` = '%s'
            ",
            $this->countrycode, $this->admincode
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('No such Admincode');
        }
        $admincode = $s->fetch(PDB::FETCH_OBJ);
        
        $navichain = array(
            'forums/' => 'Forums', 
            'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/' => Forums::$continents[$this->continent],
            'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/' => $countrycode->name,
            'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/a'.$this->admincode.'-'.$admincode->name.'/' => $admincode->name
        );
                
        $query = sprintf(
            "
SELECT `name` 
FROM `geonames_cache` 
WHERE `geonameid` = '%d'
            ",
            $this->geonameid
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('No such Country');
        }
        $geonameid = $s->fetch(PDB::FETCH_OBJ);
		if (!isset($geonameid->name)) {
			$geonameid->name='' ; // to avoid a Notice error when trying to display a not found name (fix for ticket #484 the Astghadzor case)
		}
        
        $url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/a'.$this->admincode.'-'.$admincode->name.'/g'.$this->geonameid.'-'.$geonameid->name;
        $href = $url;
        if ($this->tags) {
            $taginfo = $this->getTagsNamed();
            
            $navichain[$url] = $geonameid->name;
            for ($i = 0; $i < count($this->tags) - 1; $i++) {
                if (isset($taginfo[$this->tags[$i]])) {
                    $url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
                    $navichain[$url] = $taginfo[$this->tags[$i]];
                }
            }
            
            $title = $taginfo[$this->tags[count($this->tags) -1]];
        } else {
            $title = $geonameid->name;
        }
        
        $this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode, $this->admincode, $this->geonameid);
        $this->board->initThreads($this->getPage());
    } // end of boardLocation
    
    /**
    * Fetch all required data for the view to display a forum
		* this data are stored in $this->board
    */
    public function prepareForum($showsticky = true) {
        if (!$this->geonameid && !$this->countrycode && !$this->continent && !$this->IdGroup) {
			if($this->TopMode==Forums::CV_TOPMODE_CATEGORY) {
				$this->boardTopLevelCategories();
			}
			elseif ($this->TopMode==Forums::CV_TOPMODE_LASTPOSTS) {
				$this->boardTopLevelLastPosts($showsticky);
			}
			else {
				$this->boardTopLevelLastPosts($showsticky);
			}
		} else if ($this->continent && !$this->geonameid && !$this->countrycode) { 
            $this->boardContinent();
        } else if ($this->IdGroup) { 
            $this->boardGroup($showsticky);
        } else if (isset($this->admincode) && $this->admincode && $this->continent && $this->countrycode && !$this->geonameid) { 
            $this->boardadminCode();
        } else if ($this->continent && $this->countrycode && !$this->geonameid) {
            $this->boardCountry();
        } else if ($this->continent && $this->countrycode && $this->geonameid && isset($this->admincode) && $this->admincode) { 
            $this->boardLocation();
        } else {
            if (PVars::get()->debug) {
                throw new PException('Invalid Request');
            } else {
                PRequest::home();
            }
        }
    } // end of prepareForum
    
    private $board;
    private $topboard;
    public function getBoard() {
        return $this->board;
    }
    
    public function createProcess() {
        if (!($User = APP_User::login())) {
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
    `postid`,
    `authorid`,
    `IdWriter`,
    `HasVotes`,
    `IdLocalEvent`,
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
    `forums_threads`.`continent`,
    `forums_threads`.`IdGroup`,
    `forums_threads`.`geonameid`,
    `forums_threads`.`admincode`,
    `forums_threads`.`countrycode`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
WHERE `postid` = $this->messageId
and ($this->PublicPostVisibility)
            "
        ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('getEditData :: Could not retrieve Postinfo!');
        }
        $vars =& PPostHandler::getVars($callbackId);
        $vars = $s->fetch(PDB::FETCH_ASSOC);
        $tags = array();
        
        // retrieve tags for the current post ($this->messageId)
        $query =    " 
SELECT forums_tags.IdName
FROM `tags_threads`,`forums_posts`,`forums_threads`,`forums_tags`
WHERE `forums_posts`.`threadid` = `forums_threads`.`id`
AND `tags_threads`.`IdThread` = `forums_threads`.`id` 
AND `forums_posts`.`id` = $this->messageId and `forums_tags`.`id`=`tags_threads`.`IdTag`" ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('getEditData :: Failed to retrieve the tags!');
        }
				
//				echo "query=",$query,"<br \>" ;

        $tag=array() ;
        while ($rTag = $s->fetch(PDB::FETCH_OBJ)) {
              if (!empty($rTag->IdName))  $tags[]=$this->words->fTrad($rTag->IdName) ; // Find the name according to current language in associations with this tag
        }
        
        $vars['tags'] = $tags;
        $this->admincode = $vars['admincode'];
        $this->continent = $vars['continent'];
        $this->countrycode = $vars['countrycode'];
        $this->geonameid = $vars['geonameid'];
        $this->threadid = $vars['threadid'];
        $this->IdGroup = $vars['IdGroup'];
				

    } // end of get getEditData
    
    /*
     * Write in the database the changed data
	  * when a post is edited, this also write a log and 
	  * this call editPost and may be editTopic which does the update in the database  
	  * by the user
     */
    public function editProcess() {
        if (!($User = APP_User::login())) {
            return false;
        }
        
        $vars =& PPostHandler::getVars();
        
        $query = 
            "
SELECT
    `postid`,
    `authorid`,
    `IdWriter`,
    `forums_posts`.`threadid`, 
    `HasVotes`,
    `IdLocalEvent`,
    `first_postid`,
	`OwnerCanStillEdit`,
    `PostDeleted`,
    `PostVisibility`,
    `ThreadVisibility`,
    `ThreadDeleted`,
	`forums_threads`.`IdGroup`,
    `last_postid`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
WHERE `postid` = $this->messageId
            "
        ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Postinfo!');
        }
        $postinfo = $s->fetch(PDB::FETCH_OBJ);
        
//        if ($this->BW_Right->HasRight("ForumModerator","Edit") || ($User->hasRight('edit_own@forums') && $postinfo->authorid == $User->getId())) {
        if ($this->BW_Right->HasRight("ForumModerator","Edit") ||  ($postinfo->IdWriter == $_SESSION["IdMember"] and $postinfo->OwnerCanStillEdit=="Yes")) {
            $is_topic = ($postinfo->postid == $postinfo->first_postid);
            
            if ($is_topic) {
                $vars_ok = $this->checkVarsTopic($vars);
            } else {
                $vars_ok = $this->checkVarsReply($vars);
            }
            if ($vars_ok) {
                $this->dao->query("START TRANSACTION");
        
                if ($is_topic) {
                    $vars['PostVisibility'] = $vars['ThreadVisibility'];
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
		 $this->words->MakeRevision($id, "forum_trads",$_SESSION["IdMember"], $DoneBy = "DoneByModerator")  ;
		 $IdLanguage=(int)$P_IdLanguage ;
		 $Sentence= mysql_real_escape_string($P_Sentence) ;

        MOD_log::get()->write("Updating data for IdForumTrads=#".$id." Before [".addslashes($rBefore->Sentence)."] IdLanguage=".$rBefore->IdLanguage." <br />\nAfter [".$Sentence."] IdLanguage=".$IdLanguage, "ForumModerator");
		 $sUpdate="update forum_trads set Sentence='".$Sentence."',IdLanguage=".$IdLanguage.",IdTranslator=".$_SESSION["IdMember"]." where id=".$id ;
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
    `HasVotes`,
    `PostVisibility`,
    `IdLocalEvent`,
		OwnerCanStillEdit,IdWriter,forums_posts.IdFirstLanguageUsed as post_IdFirstLanguageUsed,forums_threads.IdFirstLanguageUsed as thread_IdFirstLanguageUsed,forums_posts.id,IdWriter,IdContent,forums_threads.IdTitle,forums_threads.first_postid from `forums_posts`,`forums_threads` WHERE forums_posts.threadid=forums_threads.id and forums_posts.id = ".$this->messageId ;
        $s=$this->dao->query($query);
        $rBefore=$s->fetch(PDB::FETCH_OBJ) ;
        
        $query = sprintf("UPDATE `forums_posts` SET `message` = '%s', `last_edittime` = NOW(), `last_editorid` = '%d', `edit_count` = `edit_count` + 1 WHERE `postid` = '%d'",
        $this->dao->escape($this->cleanupText($vars['topic_text'])), $editorid, $this->messageId);
        $this->dao->query($query);
		$this->ReplaceInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])),"forums_posts.IdContent",$rBefore->id, $rBefore->IdContent, $rBefore->IdWriter) ;

		// case the update concerns the reference language of the posts
		if ($rBefore->post_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	$query="update forums_posts set message='".$this->dao->escape($this->cleanupText($vars['topic_text']))."' where postid=".$this->messageId ;
        	$s=$this->dao->query($query);
		}
		 
		// case the visibility has changed
		if ($rBefore->PostVisibility!=$vars['PostVisibility']) {
		 	$query="update forums_posts set PostVisibility='".$vars['PostVisibility']."' where postid=".$this->messageId ;
        	$s=$this->dao->query($query);
			MOD_log::get()->write("Changing Post Visibility from <b>".$rBefore->PostVisibility."</b> to <b>".$vars['PostVisibility']."</b>", "Forum");
		}
		 
		 
		 
		// If this is the first post, may be we can update the title
		if ($rBefore->first_postid==$rBefore->id) {
		 	$this->ReplaceInFTrad($this->dao->escape($this->cleanupText($vars['topic_title'])),"forums_threads.IdTitle",$rBefore->threadid, $rBefore->IdTitle, $rBefore->IdWriter) ;
		// case the update concerns the reference language of the threads
		 	if ($rBefore->thread_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	   $query="update forums_threads set IdGroup=".$vars['IdGroup'].",title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forums_threads.id=".$rBefore->threadid ;
        	   $s=$this->dao->query($query);
		   }
		}

        // subscription if any, could be done out of transaction, this is not so important
        if ((isset($vars['NotifyMe'])) and ($vars['NotifyMe']=="on")) {
			if (!$this->IsThreadSubscribed($rBefore->threadid,$_SESSION["IdMember"])) {
                 $this->SubscribeThread($rBefore->threadid,$_SESSION["IdMember"]) ;
			}
        }
        else {
			$vars['NotifyMe']="Not Asked" ;
			if ($this->IsThreadSubscribed($rBefore->threadid,$_SESSION["IdMember"])) {
                $this->UnsubscribeThreadDirect($rBefore->threadid,$_SESSION["IdMember"]) ;
			}
        }
    
        $this->prepare_notification($this->messageId,"useredit") ; // Prepare a notification
        MOD_log::get()->write("Editing Post=#".$this->messageId." Text Before=<i>".addslashes($rBefore->message)."</i> <br /> NotifyMe=[".$vars['NotifyMe']."]", "Forum");
    } // editPost

    private function subtractTagCounter($threadid) {
        // in fact now this function does a full update of counters for tags of this thread
    
        $query=" UPDATE `forums_tags` SET `counter` = (select count(*) from `tags_threads` where `forums_tags`.`id`=`tags_threads`.`IdTag`)" ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Failed for subtractTagCounter!');
        }
    } // end of subtractTagCounter
    
/**
*	editTopic write the data in of change thread in the database
*	warning : dont start any transaction in it since there is already one
*  started by the caller
* this also write a log
*/

    private function editTopic($vars, $threadid)     {
        $this->subtractTagCounter($threadid);
        
		if(empty($vars['d_continent'])) {
			$d_continent='NULL' ;
		}
		else {
			$d_continent=$vars['d_continent'] ;
			if ($d_continent=='none') {
				$d_continent='NULL' ;
			}
		}
		
		if(empty($vars['d_country'])) {
			$d_country='NULL' ;
		}
		else {
			$d_country=$vars['d_country'] ; 
			if ($d_country=='none') {
				$d_country='NULL' ;
			}
		}

		if(empty($vars['d_admin'])) {
			$d_admin='NULL' ;
		}
		else {
			$d_admin=$vars['d_admin'] ;
			if ($d_admin=='none') {
				$d_admin='NULL' ;
			}
		}

		if(empty($vars['d_geoname'])) {
			$d_geoname='NULL' ;
		}
		else {
			$d_geoname=$vars['d_geoname'] ; 
			if ($d_geoname=='none') {
				$d_geoname='NULL' ;
			}
		}


        $query = sprintf("
UPDATE `forums_threads` 
SET `title` = '%s',`geonameid` = %s, `admincode` = %s, `countrycode` = %s, `continent` = %s
WHERE `threadid` = '%d' ", 
            $this->dao->escape(strip_tags($vars['topic_title'])), 
            "'".$d_geoname."'" ,
            "'".$d_admin."'" ,
            "'".$d_country."'" ,
            "'".$d_continent."'" ,
            $threadid
        );
            
        $this->dao->query($query);
		 
        $s=$this->dao->query("select IdWriter,forums_threads.id as IdThread,forums_threads.IdTitle,forums_threads.IdFirstLanguageUsed as thread_IdFirstLanguageUsed 
		from forums_threads,forums_posts 
		where forums_threads.first_postid=forums_posts.id and forums_threads.id=".$threadid);
        if (!$s) {
            throw new PException('editTopic:: previous info for firtst post in the thread!');
        }
        $rBefore = $s->fetch(PDB::FETCH_OBJ);
		 
		 $this->ReplaceInFTrad($this->dao->escape(strip_tags($vars['topic_title'])),"forums_threads.IdTitle",$rBefore->IdThread, $rBefore->IdTitle, $rBefore->IdWriter) ;

		 // case the update concerns the reference language of the posts
		if ($rBefore->thread_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	$query="update forums_threads set title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forums_threads.id=".$rBefore->IdThread ;
        	$s=$this->dao->query($query);
		}
		 
         // Set ThreadVisibility
        $query = 'UPDATE forums_threads SET ThreadVisibility = "' . $vars['ThreadVisibility'] . '" WHERE forums_threads.id=' . $rBefore->IdThread;
        $s =$this->dao->query($query);

// Edit topic must not allow for tags edit
// or if if does, this iss something very uneasy to manage ;-)
//        $this->updateTags($vars, $threadid);
        MOD_log::get()->write("Editing Topic Thread=#".$threadid, "Forum");
    } // end of editTopic
    
    public function replyProcess() {
        if (!($User = APP_User::login())) {
            return false;
        }
        
        $vars =& PPostHandler::getVars();

	     $this->checkVarsReply($vars);
        $this->replyTopic($vars);

        PPostHandler::clearVars();
        return PVars::getObj('env')->baseuri.$this->forums_uri.'s'.$this->threadid;
    } // end of replyProcess
    
    public function reportpostProcess() {
        if (!($User = APP_User::login())) {
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
		if (isset($vars['Type'])) $Type=$vars['Type'] ;
		if (!empty($vars['IdReporter'])) {
			$IdReporter=$vars['IdReporter'] ;
		}
		else {
			$IdReporter=$_SESSION["IdMember"] ;
		}

        $ss = "select reports_to_moderators.* from reports_to_moderators where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
        $s = $this->dao->query($ss);
		$OldReport = $s->fetch(PDB::FETCH_OBJ) ;
		

		$UsernameAddTime='at '.date("d-m-Y").' '.date("H:i").'(server time) <a href="'.$_SESSION["Username"].'">'.$_SESSION["Username"].'</a> wrote:<br/>' ;
		if (($this->BW_Right->HasRight("ForumModerator")) and (isset($OldReport->IdReporter))) {
			$PostComment=$UsernameAddTime.$this->cleanupText($vars['PostComment']) ;
			if (isset($OldReport->PostComment)) $PostComment=$PostComment."<hr />\n".$OldReport->PostComment ;
			$ss="update reports_to_moderators set  LastWhoSpoke='Moderator',PostComment='".$this->dao->escape($PostComment)."',IdModerator=".$_SESSION["IdMember"].",Status='".$this->dao->escape($Status)."',Type='".$this->dao->escape($Type)."',IdModerator=".$_SESSION['IdMember']." where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
			$this->dao->query($ss);
			$this->MailTheReport($IdPost,$IdReporter,$PostComment,0,$Status,1) ;

		}
		else {
			if ($IdReporter!=$_SESSION["IdMember"]) {
			    MOD_log::get()->write("Trying to trick report to moderator for post #".$IdPost,"Forum") ; 				
				die("Failed to report to moderator") ;
			}
			if (isset($OldReport->IdReporter)) {
				$PostComment=$UsernameAddTime.$this->cleanupText($vars['PostComment'])."<hr />\n".$OldReport->PostComment ;
				$ss="update reports_to_moderators set LastWhoSpoke='Member',PostComment='".$this->dao->escape($PostComment)."',Status='".$this->dao->escape($Status)."'"." where IdPost=".$IdPost." and IdReporter=".$IdReporter ;
				$this->dao->query($ss);
				$this->MailTheReport($IdPost,$OldReport->IdReporter,$PostComment,$OldReport->IdModerator,$Status,0) ;
			}
			else {
				$PostComment=$UsernameAddTime.$this->cleanupText($vars['PostComment']) ;
				$ss="insert into reports_to_moderators(PostComment,created,IdPost,IdReporter,Status) " ;
				$ss=$ss." values('".$this->dao->escape($PostComment)."',now(),".$IdPost.",".$_SESSION["IdMember"].",'".$Status."')" ;
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
    public function prepareModeratorEditPost($IdPost) {
	 	$DataPost->IdPost=$IdPost ;
		$DataPost->Error="" ; // This will receive the error sentence if any
        $query = "select forums_posts.*,members.Status as memberstatus,members.UserName as UserNamePoster from forums_posts,members where forums_posts.id=".$IdPost." and IdWriter=members.id" ;
        $s = $this->dao->query($query);
		$DataPost->Post = $s->fetch(PDB::FETCH_OBJ) ;

		if (!isset($DataPost->Post)) {
		 	$DataPost->Error="No Post for Post=#".$IdPost ;
			return($DataPost) ;
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
		
// retrieve all tags connected to this thread
        $query = "select forums_tags.*,tags_threads.IdTag as IdTag  from forums_tags,tags_threads where tags_threads.IdTag=forums_tags.id and IdThread=".$DataPost->Thread->id ;
        $s = $this->dao->query($query);
		 $DataPost->Tags=array() ;
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   $DataPost->Tags[]=$row ;
		 }
		 
// retrieve alltag NOT connected to this thread (will be use to select the proposed tag to add)
         $query = "SELECT DISTINCT forums_tags.id AS IdTag, forums_tags.IdName AS IdName,forums_tags.counter  as cnt
				FROM forums_tags
				RIGHT JOIN tags_threads ON ( tags_threads.IdTag != forums_tags.id ) WHERE IdThread = ".$DataPost->Thread->id." order by cnt desc" ;
        $s = $this->dao->query($query);
		 $DataPost->AllNoneTags=array() ;
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   $DataPost->AllNoneTags[]=$row ;
		 }
		 // no tags means query above won't work
		 if (empty($DataPost->AllNoneTags)) {
		     $query = "SELECT forums_tags.id AS IdTag, forums_tags.IdName AS IdName,forums_tags.counter  as cnt
						FROM forums_tags ORDER BY cnt DESC" ;
		     $s = $this->dao->query($query);
		     $DataPost->AllNoneTags=array() ;
		     while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		         $DataPost->AllNoneTags[]=$row ;
		     }
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
     if (!($User = APP_User::login())) {
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
			$sql="update forums_threads set IdGroup=".$IdGroup.",stickyvalue=".$stickyvalue.",expiredate=".$expiredate.",ThreadVisibility='".$ThreadVisibility."',ThreadDeleted='".$ThreadDeleted."',WhoCanReply='".$WhoCanReply."' where id=".$IdThread ;
			
//			die ($sql) ;
			$this->dao->query($sql);
		 }

		 if (isset($vars["submit"]) and ($vars["submit"]=="add translated title")) { // if a new translation is to be added for a title
		 	$IdThread=(int)$vars["IdThread"] ;
			$qry=$this->dao->query("select * from forum_trads where IdTrad=".$vars["IdTrad"]." and IdLanguage=".$vars["IdLanguage"]);
			$rr=$qry->fetch(PDB::FETCH_OBJ) ;
			if (empty($rr->id)) { // Only proceed if no such a title exists
		 		$ss=$vars["NewTranslatedTitle"]  ;
				$this->InsertInFTrad($ss,"forums_threads.IdTitle",$IdThread, $_SESSION["IdMember"], $vars["IdLanguage"],$vars["IdTrad"]) ;
				MOD_log::get()->write("Updating Thread=#".$IdThread." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
			} 
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		 if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
		 	$OwnerCanStillEdit="'".$vars["OwnerCanStillEdit"]."'"  ;
		 	$HasVotes="'".$vars["HasVotes"]."'"  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."] HasVotes=[".$HasVotes."]","ForumModerator");
			$this->dao->query("update forums_posts set OwnerCanStillEdit=".$OwnerCanStillEdit.",HasVotes=".$HasVotes." where id=".$IdPost);
		 }

		 if (isset($vars["submit"]) and ($vars["submit"]=="add translated post")) { // if a new translation is to be added for a title
		 		$IdPost=(int)$vars["IdPost"] ;
				$qry=$this->dao->query("select * from forum_trads where IdTrad=".$vars["IdTrad"]." and IdLanguage=".$vars["IdLanguage"]);
				$rr=$qry->fetch(PDB::FETCH_OBJ) ;
				if (empty($rr->id)) { // Only proceed if no such a post exists
		 			$ss=$this->dao->escape($vars["NewTranslatedPost"])  ;
					$this->InsertInFTrad($ss,"forums_posts.IdContent",$IdPost, $_SESSION["IdMember"], $vars["IdLanguage"],$vars["IdTrad"]) ;
       		MOD_log::get()->write("Updating Post=#".$IdPost." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
				} 
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
		 	$OwnerCanStillEdit=$vars["OwnerCanStillEdit"]  ;
		 	$PostVisibility=$vars["PostVisibility"]  ;
		 	$PostDeleted=$vars["PostDeleted"]  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."] PostVisibility=[".$PostVisibility."] PostDeleted=[".$PostDeleted."] ","ForumModerator");
			$this->dao->query("update forums_posts set OwnerCanStillEdit='".$OwnerCanStillEdit."',PostVisibility='".$PostVisibility."',PostDeleted='".$PostDeleted."' where id=".$IdPost);
		}

		if (isset($vars["submit"]) and ($vars["submit"]=="delete Tag")) { // if an effective update was chosen for a forum trads
		 	 $IdTag=(int)$vars["IdTag"] ;
		 	 $IdThread=(int)$vars["IdThread"] ;
       MOD_log::get()->write("Updating thread=#".$IdThread." removing tag =[".$IdTag."]","ForumModerator");
       $this->dao->query("delete from tags_threads where IdThread=".$IdThread." and  IdTag=".$IdTag);
				$this->dao->query("UPDATE `forums_tags` SET `counter` = ".
			"(select count(*) from `tags_threads` where `IdTag`=".$IdTag.") where `id`=".$IdTag) ; // update counters			
		 }


		 if (isset($vars["submit"]) and ($vars["submit"]=="Add Tag") and !(empty($vars["IdTag"]))) { // if an effective update was chosen for a forum trads
		 	 $IdTag=(int)$vars["IdTag"] ;
		 	 $IdThread=(int)$vars["IdThread"] ;
       MOD_log::get()->write("Updating Thread=#".$IdThread." adding tag =[".$IdTag."]","ForumModerator");
			 $sql="replace into tags_threads(IdTag,IdThread) values (".$IdTag.",".$IdThread.")" ;
//			 echo $sql ;
       $this->dao->query($sql);
				$this->dao->query("UPDATE `forums_tags` SET `counter` = ".
			"(select count(*) from `tags_threads` where `IdTag`=".$IdTag.") where `id`=".$IdTag) ; // update counters			
		 }


 		if (isset($vars["IdForumTrads"])) { // if an effective update was chosen for a forum trads
		 			$this->DofTradUpdate($vars["IdForumTrads"],$vars["Sentence"],$vars["IdLanguage"]) ; // update the corresponding translations
		 }
			 
     PPostHandler::clearVars();
		 
     return PVars::getObj('env')->baseuri.$this->forums_uri.'modfulleditpost/'.$IdPost;
 		} // end of ModeratorEditPostProcess
    
/*
* ModeratorEditTagProcess deals with the tabs updated by moderators
*/
    public function ModeratorEditTagProcess() {
        if (!($User = APP_User::login())) {
            return false;
        }
				
        $vars =& PPostHandler::getVars();
		 if ($vars["submit"]=="replace tag") { // if an effective update was chosen for a forum trads
		 	$IdTag=$vars["IdTag"] ;
		 	$IdTagToReplace=$vars["IdTagToReplace"] ;
			// first save the list of the thread where the tag is going to be replacec for the logs
        	$s=$this->dao->query("select IdThread from tags_threads where IdTag=".$IdTagToReplace) ;
			$strlogs="" ;
        	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			  if ($strlogs=="") {
			  	 $strlogs="(".$row->IdThread ;
			  }
			  else {
			  	 $strlogs=$strlogs.",".$row->IdThread ;
			  }
			}
		  	$strlogs.=")" ;
        	MOD_log::get()->write("Replacing tag IdTag=#".$IdTagToReplace." with tag IdTag=#".$IdTag." for thread ".$strlogs,"ForumModerator");
			$s=$this->dao->query("select * from tags_threads where IdTag=".$IdTagToReplace) ; // replace the tags
			while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$s2=$this->dao->query("select * from tags_threads where IdTag=".$IdTag." and IdThread=".$row->IdThread) ; // replace the tags
				$row2 = $s2->fetch(PDB::FETCH_OBJ) ;
				if (isset($row2->IdTad)) continue ; // Don't try to recreate an allready associated tag
				$this->dao->query("update tags_threads set IdTag=".$IdTag." where IdTag=".$row->IdTag." and IdThread=".$row->IdThread) ; // replace the tags
				
			}
			$this->dao->query("delete from tags_threads where IdTag=".$IdTagToReplace) ; // delete the one who are still here after replace
			$this->dao->query("delete from forums_tags where id=".$IdTagToReplace) ; // delete the tag
			$this->dao->query("UPDATE `forums_tags` SET `counter` = ".
			"(select count(*) from `tags_threads` where `forums_tags`.`id`=`tags_threads`.`IdTag`)") ; // update counters			
		 }
		 elseif (isset($vars["IdForumTradsTag"]) and ($vars["submit"]=="update")) { // if an effective update was chosen for a forum trads
		 	$this->DofTradUpdate($vars["IdForumTradsTag"],$vars["SentenceTag"],$vars["IdLanguage"]) ; // update the corresponding translations
		 }
		 elseif (isset($vars["IdForumTradsDescription"]) and ($vars["submit"]=="update")) { // if an effective update was chosen for a forum trads
		 	$this->DofTradUpdate($vars["IdForumTradsDescription"],$vars["SentenceDescription"],$vars["IdLanguage"]) ; // update the corresponding translations
		 }
		 elseif ($vars["submit"]=="delete") { // if an effective update was chosen for a forum trads
		 	if (isset($vars["IdForumTradsTag"])) {
        	   MOD_log::get()->write("Deleting forum_trads=#".$vars["IdForumTradsTag"]." for tag IdTag=#".$vars["IdTag"].
						 " Name=[".$vars["SentenceTag"]."]", "ForumModerator");
        	   $this->dao->query("delete from forum_trads where id=".(int)$vars["IdForumTradsTag"]);
			}
		 	if (isset($vars["IdForumTradsDescription"])) {
        	   MOD_log::get()->write("Deleting forum_trads=#".$vars["IdForumTradsDescription"]." for Tag IdTag=#".$vars["IdTag"].
						 " Description=[".$vars["SentenceDescription"]."]", "ForumModerator");
        	   $this->dao->query("delete from forum_trads where id=".(int)$vars["IdForumTradsDescription"]);
			}
		 }
		 elseif (isset($vars["submit"]) and ($vars["submit"]=="add translation")) {
		 	$SaveIdLanguage=$_SESSION["IdLanguage"] ; // Nasty trick because ReplaceInFTrad will use $_SESSION["IdLanguage"] as a global var
			$_SESSION["IdLanguage"]=$vars["NewIdLanguage"] ;
        	MOD_log::get()->write("Adding a translation for Tag IdTag=#".$vars["IdTag"].
					" [".$vars["SentenceTag"]."] <br />Desc [<i>".$vars["SentenceDescription"].
					"</i>]<br /> in Lang :".$vars["NewIdLanguage"], "ForumModerator");
		 	if (!empty($vars["SentenceTag"])) {
			   $this->ReplaceInFTrad(addslashes($vars["SentenceTag"]),"forums_tags.IdName",$vars["IdTag"],$vars["IdName"])  ;
			} 
		 	if (!empty($vars["SentenceDescription"])) {
			   $this->ReplaceInFTrad(addslashes($vars["SentenceDescription"]),"forums_tags.IdDescription",$vars["IdTag"],$vars["IdDescription"]) ;
			} 
			$_SESSION["IdLanguage"]=$SaveIdLanguage ; // restore the NastyTrick
		 }
	     $IdTag=$vars['IdTag'] ;
        PPostHandler::clearVars();
		 
        return PVars::getObj('env')->baseuri.$this->forums_uri.'modedittag/'.$IdTag;
    } // end of ModeratorEditTagProcess
    
    public function delProcess() {
        if (!($User = APP_User::login())) {
            return false;
        }
        
        if ($this->BW_Right->HasRight("ForumModerator","Delete")) {
            $this->dao->query("START TRANSACTION");
            $query = sprintf(
                "
SELECT
    `forums_posts`.`threadid`,
    `HasVotes`,
    `IdLocalEvent`,
    `forums_threads`.`first_postid`,
    `forums_threads`.`last_postid`,
    `forums_threads`.`expiredate`,
    `forums_threads`.`stickyvalue`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
WHERE `forums_posts`.`postid` = '%d'
                ",
                $this->messageId
            );
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could not retrieve Threadinfo!');
            }
            $topicinfo = $s->fetch(PDB::FETCH_OBJ);
            
            if ($topicinfo->first_postid == $this->messageId) { // Delete the complete topic
                $this->subtractTagCounter($topicinfo->threadid);
                
                $query =
                    "
UPDATE `forums_threads`
SET `first_postid` = NULL, `last_postid` = NULL
WHERE `threadid` = '$topicinfo->threadid'
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
WHERE `threadid` = '$topicinfo->threadid'
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
WHERE `threadid` = '$topicinfo->threadid'
                        "
                    ;
                    $this->dao->query($query);
                }
                MOD_log::get()->write("deleting single post where Post=#". $this->messageId, "Forum");
                
                $this->prepare_notification($this->messageId,"deletepost") ; // Prepare a notification (before the delete !)

                $query =
                    "
DELETE FROM `forums_posts`
WHERE `postid` = '$this->messageId'
                    "
                ;
                $this->dao->query($query);

                if ($topicinfo->last_postid == $this->messageId) {
                    $query =
                        "
SELECT `postid` 
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
WHERE `threadid` = '$topicinfo->threadid'
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
    
    private function checkVarsTopic(&$vars) {
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
        if (!($User = APP_User::login())) {
            throw new PException('User gone missing...');
        }

        $this->dao->query("START TRANSACTION");
        
        $query = sprintf(
            "
INSERT INTO `forums_posts` (`authorid`, `threadid`, `create_time`, `message`,`IdWriter`,`IdFirstLanguageUsed`,`PostVisibility`)
VALUES ('%d', '%d', NOW(), '%s','%d',%d,'%s')
            ",
            $User->getId(),
            $this->threadid,
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            $_SESSION["IdMember"],$this->GetLanguageChoosen(),$vars['PostVisibility']
        );

        $result = $this->dao->query($query);
		 
        
        $postid = $result->insertId();
		 
// todo one day, remove this line (aim to manage the redudancy with the new id)
		 $query="update `forums_posts` set `id`=`postid` where id=0" ;		 
        $result = $this->dao->query($query);

		 // Now create the text in forum_trads		 
 		 $this->InsertInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])),"forums_posts.IdContent",$postid) ;
        
        $query =
            "
UPDATE `forums_threads`
SET `last_postid` = '$postid', `replies` = `replies` + 1
WHERE `threadid` = '$this->threadid'
            "
        ;
        $this->dao->query($query);
        
        $this->dao->query("COMMIT");
        

        // subscription if any is out of transaction, this is not so important
        if ((isset($vars['NotifyMe'])) and ($vars['NotifyMe']=="on")) {
           if (!$this->IsThreadSubscribed($this->threadid,$_SESSION["IdMember"])) {
                 $this->SubscribeThread($this->threadid,$_SESSION["IdMember"]) ;
           }
        }
        else {
           $vars['NotifyMe']="Not Asked" ;
           if ($this->IsThreadSubscribed($this->threadid,$_SESSION["IdMember"])) {
                 $this->UnsubscribeThreadDirect($this->threadid,$_SESSION["IdMember"]) ;
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
    private function newTopic(&$vars) {
        if (!($User = APP_User::login())) {
            throw new PException('User gone missing...');
        }
        $IdGroup=0 ;
		$ThreadVisibility=$vars['ThreadVisibility'] ;
		if (isset($vars['IdGroup'])) {
			$IdGroup=$vars['IdGroup'] ;
			if (!empty($IdGroup)) {
				$ss="select * from groups where id=".intval($IdGroup);
                $s = $this->dao->query($ss);
                $rGroup = $s->fetch(PDB::FETCH_OBJ);
				if ($vars['ThreadVisibility']=='Default') {
					if ($rGroup->VisiblePosts=='no') {
						$ThreadVisibility='GroupOnly' ;
					}
				}
			}
		}
		if ($ThreadVisibility=='Default') {
			$ThreadVisibility='NoRestriction' ;
		}
				
        $this->dao->query("START TRANSACTION");
        
        $query = sprintf(
            "
INSERT INTO `forums_posts` (`authorid`, `create_time`, `message`,`IdWriter`,`IdFirstLanguageUsed`,`PostVisibility`)
VALUES ('%d', NOW(), '%s','%d',%d,'%s')
            ",
            $User->getId(),
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            $_SESSION["IdMember"],$this->GetLanguageChoosen(),$ThreadVisibility
        );
        $result = $this->dao->query($query);
        
        $postid = $result->insertId();
		
		if(empty($vars['d_continent'])) {
			$d_continent='NULL' ;
		}
		else {
			$d_continent=$vars['d_continent'] ;
			if ($d_continent=='none') {
				$d_continent='NULL' ;
			}
		}
		
		if(empty($vars['d_country'])) {
			$d_country='NULL' ;
		}
		else {
			$d_country=$vars['d_country'] ; 
			if ($d_country=='none') {
				$d_country='NULL' ;
			}
			else {
				$d_country="'".$d_country."'" ;
			}
		}

		if(empty($vars['d_admin'])) {
			$d_admin='NULL' ;
		}
		else {
			$d_admin=$vars['d_admin'] ;
			if ($d_admin=='none') {
				$d_admin='NULL' ;
			}
			else {
				$d_admin="'".$d_admin."'" ;
			}
		}

		if(empty($vars['d_geoname'])) {
			$d_geoname='NULL' ;
		}
		else {
			$d_geoname=$vars['d_geoname'] ; 
			if ($d_geoname=='none') {
				$d_geoname='NULL' ;
			}
		}

		
		// todo one day, remove this line (aim to manage the redudancy with the new id)
		$query="update `forums_posts` set `id`=`postid` where id=0" ;		 
        $result = $this->dao->query($query);

 		$this->InsertInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])),"forums_posts.IdContent",$postid) ;
        
        $query = sprintf(
            "
INSERT INTO `forums_threads` (`title`, `first_postid`, `last_postid`, `geonameid`, `admincode`, `countrycode`, `continent`,`IdFirstLanguageUsed`,`IdGroup`,`ThreadVisibility`)
VALUES ('%s', '%d', '%d', %s, %s, %s, %s,%d,%d,'%s')
            ",
            $this->dao->escape(strip_tags($vars['topic_title'])),
            $postid,
            $postid, 
            "'".$d_geoname."'",
            $d_admin,
            $d_country,
            "'".$d_continent."'",$this->GetLanguageChoosen(),$IdGroup,$ThreadVisibility 
        );
        $result = $this->dao->query($query);
        
        $threadid = $result->insertId();

// todo one day, remove this line (aim to manage the redudancy with the new id)
		$query="update `forums_threads` set `id`=`threadid` where id=0" ;		 
        $result = $this->dao->query($query);

		$ss=$this->dao->escape(strip_tags(($vars['topic_title']))) ;
 		$this->InsertInFTrad($ss,"forums_threads.IdTitle",$threadid) ;
        
        $query = sprintf("UPDATE `forums_posts` SET `threadid` = '%d' WHERE `postid` = '%d'", $threadid, $postid);
        $result = $this->dao->query($query);
        
         // Create the tags
        $this->updateTags($vars, $threadid);
        
        $this->dao->query("COMMIT");


        // subscription if any is out of transaction, this is not so important

        if ((isset($vars['NotifyMe'])) and ($vars['NotifyMe']=="on")) {
                 $this->SubscribeThread($threadid,$_SESSION["IdMember"]) ;
        }
        else {
             $vars['NotifyMe']="Not Asked" ;
        }

        $this->prepare_notification($postid,"newthread") ; // Prepare a notification 
        MOD_log::get()->write("New Thread new Tread=#".$threadid." Post=#". $postid." IdGroup=#".$IdGroup." NotifyMe=[".$vars['NotifyMe']."] initial Visibility=".$vars['ThreadVisibility'], "Forum");
        
        return $threadid;
    } // end of NewTopic
    
/*
* updateTags function is called by newtopic or by editpost and allows to add or update tags for a given threadid
*/
    private function updateTags($vars, $threadid) {
		 // Try to find a default language
		 $IdLanguage=0 ;
   	 if (isset($_SESSION['IdLanguage'])) {
	   	 	$IdLanguage=$_SESSION['IdLanguage'] ;
		 }
		 if (isset($_POST['IdLanguage'])) { // This will allow to consider a Language specified in the form
	   	 	$IdLanguage=$_POST['IdLanguage'] ;
	 	 }

		 
        if (isset($vars['tags']) && $vars['tags']) {
            $tags = explode(',', $vars['tags']);
            /** 
            $tags = explode(' ', $vars['tags']);
            separator should better be a blank space, but help text must be changed accordingly
            **/
            $ii = 1;
            foreach ($tags as $tag) {
                if ($ii > 15) { // 15 is this a reasonable limit ?
                    break;
                }
                
                $tag = trim(strip_tags($tag));
                $tag = $this->dao->escape($tag);

				 

                // Check if it already exists in our Database
                $query = "SELECT `tagid` FROM `forums_tags` WHERE `forums_tags`.`tag` = '$tag' ";
                $s = $this->dao->query($query);
                $taginfo = $s->fetch(PDB::FETCH_OBJ);
								$IdNameUpdate="" ;
                if (!empty($taginfo->tagid)) {
                    $tagid = $taginfo->tagid;
                } else {
                    // Insert it
                    $query = "INSERT INTO `forums_tags` (`tag`) VALUES ('$tag')  ";
                    $result = $this->dao->query($query);
                    $tagid = $result->insertId();
 		 			 					$IdName=$this->InsertInFTrad($tag,"forums_tags.IdName",$tagid) ;
								    $IdNameUpdate=",IdName=".$IdName ;
					 
// todo one day, remove this line (aim to manage the redudancy with the new id)
		 $query="update `forums_tags` set `id`=`tagid` where id=0" ;		 
        $result = $this->dao->query($query);
        		   	 MOD_log::get()->write("Inserting new tag [<b>".$tag."</b>] in IdLanguage[".$IdLanguage."] IdTag=#".$tagid, "ForumTag");

                }
                if ($tagid) {
                    $query = "UPDATE `forums_tags` SET `counter` = `counter` + 1".$IdNameUpdate." WHERE `tagid` = '$tagid' ";
                    $this->dao->query($query);
//                    $query = "UPDATE `forums_threads` SET `tag$ii` = '$tagid' WHERE `threadid` = '$threadid'"; // todo this tag1, tag2 ... thing is going to become obsolete
//                    $this->dao->query($query);
                    $query ="replace INTO `tags_threads` (`IdTag`,`IdThread`) VALUES($tagid, $threadid) ";
                    $this->dao->query($query);
                    
                    $ii++;
                }
            }
        }
    } // end of updateTags
     
    private $topic;
/**
* function prepareTopic prepares the detail of a topic for display according to threadid
* if @$WithDetail is set to true, additional details (available languages and original author are displayed)
 
*/	 
    public function prepareTopic($WithDetail=false) {
        $this->topic = new Topic();
		 
        $this->topic->WithDetail = $WithDetail;
		 
        // Topic Data
        $query = "SELECT
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
    `forums_threads`.`continent`,
    `forums_threads`.`IdGroup`,
    `forums_threads`.`geonameid`, `geonames_cache`.`name` AS `geonames_name`,
    `forums_threads`.`admincode`, `geonames_admincodes`.`name` AS `adminname`,
    `forums_threads`.`countrycode`, `geonames_countries`.`name` AS `countryname`,
	 `groups`.`Name` AS `GroupName`
FROM `forums_threads`
LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)
LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)
LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)
LEFT JOIN `groups` ON (`forums_threads`.`IdGroup` = `groups`.`id`)
WHERE `threadid` = '$this->threadid' 
and ($this->PublicThreadVisibility)
and ($this->ThreadGroupsRestriction)
"
        ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Thread=#".$this->threadid." !');
        }
		
        $topicinfo = $s->fetch(PDB::FETCH_OBJ);

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
				
//				echo "\$topicinfo->IdGroup=",$topicinfo->IdGroup ;
        
        // Now fetch the tags associated with this thread
        $topicinfo->NbTags=0 ;
		if (!isset($topicinfo->IdThread)) {
			$query2="SELECT IdTag,IdName from tags_threads,forums_tags ".
							  "WHERE IdThread=-1 and forums_tags.id=tags_threads.IdTag"; // This will return nothing
			require SCRIPT_BASE.'build/members/pages/mustlogin.page.php';  
			$topicinfo->title="Please log in to see the thread" ;
			$topicinfo->replies=0 ;

		}
		else {
			$query2="SELECT IdTag,IdName from tags_threads,forums_tags ".
							  "WHERE IdThread=".$topicinfo->IdThread." and forums_tags.id=tags_threads.IdTag";
		}
//								die("query2=".$query2) ;
        $s2 = $this->dao->query($query2);
        if (!$s2) {
           throw new PException('Could not retrieve IdTags for Threads!');
        }
        while ($row2 = $s2->fetch(PDB::FETCH_OBJ)) {
            //        echo $row2->IdTag," " ;
            $topicinfo->IdTag[]=$row2->IdTag ;
            $topicinfo->IdName[]=$row2->IdName ;
            $topicinfo->NbTags++ ;
        }
        
        $this->topic->topicinfo = $topicinfo;
        $this->topic->IdThread=$this->threadid ;

        
        $from = $this->POSTS_PER_PAGE * ($this->getPage() - 1);
        
        $query = sprintf("
SELECT
    postid,
    forums_posts.id as IdPost,
    UNIX_TIMESTAMP(create_time) AS posttime,
    message,
    IdContent,
    IdWriter,
    geonames_cache.fk_countrycode,
    geonames_cache.name AS city,
    geonames_countries.name AS country,
    forums_posts.threadid,
    OwnerCanStillEdit,
    members.Username as OwnerUsername,
    HasVotes,
    PostVisibility,
    PostDeleted,
    IdLocalEvent,
    forums_threads.IdGroup
FROM
    forums_threads,
    forums_posts
LEFT
    JOIN members ON forums_posts.IdWriter = members.id
LEFT JOIN
    addresses AS a ON a.IdMember = members.id AND a.rank = 0
LEFT JOIN
    geonames_cache ON a.IdCity = geonames_cache.geonameid
LEFT JOIN
    geonames_countries ON geonames_cache.fk_countrycode = geonames_countries.iso_alpha2
WHERE
    forums_posts.threadid = '%d'
    AND forums_posts.threadid=forums_threads.id
    AND ({$this->PublicPostVisibility})
    AND ({$this->ThreadGroupsRestriction})
ORDER BY
    posttime ASC
LIMIT %d, %d",$this->threadid,$from,$this->POSTS_PER_PAGE);

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
				if ($row->HasVotes=="Yes") { // Id this post is connected to some opinion
					$row->Vote=$this->GetPostVote($row->IdPost) ;
//					print_r($row->Vote) ; die("0") ;
				}
			}
			$this->topic->posts[] = $row;        
        } // end  // Now retrieve all the Posts of this thread
        
        
        // Check if the current user has subscribe to this thread or not (to display the proper option, subscribe or unsubscribe)
        if (isset($_SESSION["IdMember"])) {
            $query = sprintf( "
SELECT
    `members_threads_subscribed`.`id` AS IdSubscribe,
    `members_threads_subscribed`.`UnSubscribeKey` AS IdKey 
FROM members_threads_subscribed
WHERE IdThread=%d
AND IdSubscriber=%d
                ",
                $this->threadid,
                $_SESSION["IdMember"]
            );
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could if has subscribed to Thread=#".$this->threadid." !');
            }
            $row = $s->fetch(PDB::FETCH_OBJ) ;
            if (isset($row->IdSubscribe)) {
                $this->topic->IdSubscribe= $row->IdSubscribe ;
                $this->topic->IdKey= $row->IdKey ;
            }
        }
        
/*
        $query = sprintf(  "
SELECT
    `forums_threads`.`title`,
    `forums_threads`.`IdTitle`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forums_threads`.`replies`,
    `forums_threads`.`views`,
    `forums_threads`.`first_postid`,
    `forums_threads`.`IdGroup`,
    `forums_threads`.`continent`,
    `forums_threads`.`geonameid`, `geonames_cache`.`name` AS `geonames_name`,
    `forums_threads`.`admincode`, `geonames_admincodes`.`name` AS `adminname`,
    `forums_threads`.`countrycode`, `geonames_countries`.`name` AS `countryname`,
    `forums_threads`.`tag1` AS `tag1id`, `tags1`.`tag` AS `tag1`,
    `forums_threads`.`tag2` AS `tag2id`, `tags2`.`tag` AS `tag2`,
    `forums_threads`.`tag3` AS `tag3id`, `tags3`.`tag` AS `tag3`,
    `forums_threads`.`tag4` AS `tag4id`, `tags4`.`tag` AS `tag4`,
    `forums_threads`.`tag5` AS `tag5id`, `tags5`.`tag` AS `tag5`,
    `groups`.`Name` AS `GroupName`
FROM `forums_threads`
LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)
LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)
LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)
LEFT JOIN `forums_tags` AS `tags1` ON (`forums_threads`.`tag1` = `tags1`.`tagid`)
LEFT JOIN `forums_tags` AS `tags2` ON (`forums_threads`.`tag2` = `tags2`.`tagid`)
LEFT JOIN `forums_tags` AS `tags3` ON (`forums_threads`.`tag3` = `tags3`.`tagid`)
LEFT JOIN `forums_tags` AS `tags4` ON (`forums_threads`.`tag4` = `tags4`.`tagid`)
LEFT JOIN `forums_tags` AS `tags5` ON (`forums_threads`.`tag5` = `tags5`.`tagid`)
LEFT JOIN `groups` ON (`forums_threads`.`IdGroup` = `groups`.`id`)
WHERE `threadid` = '%d'
            ",
            $this->threadid
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Thread=#".$this->threadid." !');
        }
*/
        // Increase the number of views
        $query = "
UPDATE `forums_threads`
SET `views` = (`views` + 1)
WHERE `threadid` = '$this->threadid' LIMIT 1
            "     ;
        $this->dao->query($query);
        
    } // end of prepareTopic
    
    public function initLastPosts() {
        $query = sprintf("
SELECT
    `postid`,
	`postid` as IdPost,
    UNIX_TIMESTAMP(`create_time`) AS `posttime`,
    `message`,
	`IdContent`,
    `members`.`Username` AS `OwnerUsername`,
    `IdWriter`,
	forums_threads.`threadid`,
    `PostVisibility`,
    `PostDeleted`,
    `ThreadDeleted`,
	`OwnerCanStillEdit`,
    `geonames_cache`.`fk_countrycode`,
    `HasVotes`,
    `IdLocalEvent`,
    `IdGroup`
FROM forums_posts, forums_threads, members, addresses
LEFT JOIN `geonames_cache` ON (addresses.IdCity = `geonames_cache`.`geonameid`)
WHERE `forums_posts`.`threadid` = '%d' AND `forums_posts`.`IdWriter` = `members`.`id`
AND addresses.IdMember = members.id AND addresses.rank = 0
 AND `forums_posts`.`threadid`=`forums_threads`.`id`
	and ({$this->PublicPostVisibility})
	and ({$this->PublicThreadVisibility})
	and ({$this->PostGroupsRestriction})
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
    
    /**
     * This function retrieve the subscriptions for the member $cid and/or the the thread IdThread and/or theIdTag
     * @$cid : either the IdMember or the username of the member we are searching the subscription
     * this $cid and $IdThread and $IdTag parameters are only used if the current member has moderator rights
     * It returns a $TResults structure
     * Very important  : member who are not moderators cannot see other people subscriptions
     */
    public function searchSubscriptions($cid=0,$IdThread=0,$IdTag=0) {
        $IdMember=0 ;
        
        $TResults->Username="" ;
        $TResults->ThreadTitle="" ;
        $TResults->IdThread=0 ;
        
        if (!empty($_SESSION["IdMember"])) { // By default current members
            $IdMember=$_SESSION["IdMember"];
        }
        if (($cid!=0) and ($this->BW_Right->HasRight("ForumModerator","SeeSubscriptions"))) {
            // Moderators can see the subscriptions of other members
            if (is_numeric($cid)) {
                $IdMember=$cid ;
                $query = sprintf("select id,Username from members where id%d=",$IdMember) ;
                $s = $this->dao->query($query);
                if (!$s) {
                    throw new PException('Could not retrieve members username via id!');
                }
                $row = $s->fetch(PDB::FETCH_OBJ) ;
                if (isset($row->Username)) {
                    $TResults->Username=$row->Username ;
                }
            } else {
                $query = sprintf(
                    "
SELECT id
FROM members
WHERE username='%s'
                    ",
                    $this->dao->escape($cid)
                ); 
                $s = $this->dao->query($query);
                if (!$s) {
                    throw new PException('Could not retrieve members id via username !');
                }
                $row = $s->fetch(PDB::FETCH_OBJ) ;
                if (isset($row->id)) {
                    $IdMember=$row->id ;
                }
            }
        }
      
        if (!empty($IdThread) and ($this->BW_Right->HasRight("ForumModerator","SeeSubscriptions"))) {
            // In this case we will browse all the threads
            $query = sprintf(
                "
SELECT
    `members_threads_subscribed`.`id` as IdSubscribe,
    `members_threads_subscribed`.`created` AS `subscribedtime`, 
    `forums_threads`.`threadid` as IdThread,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forums_threads`.`title`,
    `forums_threads`.`IdTitle`,
    `forums_threads`.`IdGroup`,
    `members_threads_subscribed`.`ActionToWatch`,
    `members_threads_subscribed`.`UnSubscribeKey`,
    `members`.`Username` 
FROM `forums_threads`,`members`,`members_threads_subscribed`
WHERE `forums_threads`.`threadid` = `members_threads_subscribed`.`IdThread`
AND `members_threads_subscribed`.`IdThread`=%d
AND `members`.`id`=`members_threads_subscribed`.`IdSubscriber` 
ORDER BY `subscribedtime` DESC
                ",
                $IdThread
            );
        } else {
            $query = sprintf(
                "
SELECT
    `members_threads_subscribed`.`id` as IdSubscribe,
    `members_threads_subscribed`.`created` AS `subscribedtime`, 
    `ThreadVisibility`,
    `ThreadDeleted`,
    `forums_threads`.`threadid` as IdThread,
    `forums_threads`.`title`,
    `forums_threads`.`IdTitle`,
    `members_threads_subscribed`.`ActionToWatch`,
    `members_threads_subscribed`.`UnSubscribeKey`,
    `members`.`Username` 
FROM `forums_threads`,`members`,`members_threads_subscribed`
WHERE `forums_threads`.`threadid` = `members_threads_subscribed`.`IdThread`
and `members_threads_subscribed`.`IdSubscriber`=%d
and `members`.`id`=`members_threads_subscribed`.`IdSubscriber` 
ORDER BY `subscribedtime` DESC
                ",
                $IdMember
            );
        }
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve members_threads_subscribed sts via searchSubscription !');
        }
        
        if ($IdThread!=0) {
            $TResults->ThreadTitle="Not Yet found Id Thread=#".$IdThread ; // Initialize the title in case there is a selected thread
            $TResults->IdThread=$IdThread ;
        }

        $TResults->TData = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($IdThread!=0) { // Initialize the title in case there is a selected thread
                $TResults->ThreadTitle=$row->title ;
            }
            $TResults->TData[] = $row;
        }

// now the Tags

        if (!empty($IdTag) and ($this->BW_Right->HasRight("ForumModerator","SeeSubscriptions"))) {
            // In this case we will browse all the tags
            $query = sprintf(
                "
SELECT
    `members_tags_subscribed`.`id` as IdSubscribe,
    `members_tags_subscribed`.`created` AS `subscribedtime`, 
    `forums_tags`.`id` as IdTag,
    `forums_tags`.`IdName`,
    `forums_tags`.`tag` as title,
    `forums_tags`.`IdName`,
    `members_tags_subscribed`.`ActionToWatch`,
    `members_tags_subscribed`.`UnSubscribeKey`,
    `members`.`Username` 
FROM `forums_tags`,`members`,`members_tags_subscribed`
WHERE `forums_tags`.`id` = `members_tags_subscribed`.`IdTag`
AND `members_tags_subscribed`.`IdThread`=%d
AND `members`.`id`=`members_tags_subscribed`.`IdSubscriber` 
ORDER BY `subscribedtime` DESC
                ",
                $IdThread
            );
        } else {
            $query = sprintf(
                "
SELECT
    `members_tags_subscribed`.`id` as IdSubscribe,
    `members_tags_subscribed`.`created` AS `subscribedtime`, 
    `forums_tags`.`id` as IdTag,
    `forums_tags`.`IdName`,
    `forums_tags`.`tag` as title,
    `forums_tags`.`IdName`,
    `members_tags_subscribed`.`ActionToWatch`,
    `members_tags_subscribed`.`UnSubscribeKey`,
    `members`.`Username` 
FROM `forums_tags`,`members`,`members_tags_subscribed`
WHERE `forums_tags`.`id` = `members_tags_subscribed`.`IdTag`
and `members_tags_subscribed`.`IdSubscriber`=%d
and `members`.`id`=`members_tags_subscribed`.`IdSubscriber` 
ORDER BY `subscribedtime` DESC
                ",
                $IdMember
            );
        }
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve members_tags_subscribed sts via searchSubscription !');
        }

        $TResults->TDataTag = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if ($IdTag!=0) { // Initialize the title in case there is a selected thread
                $TResults->TagTitle=$row->title ;
            }
            $TResults->TDataTag[] = $row;
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
        if (isset($_SESSION["IdMember"])) {
            MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from Thread=#".$row->IdThread, "Forum");
            if ($_SESSION["IdMember"]!=$row->IdSubscriber) { // If it is not the member himself, log a forum action in addition
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
        if (isset($_SESSION["IdMember"]) and $IdMember==0) {
            $IdMember=$_SESSION["IdMember"] ;
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
       if (isset($_SESSION["IdMember"]) and $IdMember==0) {
                 $IdMember=$_SESSION["IdMember"] ;
       }
       
       // Check if there is a previous Subscription
       if ($this->IsThreadSubscribed($IdThread,$_SESSION["IdMember"])) {
             MOD_log::get()->write("Allready subscribed to Thread=#".$IdThread, "Forum");
          return(false) ;
       }
       $key=MD5(rand(100000,900000)) ;
       $query = "insert into members_threads_subscribed(IdThread,IdSubscriber,UnSubscribeKey)  values(".$IdThread.",".$_SESSION["IdMember"].",'".$this->dao->escape($key)."')" ; 
       $s = $this->dao->query($query);
       if (!$s) {
              throw new PException('Forum->SubscribeThread failed !');
       }
       $IdSubscribe=mysql_insert_id() ;
         MOD_log::get()->write("Subscribing to Thread=#".$IdThread." IdSubscribe=#".$IdSubscribe, "Forum");
    } // end of UnsubscribeThread


 
	 
    /**
     * This function remove the subscription marked by IdSubscribe
     * @IdSubscribe is the primary key of the members_tags_subscribed area to remove
     * @Key is  the key to check to be sure it is not an abuse of url
     * It returns a $res=1 if ok
     */
    public function UnsubscribeTag($IdSubscribe=0,$Key="") {
        $query = sprintf(
            "
SELECT
    members_tags_subscribed.id AS IdSubscribe,
    IdTag,
    IdSubscriber,
    Username from members,
    members_tags_subscribed
WHERE members.id=members_tags_subscribed.IdSubscriber
AND members_tags_subscribed.id=%d
AND UnSubscribeKey='%s'
            ",
            $IdSubscribe,$this->dao->escape($Key)
        ); 
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->UnsubscribeTag Could not retrieve the subscription !');
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        if (!isset($row->IdSubscribe)) {
            MOD_log::get()->write("No entry found while Trying to unsubscribe Tag  IdSubscribe=#".$IdSubscribe." IdKey=".$Key, "Forum");
            return(false) ;
        }
        $query = sprintf(
            "
DELETE
FROM members_tags_subscribed
WHERE id=%d
AND UnSubscribeKey='%s'
            ",
            $IdSubscribe,
            $this->dao->escape($Key)
        ); 
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->UnsubscribeTag delete failed !');
        }
        if (isset($_SESSION["IdMember"])) {
            MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from IdTag=#".$row->IdTag, "Forum");
            if ($_SESSION["IdMember"]!=$row->IdSubscriber) { // If it is not the member himself, log a forum action in addition
                MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from IdTag=#".$row->IdTag, "ForumModerator");
            }
        }
        else {
            MOD_log::get()->write("Unsubscribing member <b>".$row->Username."</b> from IdTag=#".$row->IdTag." without beeing logged", "Forum");
        }
        return(true) ;
    } // end of UnsubscribeTag

    /**
     * This function remove the subscription without checking the key
     *
     * @param unknown_type $IdTag the id of the Tag to unsubscribe to
     * @param unknown_type $ParamIdMember the member to unsubscribe, if 0, the current member will eb used
     * @return unknown
     */
    public function UnsubscribeTagDirect($IdTag=0,$ParamIdMember=0) {
        $IdMember=$ParamIdMember ;
        if (isset($_SESSION["IdMember"]) and $IdMember==0) {
            $IdMember=$_SESSION["IdMember"] ;
        }
        
        $query = sprintf(
            "
DELETE
FROM members_tags_subscribed
WHERE IdSubscriber=%d
AND IdTag=%d
            ",
            $IdMember,
            $IdTag
        ); 
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Forum->UnsubscribeTagDirect failed to delete !');
        }
        MOD_log::get()->write("Unsubscribing direct (By NotifyMe) member=#".$IdMember." from IdTag=#".$IdTag, "Forum");
        return(true) ;
    } // end of UnsubscribeTagDirect
    
    
    /**
     * This function allow to subscribe to a Tag
     * 
     * @$IdTag : The Tag we want the user to subscribe to
     * @$ParamIdMember optional IdMember, by default set to 0 in this case current logged member will be used
     * It also check that member is not yet subscribing to Tag
     */
    public function SubscribeTag($IdTag,$ParamIdMember=0) {
		$IdMember=$ParamIdMember ;
		if (!empty($_SESSION["IdMember"]) and $IdMember==0) {
            $IdMember=$_SESSION["IdMember"] ;
		}
       
		// Check if there is a previous Subscription
		if ($this->IsTagSubscribed($IdTag,$IdMember)) {
            MOD_log::get()->write("Allready subscribed to IdTag=#".$IdTag, "Forum");
			return(false) ;
		}
		$key=MD5(rand(100000,900000)) ;
		$query = "insert into members_tags_subscribed(IdTag,IdSubscriber,UnSubscribeKey)  values(".$IdTag.",".$IdMember.",'".$this->dao->escape($key)."')" ; 
		$s = $this->dao->query($query);
		if (!$s) {
            throw new PException('Forum->SubscribeTag to IdTag=#'.$IdTag.' failed !');
		}
		$IdSubscribe=mysql_insert_id() ;
        MOD_log::get()->write("Subscribing to IdTag=#".$IdTag." IdSubscribe=#".$IdSubscribe, "Forum");
    } // end of UnsubscribeTag

	 

	 
	 
	 
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
            "SELECT    `postid`,`forums_posts`.`postid` as IdPost, UNIX_TIMESTAMP(`create_time`) AS `posttime`,  `message`,
    `OwnerCanStillEdit`,`IdContent`,  `forums_threads`.`threadid`,   `forums_threads`.`title`,
    `HasVotes`,
    `ThreadVisibility`,
    `ThreadDeleted`,
    `PostVisibility`,
    `PostDeleted`,
    `ThreadDeleted`,
    `IdLocalEvent`,
    `forums_threads`.`IdTitle`,`forums_threads`.`IdGroup`,   `IdWriter`,   `members`.`Username` AS `OwnerUsername`, `groups`.`Name` AS `GroupName`,    `geonames_cache`.`fk_countrycode` 
		FROM (forums_posts, members, forums_threads, addresses)  
LEFT JOIN `groups` ON (`forums_threads`.`IdGroup` = `groups`.`id`)
LEFT JOIN `geonames_cache` ON (addresses.IdCity = geonames_cache.geonameid)
WHERE `forums_posts`.`IdWriter` = %d AND `forums_posts`.`IdWriter` = `members`.`id` 
AND `forums_posts`.`threadid` = `forums_threads`.`threadid` 
AND addresses.IdMember = members.id AND addresses.rank = 0
AND ($this->PublicThreadVisibility)
AND ($this->PublicPostVisibility)
AND ($this->PostGroupsRestriction)
ORDER BY `posttime` DESC    ",    $IdMember   );
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
    
    private $geonameid = 0;
    private $countrycode = 0;
    private $admincode;
    private $threadid = 0;
    private $tags = array();
    private $continent = false;
    private $page = 1;
    private $messageId = 0;
    private $TopMode=Forums::CV_TOPMODE_LASTPOSTS; // define which top mode is to be use latest post or CATEGORIES
//    private $TopMode=Forums::CV_TOPMODE_CATEGORY; // define which top mode is to be use latest post or CATEGORIES


    public function setTopMode($Mode) {
        $this->TopMode = $Mode ;
    }
    public function getTopMode() {
        return $this->TopMode;
    }
    public function setGeonameid($geonameid) {
        $this->geonameid = (int) $geonameid;
    }
    public function getGeonameid() {
        return $this->geonameid;
    }
    public function setCountryCode($countrycode) {
        $this->countrycode = $countrycode;
    }
    public function getCountryCode() {
        return $this->countrycode;
    }
    public function setAdminCode($admincode) {
        $this->admincode = $admincode;
    }
    public function getAdminCode() {
        return $this->admincode;
    }
    public function addTag($tagid) {
        $this->tags[] = (int) $tagid;
    }
    public function getTags() {
        return $this->tags;
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
    public function setContinent($continent) {
        $this->continent = $continent;
    }
    public function getContinent() {
        return $this->continent;
    }
    public function getPage() {
        return $this->page;
    }
    public function setPage($page) {
        $this->page = (int) $page;
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
// Gets the name of a tag
    public function getTagsNamed() {
        $tags = array();

        if ($this->tags) {
            $query = sprintf("SELECT `tagid`, `tag`,`IdName` FROM `forums_tags` WHERE `tagid` IN (%s) ", implode(',', $this->tags)  );
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could not retrieve countries!');
            }
            while ($row = $s->fetch(PDB::FETCH_OBJ)) {
                $tags[$row->tagid] = $this->words->fTrad($row->IdName);
            }
            
        }
        return $tags;
    }
    
/*
* function getAllTags() retrieve up to 50 tags, mix them in an array
* find the corresponding translation (according to members current language)
* it is typically use to build the TagCloud
* and returns an array
*/
    public function getAllTags() {
        $tags = array();
        
        $query = "SELECT `tag`, `tagid`, `counter`,`IdName`,`tag_description`, IdDescription FROM `forums_tags` ORDER BY `counter` DESC LIMIT 25 ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve tags!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
		 	$row->tag=$this->words->fTrad($row->IdName) ; // Retrieve the real tags content
			if (empty($row->IdDescription)) {
				$row->TagDescription="" ;
			}
			else {
				$row->TagDescription=$this->words->fTrad($row->IdDescription) ; // Retrieve the description if any
			}
            $tags[$row->tagid] = $row;
        }
        shuffle($tags);
        return $tags;
    } // end of getAllTags
    
    public function getTagsMaximum() {
        $tagscloud = array();

        $query = "SELECT `tag`, `counter`,`IdName`,`tag_description`,IdDescription  FROM `forums_tags` ORDER BY `counter` DESC LIMIT 1";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve countries!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $tag = $row->tag;
			if (empty($row->IdDescription)) {
				$row->TagDescription="" ;
			}
			else {
				$row->TagDescription=$this->words->fTrad($row->IdDescription) ; // Retrieve the description if any
			}
            $counter = $row->counter;
            $tagscloud[] = array($tag => $counter);
        }
        // Then we want to determine the maximum counter and shuffle the array (unless you want to retain the order from most searched to least searched).

        // extract maximum counter

        $maximum = max($tagscloud);
        $maximum = max($maximum);

        return $maximum;
    }


    public function getTopCategoryLevelTags() {
        $tags = array();
        
        $query = "SELECT `tagid`, `tag`, `tag_description`,`IdName`,`IdDescription` FROM `forums_tags` WHERE `Type` ='Category'  ORDER BY `tag_position` ASC, `tag` ASC";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve TopLevelTags!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $tags[$row->tagid] = $row;
        }
        return $tags;    
    } // end of getTopCategoryLevelTags
    
    /*
     * cleanupText
     *
     * @param string $txt
     * @access private
     * @return string
     */
    private function cleanupText($txt)
    {
        $purifier = MOD_htmlpure::get()->getPurifier();
        return $purifier->purify($txt);
    } // end of cleanupText
    
    public function suggestTags($search) {
        // Split words
        $wtags = explode(',', $search);
        $cleaned = array();
        // Clean up
        foreach ($wtags as $wtag) {
            $wtag = trim($wtag);
            if ($wtag) {
                $cleaned[] = $wtag;
            }
        }
        $wtags = $cleaned;

        // Which word is the person changing?
        $number_words = count($wtags);
        if ($number_words && isset($_SESSION['prev_tag_content']) && $_SESSION['prev_tag_content']) {
            $search_for = false;
            $pos = false;
            for ($i = 0; $i < $number_words; $i++) {
                if (isset($wtags[$i]) && (!isset($_SESSION['prev_tag_content'][$i]) || $wtags[$i] != $_SESSION['prev_tag_content'][$i])) {
                    $search_for = $wtags[$i];
                    $pos = $i;
                }
            }
            if (!$search_for) {
                return array();
            }
        } else if ($number_words) {
            $search_for = $wtags[count($wtags) - 1]; // last word
            $pos = false;
        } else {
            return array();
        }

        if ($search_for) {
    
            $_SESSION['prev_tag_content'] = $wtags;
        
            $tags = array();
            // look for possible matches (from ALL tags) in current user language
            $query = "SELECT `Sentence` FROM `forums_tags`,`forum_trads` 
			 		   WHERE forum_trads.IdTrad=forums_tags.IdName and `forum_trads`.`Sentence` LIKE '".$this->dao->escape($search_for)."%' and forum_trads.IdLanguage=".$_SESSION["IdLanguage"]." ORDER BY `counter` DESC";
            $s = $this->dao->query($query);
            if (!$s) {
                throw new PException('Could not retrieve tag entries for user language='.$_SESSION["IdLanguage"]);
            }
            while ($row = $s->fetch(PDB::FETCH_OBJ)) {
                $tags[] = $row->Sentence;
            }
            
			 if ($_SESSION["IdLanguage"]!=0) {
            	// look for possible matches (from ALL tags) english
            	$query = "SELECT `Sentence` FROM `forums_tags`,`forum_trads` 
			 		   WHERE forum_trads.IdTrad=forums_tags.IdName and `forum_trads`.`Sentence` LIKE '".$this->dao->escape($search_for)."%' and forum_trads.IdLanguage=0 ORDER BY `counter` DESC";
               $s = $this->dao->query($query);
            	if (!$s) {
                 throw new PException('Could not retrieve tag entries in english');
            	}
            	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
                $tags[] = $row->Sentence;
            	}
			}
            
            if ($tags) {
                $out = array();
                $suggestion_number = 0;
                foreach ($tags as $w) {
                    $out[$suggestion_number] = array();
                    for ($i = 0; $i < count($wtags); $i++) {
                        if ($i == $pos) {
                            $out[$suggestion_number][] = $w;
                        } else {
                            $out[$suggestion_number][] .= $wtags[$i];
                        }
                    }
                    $suggestion_number++;
                }
                return $out;
            }
        }
        return array();
    } // end of suggestTags
	 

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
			if ((isset($_SESSION["IdLanguage"]) and (!in_array($_SESSION["IdLanguage"],$allreadyin)))) {
			   $row=$this->GetLanguageName($_SESSION["IdLanguage"]) ;
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
										 WHERE membersgroups.IdGroup=groups.id group by groups.id order by groups.id ";
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
										  members.Status in ('Active','ChoiceInactive','ActiveHidden') and members.id=".$_SESSION['IdMember']." and membersgroups.Status='In' group by groups.id order by groups.id ";
     	$s = $this->dao->query($query);
     	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$row->GroupName=$this->getGroupName($row->Name);
			array_push($tt,$row) ;
		}
		return($tt) ; // returs the array of structures
			
	 } // end of GroupChoices 


	 /**	 
    * This will prepare a post for a moderator action
    * @IdTag : Id of the post to process
	 */
    public function prepareModeratorEditTag($IdTag) {
	 	 $DataTag->IdTag=$IdTag ;
		 $DataTag->Error="" ; // This will receive the error sentence if any
		 
		
// retrieve The tag
//        $query = "select forums_tags.*,count(*) as cnt  from forums_tags,tags_threads where tags_threads.IdTag=forums_tags.id and forums_tags.id=".$DataTag->IdTag." group by  tags_threads.IdThread" ;;
        $query = "select * from forums_tags where forums_tags.id=".$DataTag->IdTag;
        $s = $this->dao->query($query);
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   $DataTag->Tag=$row ;
		 }
		
// Retrieve the count of thread which are using this tag
        $query = "select count(*) as NbThread from tags_threads where IdTag=".$DataTag->IdTag;
        $s = $this->dao->query($query);
		 $row=$s->fetch(PDB::FETCH_OBJ) ;
		 $DataTag->NbThread=$row->NbThread ;

// Retrieve the tags name
        $query = "select forum_trads.*,EnglishName,ShortCode,forum_trads.id as IdForumTrads from forum_trads,languages where IdLanguage=languages.id and IdTrad=".$DataTag->Tag->IdName." order by forum_trads.created asc" ;
		 $DataTag->Names=array() ;
        $s = $this->dao->query($query);
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   array_push($DataTag->Names,$row) ;
		 }

// Retrieve the tags description
        $query = "select forum_trads.*,EnglishName,ShortCode,forum_trads.id as IdForumTrads from forum_trads,languages where IdLanguage=languages.id and IdTrad=".$DataTag->Tag->IdDescription." order by forum_trads.created asc" ;
		 $DataTag->Descriptions=array() ;
        $s = $this->dao->query($query);
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   array_push($DataTag->Descriptions,$row) ;
		 }

		return ($DataTag) ;
	 } // end of prepareModeratorEditTag

    public function getAllContinents() {
        return self::$continents;
    }


	/**
	Return true if the Member is not allowed to see the given post because of groups restrictions
	@$IdMember : Id of the member
	@$rPost data about the post (Thread and Post visibility, id of the group and delettion status)
	return true or false
	!! Becareful, in case the member has moderator right, his moderator rights will not be considerated
	*/
	function NotAllowedForGroup($IdMember,$rPost) {
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
			$qry = $this->dao->query("select IdGroup from membersgroups where IdMember=".$_SESSION["IdMember"]." and IdGroup=".$rPost->IdGroup." and Status='In'");
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
    // This will compute the needed notifications and will prepare enqueing
    // @IdPost : Id of the post to notify about
    // @Type : Type of notification "newthread", "reply","moderatoraction","deletepost","deletethread","useredit","translation"
	// It also consider the visibility of the post before deciding to send the message or not
    // Nota this private function must not make any transaction since it can be called from within a transaction
    // it is not a very big deal if a notification is lost so no need to worry about transations here
	*/
	
    private function prepare_notification($IdPost,$Type) {
        $alwaynotified = array() ;// This will be the list of people who will be notified about every forum activity

        // retrieve the post data
        $query = sprintf("select forums_posts.threadid as IdThread,forums_threads.IdGroup as IdGroup,PostVisibility,PostDeleted,ThreadVisibility,ThreadDeleted from forums_posts,forums_threads where forums_posts.threadid=forums_threads.id and forums_posts.postid=%d",$IdPost) ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('prepare_notification Could not retrieve the post data!');
        }
        $rPost = $s->fetch(PDB::FETCH_OBJ) ;



        // retrieve the forummoderator with Scope ALL
        $query = sprintf("
SELECT `rightsvolunteers`.`IdMember` 
FROM `rightsvolunteers`,`rights` ,`members`
WHERE `rightsvolunteers`.`IdRight`=`rights`.`id` and `rights`.`Name`= 'ForumModerator' 
AND `rightsvolunteers`.`Scope` = '\"All\"' and `rightsvolunteers`.`level` >1 
AND `members`.`id`=`rightsvolunteers`.`IdMember` 
AND `members`.`Status` in ('Active','ActiveHidden')
" 
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve forum moderators!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            array_push($alwaynotified,$row->IdMember) ;
        }

        for ($ii=0;$ii<count($alwaynotified);$ii++) {
            $query = "INSERT INTO `posts_notificationqueue` (`IdMember`, `IdPost`, `created`, `Type`)
                   VALUES (".$alwaynotified[$ii].",".$IdPost.",now(),'".$Type."')" ;
                   $result = $this->dao->query($query);
                   
            if (!$result) {
               throw new PException('prepare_notification failed : for Type='.$Type);
            }
        } // end of for $ii
        
        
		 // Check the user who have subscribed to one tag of this thread 
        $query = sprintf("select IdSubscriber,members_tags_subscribed.id as IdSubscription from members_tags_subscribed,tags_threads where tags_threads.IdTag=members_tags_subscribed.IdTag and tags_threads.IdThread=%d ",$rPost->IdThread) ;
        $s1 = $this->dao->query($query);
        if (!$s1) {
            throw new PException('prepare_notification Could not retrieve the members_tags_subscribed !');
        }
        while ($rSubscribed = $s1->fetch(PDB::FETCH_OBJ)) { // for each subscriber to this thread

			if ($this->NotAllowedForGroup($rSubscribed->IdSubscriber,$rPost)) continue; // Don't notifiy a member if they are group restiction applying to him 

		// we are going to check wether there is allready a pending notification for this post to avoid duplicated
//            die ("\$row->IdSubscriber=".$row->IdSubscriber) ;
            $IdMember=$rSubscribed->IdSubscriber ;
            $query = sprintf("select id from posts_notificationqueue where IdPost=%d and IdMember=%d and Status='ToSend'",$IdPost,$IdMember) ;
            $s = $this->dao->query($query);
            if (!$s) {
               throw new PException('prepare_notification Could not retrieve the posts_notificationqueue(1) !');
            }
            $rAllreadySubscribe = $s->fetch(PDB::FETCH_OBJ) ;
            if (isset($rAllreadySubscribe->id)) {
               continue ; // We don't introduce another subscription if there is allready a pending one for this post for this member
            }

            $query = "INSERT INTO `posts_notificationqueue` (`IdMember`, `IdPost`, `created`, `Type`, `TableSubscription`, `IdSubscription`)  VALUES (".$IdMember.",".$IdPost.",now(),'".$Type."','members_tags_subscribed',".$rSubscribed->IdSubscription.")" ;
            $result = $this->dao->query($query);
                   
            if (!$result) {
               throw new PException('prepare_notification  for tag for Thread=#'.$rPost->IdThread.' failed : for Type='.$Type);
            }
        } // end for each subscriber to this tag
		 
		 
		 
        // Check usual members subscription for thread
        // First retrieve the one who are subscribing to this thread
        $query = sprintf("select IdSubscriber,members_threads_subscribed.id as IdSubscription from members_threads_subscribed where IdThread=%d",$rPost->IdThread) ;
        $s1 = $this->dao->query($query);
        if (!$s1) {
            throw new PException('prepare_notification Could not retrieve the members_threads_subscribed !');
        }
        while ($rSubscribed = $s1->fetch(PDB::FETCH_OBJ)) { // for each subscriber to this thread

			if ($this->NotAllowedForGroup($rSubscribed->IdSubscriber,$rPost)) continue; // Don't notifiy a member if they are group restiction applying to him 

            // we are going to check wether there is allready a pending notification for this post to avoid duplicated
//            die ("\$row->IdSubscriber=".$row->IdSubscriber) ;
            $IdMember=$rSubscribed->IdSubscriber ;
            $query = sprintf("select id from posts_notificationqueue where IdPost=%d and IdMember=%d and Status='ToSend'",$IdPost,$IdMember) ;
            $s = $this->dao->query($query);
            if (!$s) {
               throw new PException('prepare_notification Could not retrieve the posts_notificationqueue(2) !');
            }
            $rAllreadySubscribe = $s->fetch(PDB::FETCH_OBJ) ;
            if (isset($rAllreadySubscribe->id)) {
               continue ; // We dont introduce another subscription if there is allready a pending one for this post for this member
            }

            $query = "INSERT INTO `posts_notificationqueue` (`IdMember`, `IdPost`, `created`, `Type`, `TableSubscription`, `IdSubscription`)  VALUES (".$IdMember.",".$IdPost.",now(),'".$Type."','members_threads_subscribed',".$rSubscribed->IdSubscription.")" ;
            $result = $this->dao->query($query);
                   
            if (!$result) {
               throw new PException('prepare_notification  for Thread=#'.$rPost->IdThread.' failed : for Type='.$Type);
            }
        } // end for each subscriber to this thread

        
		 // Check the user who have subscribed to one group of this thread 
         /*
        $query = sprintf("select IdSubscriber,members_groups_subscribed.id as IdSubscription from members_groups_subscribed,forums_threads where forums_threads.IdGroup=members_groups_subscribed.IdGroup and forums_threads.threadid=%d ",$rPost->IdThread) ;
        $s1 = $this->dao->query($query);
        if (!$s1) {
            throw new PException('prepare_notification Could not retrieve the members_tags_subscribed !');
        }
        */
        $thread = $this->createEntity('Thread')->findByThreadId($rPost->IdThread);
        if ($thread->IdGroup)
        {
            $group = $this->createEntity('Group')->findById($thread->IdGroup);
            $subscribers = $group->getEmailAcceptingMembers();
            foreach ($subscribers as $subscriber)
            {
        //while ($rSubscribed = $s1->fetch(PDB::FETCH_OBJ))  // for each subscriber to this thread Group

                if ($this->NotAllowedForGroup($subscriber->getPKValue(),$rPost)) continue; // Don't notifiy a member if they are group restiction applying to him 

                // we are going to check wether there is allready a pending notification for this post to avoid duplicated
                $query = sprintf("select id from posts_notificationqueue where IdPost=%d and IdMember=%d and Status='ToSend'",$IdPost,$subscriber->getPKValue()) ;
                $s = $this->dao->query($query);
                if (!$s) {
                   throw new PException('prepare_notification Could not retrieve the posts_notificationqueue(1) !');
                }
                $rAllreadySubscribe = $s->fetch(PDB::FETCH_OBJ) ;
                if (isset($rAllreadySubscribe->id)) {
                   continue ; // We dont introduce another subscription if there is allready a pending one for this post for this member
                }

                $query = <<<SQL
INSERT INTO posts_notificationqueue (IdMember, IdPost, created, `Type`, TableSubscription, IdSubscription)
VALUES ('{$subscriber->getPKValue()}','{$IdPost}',now(),'{$Type}','membersgroups',0)
SQL;
                $result = $this->dao->query($query);
                       
                if (!$result)
                {
                    $this->logWrite("prepare_notification  for group for Thread=#{$rPost->IdThread} failed : for Type={$Type}", 'bug');
                }
            }
        } // end for each subscriber to this group
        
    } // end of prepare_notification
    
    
    // This function IsGroupSubscribed return true of the member is subscribing to the IdGroup
    // @$IdGroup : The thread we want to know if the user is subscribing too
    // @$ParamIdMember optional IdMember, by default set to 0 in this case current logged membver will be used
    public function IsGroupSubscribed($IdGroup=0,$ParamIdMember=0) {
       $IdMember=$ParamIdMember ;
       if (isset($_SESSION["IdMember"]) and $IdMember==0) {
                 $IdMember=$_SESSION["IdMember"] ;
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
       if (isset($_SESSION["IdMember"]) and $IdMember==0) {
                 $IdMember=$_SESSION["IdMember"] ;
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
    
    // This function IsTagSubscribed return true of the member is subscribing to the IdTag
    // @$IdThread : The thread we want to know if the user is subscribing too
    // @$ParamIdMember optional IdMember, by default set to 0 in this case current logged member will be used
    public function IsTagSubscribed($IdTag=0,$ParamIdMember=0) {
       $IdMember=$ParamIdMember ;
       if (isset($_SESSION["IdMember"]) and $IdMember==0) {
                 $IdMember=$_SESSION["IdMember"] ;
       }

       // Check if there is a previous Subscription
       $query = sprintf("select members_tags_subscribed.id as IdSubscribe,IdTag,IdSubscriber from members_tags_subscribed where IdTag=%d and IdSubscriber=%d",$IdTag,$IdMember); 
       $s = $this->dao->query($query);
       if (!$s) {
              throw new PException('IsTagSubscribed Could not check previous subscription !');
       }
       $row = $s->fetch(PDB::FETCH_OBJ) ;
       return (isset($row->IdSubscribe))  ;
    } // end of IsTagSubscribed
    
    public function GetThreadVisibility($IdThread) {
        $query = "SELECT ThreadVisibility FROM forums_threads WHERE threadid = " . intval($IdThread);
        $s = $this->dao->query($query);
        if (!$s) {
            // Couldn't fetch the result from the DB assume 'MembersOnly'
            return "MembersOnly";
        }
        $row = $s->fetch(PDB::FETCH_OBJ) ;
        return ($row->ThreadVisibility);
    }

    public function GetPostVisibility($IdPost) {
        $query = "SELECT PostVisibility FROM forums_posts WHERE postid = " . intval($IdPost);
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
} // end of class Forums


class Topic {
    public $topicinfo;
    public $posts = array();
}

class Board implements Iterator {
	public $THREADS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no

    public function __construct(&$dao, $boardname, $link, $navichain=false, $tags=false, $continent=false, $countrycode=false, $admincode=false, $geonameid=false, $board_description=false,$IdGroup=false) {
		$this->THREADS_PER_PAGE=Forums::CV_THREADS_PER_PAGE  ; //Variable because it can change wether the user is logged or no
		$this->POSTS_PER_PAGE=Forums::CV_POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no
		
		$this->BW_Right = MOD_right::get();

		if (!isset($_SESSION['IdMember'])) {
			$this->THREADS_PER_PAGE=100  ; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE=200 ; // Variable because it can change wether the user is logged or no
		}

        $this->dao =& $dao;
    
        $this->boardname = $boardname;
        $this->board_description = $board_description;
        $this->link = $link;
        $this->continent = $continent;
        $this->countrycode = $countrycode;
        $this->admincode = $admincode;
        $this->geonameid = $geonameid;
        $this->navichain = $navichain;
        $this->IdGroup = $IdGroup;
        $this->tags = $tags;

		//	Decide if it is an active LoggeMember or not
		if ((empty($_SESSION["IdMember"]) or empty($_SESSION["MemberStatus"]) or ($_SESSION["MemberStatus"]=='Pending') or $_SESSION["MemberStatus"]=='NeedMore') ) {
            $this->PublicThreadVisibility=" (ThreadVisibility='NoRestriction') and (ThreadDeleted!='Deleted')" ;
            $this->PublicPostVisibility=" (PostVisibility='NoRestriction') and (PostDeleted!='Deleted')" ;
            $this->ThreadGroupsRestriction=" (IdGroup=0 or ThreadVisibility ='NoRestriction')" ;
            $this->PostGroupsRestriction=" (IdGroup=0 or PostVisibility='NoRestriction')" ;
		}
		else {
			$this->PublicThreadVisibility="(ThreadVisibility!='ModeratorOnly') and (ThreadDeleted!='Deleted')" ;
			$this->PublicPostVisibility=" (PostDeleted!='Deleted')" ;
			//if the member prefers to see only posts to his/her groups
            $roxmodel = new RoxModelBase;
            $member = $roxmodel->getLoggedInMember();
            $owngroupsonly = $member->getPreference("ShowMyGroupsTopicsOnly", $default = "No");
            $this->owngroupsonly = $owngroupsonly;
            if ($owngroupsonly == "Yes" && ($this->IdGroup == 0 || !isset($this->IdGroup))) {
                // 0 is the group id for topics without an explicit group, we don't want them in this case. Lazy hack to avoid changing more than necessary: replace 0 with -1
                $this->PostGroupsRestriction = " (IdGroup IN (-1";
                $this->ThreadGroupsRestriction = " (IdGroup IN (-1";
            } else {
                $this->PostGroupsRestriction = " PostVisibility IN ('MembersOnly','NoRestriction') or (PostVisibility = 'GroupOnly' AND IdGroup IN (0";
			    $this->ThreadGroupsRestriction = " ThreadVisibility IN ('MembersOnly','NoRestriction') OR (ThreadVisibility = 'GroupOnly' AND IdGroup IN (0";
            }
			$qry = $this->dao->query("SELECT IdGroup FROM membersgroups WHERE IdMember=" . $_SESSION["IdMember"] . " AND Status = 'In'");
			if (!$qry) {
				throw new PException('Failed to retrieve groups for member id =#'.$_SESSION["IdMember"].' !');
			}
			while ($rr=$qry->fetch(PDB::FETCH_OBJ)) {
				$this->PostGroupsRestriction = $this->PostGroupsRestriction . "," . $rr->IdGroup;
				$this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . "," . $rr->IdGroup;
			}	;
			$this->PostGroupsRestriction = $this->PostGroupsRestriction . "))";
			$this->ThreadGroupsRestriction = $this->ThreadGroupsRestriction . "))";
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
    }
    
    private $dao;
    private $navichain;
    private $numberOfThreads;
    private $totalThreads;
    
    // This function IsTagSubscribed return true of the member is subscribing to the IdTag
    // @$IdThread : The thread we want to know if the user is subscribing too
    // @$ParamIdMember optional IdMember, by default set to 0 in this case current logged member will be used
    public function IsTagSubscribed($IdTag=0,$ParamIdMember=0) {
       $IdMember=$ParamIdMember ;
       if (isset($_SESSION["IdMember"]) and $IdMember==0) {
                 $IdMember=$_SESSION["IdMember"] ;
       }

       // Check if there is a previous Subscription
       $query = sprintf("select members_tags_subscribed.id as IdSubscribe,IdTag,IdSubscriber from members_tags_subscribed where IdTag=%d and IdSubscriber=%d",$IdTag,$IdMember); 
       $s = $this->dao->query($query);
       if (!$s) {
              throw new PException('IsTagSubscribed Could not check previous subscription !');
       }
       $row = $s->fetch(PDB::FETCH_OBJ) ;
       return (isset($row->IdSubscribe))  ;
    } // end of IsTagSubscribed



/**
	this filtres the list of thread results according to the presence of :
	$this->continent ;
	$this->countrycode ;
	$this->admincode ;
	$this->IdGroup ;
	$this->geonameid ;
*/
	public function FilterThreadListResultsWithIdCriteria() {
        $wherethread="" ;
        
        if ($this->continent) {
            $wherethread .= sprintf("AND `forums_threads`.`continent` = '%s' ", $this->continent);
        }
        if ($this->countrycode) {
            $wherethread .= sprintf("AND `countrycode` = '%s' ", $this->countrycode);
        }
        if ($this->admincode) {
            $wherethread .= sprintf("AND `admincode` = '%s' ", $this->admincode);
        }
        if ($this->IdGroup) {
            $wherethread .= sprintf("AND `forums_threads`.`IdGroup` = '%d' ", $this->IdGroup);
        }
        if ($this->geonameid) {
            $wherethread .= sprintf("AND `forums_threads`.`geonameid` = '%s' ", $this->geonameid);
        }
		$wherethread=$wherethread."and (".$this->PublicThreadVisibility.")" ;
		$wherethread=$wherethread."and (".$this->ThreadGroupsRestriction.")" ;
		return($wherethread) ;
	} // end of FilterThreadListResultsWithIdCriteria
	
    public function initThreads($page = 1, $showsticky = true) {
        
        $wherethread=$this->FilterThreadListResultsWithIdCriteria() ;

        if ($showsticky) {
            $orderby = " ORDER BY `stickyvalue` ASC,`last_create_time` DESC";
        } else {
            $orderby = " ORDER BY `last_create_time` DESC";
        }

        $wherein="" ;
        $tabletagthread="" ;
        if ($this->tags) { // If they are filters to consider according to select tags
            $ii=0 ;
            foreach ($this->tags as $tag) {
	 	 		if ($ii==0) {
					$this->IdTag=$tag ; // this will cause a subscribe unsubscribe link to become visible
					if (isset($_SESSION["IdMember"]) && $this->IsTagSubscribed($this->IdTag, $_SESSION["IdMember"])) 
					$this->IdSubscribe=true;
				}
				$tabletagthread.="`tags_threads` as `tags_threads".$ii."`," ;
				$wherethread=$wherethread." and `tags_threads".$ii."`.`IdTag`=".$tag." and `tags_threads".$ii."`.`IdThread`=`forums_threads`.`id` "  ;

				$ii++ ;
			}
		}
        

		$query = "SELECT COUNT(*) AS `number` FROM ".$tabletagthread."`forums_threads` WHERE 1 ".$wherethread;
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}
		$row = $s->fetch(PDB::FETCH_OBJ);
		$this->numberOfThreads = $row->number;
        
		$from = ($this->THREADS_PER_PAGE * ($page - 1));
        
		$query = "SELECT SQL_CALC_FOUND_ROWS `forums_threads`.`threadid`,
		 		  `forums_threads`.`id` as IdThread, `forums_threads`.`title`, 
				  `forums_threads`.`IdTitle`, 
				  `forums_threads`.`IdGroup`, 
				  `forums_threads`.`replies`, 
				  `groups`.`Name` as `GroupName`, 
					`ThreadVisibility`,
					`ThreadDeleted`,
				  `forums_threads`.`views`, 
				  `forums_threads`.`continent`,
				  `first`.`postid` AS `first_postid`, 
				  `first`.`authorid` AS `first_authorid`, 
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`postid` AS `last_postid`, 
				  `last`.`authorid` AS `last_authorid`, 
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
		$query .= "`first_member`.`Username` AS `first_author`,`last_member`.`Username` AS `last_author`,`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`," ;
		$query .= "`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`" ; 
		$query .= "FROM ".$tabletagthread."`forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)" ;
		$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
		$query .= "LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)" ;
		$query .= "LEFT JOIN `members` AS `first_member` ON (`first`.`IdWriter` = `first_member`.`id`)" ;
		$query .= "LEFT JOIN `members` AS `last_member` ON (`last`.`IdWriter` = `last_member`.`id`)" ;
		$query .= "LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)"; 
		$query .= "LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)" ; 
		$query .= "LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)" ;
		$query .= " WHERE 1 ".$wherethread . $orderby . " LIMIT ".$from.", ".$this->THREADS_PER_PAGE ;


		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}

		$sFounRow = $this->dao->query("SELECT FOUND_ROWS() AS `found_rows`");
		if (!$sFounRow) {
			throw new PException('Could not retrieve number of rows!');
		}
        $rowFounRow = $sFounRow->fetch(PDB::FETCH_OBJ);
        $this->totalThreads = $rowFounRow->found_rows;

				
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if (isset($row->continent) && $row->continent) {
                $row->continentid = $row->continent;
                $row->continent = Forums::$continents[$row->continent];
            }

// Now fetch the tags associated with this thread
            $row->NbTags=0 ;
        	$query2="SELECT IdTag,IdName from tags_threads,forums_tags ".
							  "WHERE IdThread=".$row->IdThread." and forums_tags.id=tags_threads.IdTag";
            $s2 = $this->dao->query($query2);
            if (!$s2) {
               throw new PException('Could not retrieve IdTags for Threads!');
            }
            while ($row2 = $s2->fetch(PDB::FETCH_OBJ)) {
                  $row->IdTag[]=$row2->IdTag ;
                  $row->IdName[]=$row2->IdName ;
                  $row->NbTags++ ;
            }
            $this->threads[] = $row;
        }
        
    } // end of initThreads
    
	/**
		This load the treads for a category or which does not belong to a category list
		first case : IdTagCategory is the category the thread  must be declared in
		second case : $NoInCategoryList a string with the list of idCategorr the search thread must not be in
	*/
    public function LoadThreads($IdTagCategory,$NoInCategoryList="") {
	
		$threads=array() ;
		
		if ($NoInCategoryList!="") {
			$query= "SELECT SQL_CALC_FOUND_ROWS `forums_threads`.`threadid`,
		 		`forums_threads`.`id` as IdThread, `forums_threads`.`title`, 
				`forums_threads`.`IdTitle`, 
				`forums_threads`.`IdGroup`, 
				`forums_threads`.`replies`, 
				`groups`.`Name` as `GroupName`, 
				`ThreadVisibility`,
				`ThreadDeleted`,
				  `forums_threads`.`views`, 
				  `forums_threads`.`continent`,
				  99999 as IdTagCategory,
				  `first`.`postid` AS `first_postid`, 
				  `first`.`authorid` AS `first_authorid`, 
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`postid` AS `last_postid`, 
				  `last`.`authorid` AS `last_authorid`, 
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
			$query .= "`first_member`.`Username` AS `first_author`,`last_member`.`Username` AS `last_author`,`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`," ;
			$query .= "`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`" ; 
			$query .= "FROM `tags_threads`,`forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)" ;
			$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
			$query .= "LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)" ;
			$query .= "LEFT JOIN `members` AS `first_member` ON (`first`.`IdWriter` = `first_member`.`id`)" ;
			$query .= "LEFT JOIN `members` AS `last_member` ON (`last`.`IdWriter` = `last_member`.`id`)" ;
			$query .= "LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)"; 
			$query .= "LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)" ; 
			$query .= "LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)" ;
			$query .= " where `tags_threads`.`IdThread`=`forums_threads`.`id`  and  `tags_threads`.`IdTag` not in (".$NoInCategoryList.")" ;
			$query = $query ." and (".$this->PublicThreadVisibility.")" ;
			$query = $query ." and (".$this->ThreadGroupsRestriction.")" ;
			$query .= " group by `forums_threads`.`id`" ;
			$query .= " ORDER BY `stickyvalue` asc,`last_create_time` DESC LIMIT 3 " ;
		}
		else {
			$query = "SELECT SQL_CALC_FOUND_ROWS `forums_threads`.`threadid`,
		 		`forums_threads`.`id` as IdThread, `forums_threads`.`title`, 
				`forums_threads`.`IdTitle`, 
				`forums_threads`.`IdGroup`, 
				`forums_threads`.`replies`, 
				`ThreadVisibility`,
				`ThreadDeleted`,
				  `groups`.`Name` as `GroupName`, 
				  `forums_threads`.`views`, 
				  `forums_threads`.`continent`,
				  ".$IdTagCategory." as IdTagCategory,
				  `first`.`postid` AS `first_postid`, 
				  `first`.`authorid` AS `first_authorid`, 
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`postid` AS `last_postid`, 
				  `last`.`authorid` AS `last_authorid`, 
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
			$query .= "`first_member`.`Username` AS `first_author`,`last_member`.`Username` AS `last_author`,`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`," ;
			$query .= "`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`" ; 
			$query .= "FROM `tags_threads`,`forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)" ;
			$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
			$query .= "LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)" ;
			$query .= "LEFT JOIN `members` AS `first_member` ON (`first`.`IdWriter` = `first_member`.`id`)" ;
			$query .= "LEFT JOIN `members` AS `last_member` ON (`last`.`IdWriter` = `last_member`.`id`)" ;
			$query .= "LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)"; 
			$query .= "LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)" ; 
			$query .= "LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)" ;
			$query .= " where `tags_threads`.`IdThread`=`forums_threads`.`id` and  `tags_threads`.`IdTag`=".$IdTagCategory ;
			$query = $query ." and (".$this->PublicThreadVisibility.")" ;
			$query = $query ." and (".$this->ThreadGroupsRestriction.")" ;
			$query = $query ." ORDER BY `stickyvalue` asc,`last_create_time` DESC LIMIT 3" ;
		}


		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}

		$sFounRow = $this->dao->query("SELECT FOUND_ROWS() AS `found_rows`");
		if (!$sFounRow) {
			throw new PException('Could not retrieve number of rows!');
		}
        $rowFounRow = $sFounRow->fetch(PDB::FETCH_OBJ);
        $this->totalThreads = $rowFounRow->found_rows;
				
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            if (isset($row->continent) && $row->continent) {
                $row->continentid = $row->continent;
                $row->continent = Forums::$continents[$row->continent];
            }

// Now fetch the tags associated with this thread
            $row->NbTags=0 ;
			$row2->IdName=99999 ; // Nedeed because we need an identifier !
        	$query2="SELECT IdTag,IdName from tags_threads,forums_tags ".
							  "WHERE IdThread=".$row->IdThread." and forums_tags.id=tags_threads.IdTag";
            $s2 = $this->dao->query($query2);
            if (!$s2) {
               throw new PException('Could not retrieve IdTags for Threads!');
            }
            while ($row2 = $s2->fetch(PDB::FETCH_OBJ)) {
//            echo $row2->IdTag," " ;
                  $row->IdTag[]=$row2->IdTag ;
                  $row->IdName[]=$row2->IdName ;
                  $row->NbTags++ ;
            }
            $threads[] = $row;
        }
		
		return($threads) ;
        
    } // end of LoadThreads

    private $threads = array();
    public function getThreads() {
        return $this->threads;
    }
    

    private $continent;
    private $countrycode;
    private $admincode;
    private $geonameid;
    private $tags;

    private $boardname;
    public function getBoardName() {
        return $this->boardname;
    }
    
    private $board_description;
    public function getBoardDescription() {
        return $this->tags;
    }
    
    private $link;
    public function getBoardLink() {
        return $this->link;
    }
    
    public function getNaviChain() {
        return $this->navichain;
    }
    
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



/**
     * Notify volunteers
     * // TODO: create appropriate template
     * @param array $vars with username
*/

Public function MailTheReport($IdPost,$IdReporter,$message,$IdModerator=0,$ReportStatus,$ToMember=0)    {
    //Load the files we'll need
    require_once "bw/lib/swift/Swift.php";
    require_once "bw/lib/swift/Swift/Connection/SMTP.php";
    require_once "bw/lib/swift/Swift/Message/Encoder.php";
        
		
	$ss=" select forums_threads.title as ThreadTitle,forums_threads.id as IdThread,writer.Username as UsernamePostWriter,reporter.Username as UsernameReporter " ;
	$ss=$ss." from forums_posts,members as writer,members as reporter,forums_threads where writer.id=forums_posts.IdWriter and reporter.id=".$IdReporter." and forums_posts.id=".$IdPost." and forums_threads.id=forums_posts.threadid" ; 
	$qry=mysql_query($ss) ;
    if (!$qry) {
        throw new PException('Could not retrieve '.$ss);
    }
	$rPost=mysql_fetch_object($qry) ;
	$IdThread=$rPost->IdThread ;
	$UsernamePostWriter=$rPost->UsernamePostWriter ;
	$UsernameReporter=$rPost->UsernameReporter ;
        
    // FOR TESTING ONLY (using Gmail SMTP Connection for example):
    // $smtp =& new Swift_Connection_SMTP("smtp.gmail.com", Swift_Connection_SMTP::PORT_SECURE, Swift_Connection_SMTP::ENC_TLS);
    // $smtp->setUsername("YOURUSERNAME");
    // $smtp->setpassword("YOURPASSWORD");
    // $swift =& new Swift($smtp);
        
    $language = $_SESSION['lang'];    // TODO: convert to something readable
		
	$reportlink="http://".PVars::getObj('env')->baseuri."/forums/reporttomod/".$IdPost ;
	$postlink="http://".PVars::getObj('env')->baseuri."/forums/s".$IdThread."/#".$IdPost ;
	if ($ToMember==1) {
		$subject = "Forum moderator report for post #".$IdPost." to ".$UsernameReporter ;
		$text="A BeWelcome moderator has answered your request<br />" ;
		$text=$text."Thread: <b>".$rPost->ThreadTitle."</b><br />" ;
		$text=$text."The status of this report is ".$ReportStatus ;
		$text=$text."You can view this report at <a href=\"".$reportlink."\">".$reportlink."</a>" ;
		$text=$text."<hr />".$message ;
		$mReceiver= $this->createEntity('Member',$IdReporter) ;
		$Email=$mReceiver->get_email() ;
		$sender = "noreply@bewelcome.org" ;
	}
	else {
		$subject = "moderator report from ".$UsernameReporter." for the post #".$IdPost." written by ".$UsernamePostWriter ;
		$text="member <a href=\"".PVars::getObj('env')->baseuri."/members/".$UsernameReporter."\">".$UsernameReporter."</a>" ;
		$text=$text." has written a report about member <a href=\"http://".PVars::getObj('env')->baseuri."/members/".$UsernamePostWriter."\">".$UsernamePostWriter."</a> for post <a href=\"".$postlink."\">".$postlink."</a>" ;
		$text=$text."Thread: <b>".$rPost->ThreadTitle."</b><br />" ;
		$text.="The status of this report is ".$ReportStatus ;
		$text.="You can view this report at <a href=\"".$reportlink."\">".$reportlink."</a>" ;
		$text.="<hr />".$message ;
		$mModerator= $this->createEntity('Member',$IdModerator) ;
		$Email=$mModerator->get_email() ;
		$mReporter= $this->createEntity('Member',$IdReporter) ;
		// set the sender
		$sender = strip_tags(str_replace("%40","@",$mReporter->get_email())) ;
	}
	$Email=strip_tags(str_replace("%40","@",$Email)) ;
	
                
	
/*
echo "Email=".$Email,"<br />" ; ;
echo "Subject=".$subject,"<br />" ;
echo "text=".$text,"<br />" ;
die("force stop") ;
*/
	

    //Start Swift
    $swift = new Swift(new Swift_Connection_SMTP("localhost"));
				
    //Create a message
    $message = new Swift_Message($subject);
        
    //Add some "parts"
    $message->attach(new Swift_Message_Part($text));

    // Using a html-template
    ob_start();
    require 'templates/mail/mail_html.php';
    $message_html = ob_get_contents();
    ob_end_clean();
    $message->attach(new Swift_Message_Part($message_html, "text/html"));

    //Now check if Swift actually sends it
     MOD_log::get()->write("about sending report for post #".$IdPost." message=<br />".$text."<br /> sent to [".$Email."] from [".$sender."] for post #".$IdPost, "Forum");
    if ($swift->send($message, $Email , $sender)) {
        MOD_log::get()->write("report for post #".$IdPost." sent to ".$Email." from ".$sender." for post #".$IdPost, "Forum");
        $status = true;
    } else {
/*
	print_r($recipients) ;
	die(0) ;
	*/
        MOD_log::get()->write("<b>FAILURE</b> to report for post #".$IdPost." sent to ".$recipents[0]." from ".$sender." for post #".$IdPost, "Forum");
        $status = false;
    }
} // end of MailTheReport
}
