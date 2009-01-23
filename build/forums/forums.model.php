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

class Forums extends PAppModel {
    const CV_THREADS_PER_PAGE = 15;
    const CV_POSTS_PER_PAGE = 15;
    const CV_TOPMODE_CATEGORY=1; // Says that the forum topmode is for categories
    const CV_TOPMODE_LASTPOSTS=2; // Says that the forum topmode is for categories

    const NUMBER_LAST_POSTS_PREVIEW = 5; // Number of Posts shown as a help on the "reply" page
	
	public $THREADS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no
	public $words ; // a shortcut to words module
	public $ForumOrderList ; // The order of list in forum ascencding or desc this is a preference

	 
/** ------------------------------------------------------------------------------
* function : MakeRevision
* this is a copy of a function allready running in Function tools
* this is not the best place for it, please contact jeanyves if you feel like to change this
* MakeRevision this function save a copy of current value of record Id in table
* TableName for member IdMember with Done By reason
* @$Id : id of the record
* @$TableName : table where the revision is to be done 
* @$IdMemberParam : the member who cause the revision, the current memebr will be use if this is not set
* @$DoneBy : a text to say why the update was done (this must be one of the value of the enum 'DoneByMember','DoneByOtherMember","DoneByVolunteer','DoneByAdmin','DoneByModerator')
*/
function MakeRevision($Id, $TableName, $IdMemberParam = 0, $DoneBy = "DoneByMember") {
	global $_SYSHCVOL; // this is needed to retrieve the optional mem
	$IdMember = $IdMemberParam;
	if ($IdMember == 0) {
		$IdMember = $_SESSION["IdMember"];
	}
	$qry = mysql_query("SELECT * FROM " . $TableName . " WHERE id=" . $Id);
	if (!$qry) {
	  throw new PException("forum::MakeRevision fail to select id=#".$Id." from ".$TableName);
	}

	$count = mysql_num_fields($qry);
	$rr = mysql_fetch_object($qry);

	$XMLstr = "";
	for ($ii = 0; $ii < $count; $ii++) {
		$field = mysql_field_name($qry, $ii);
		$XMLstr .= "<field>" . $field . "</field>\n";
		$XMLstr .= "<value>" . $rr-> $field . "</value>\n";
	}
	$str = "INSERT INTO " . $_SYSHCVOL['ARCH_DB'] . ".previousversion(IdMember,TableName,IdInTable,XmlOldVersion,Type) VALUES(" . $IdMember . ",'" . $TableName . "'," . $Id . ",'" . mysql_real_escape_string($XMLstr) . "','" . $DoneBy . "')";
	if (!$qry) {
	  throw new PException("forum::MakeRevision fail to insert id=#".$Id." for ".$TableName." into ".$_SYSHCVOL['ARCH_DB'] . ".previousversion");
	}
	mysql_query($str);
} // end of MakeRevision


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
	$DefLanguage=$this->GetLanguageChoosen() ;
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}

	if ($_IdLanguage == -1) {
		$IdLanguage = $DefLanguage;
	}
	else {
		$IdLanguage = $_IdLanguage;
	}

	if ($IdTrad <=0) { // if a new IdTrad is needed
		// Compute a new IdTrad
   	$s = $this->dao->query("SELECT MAX(IdTrad) AS maxi FROM forum_trads");
   	if (!$s) {
      	   throw new PException('Failed in InsertInFTrad searchin max(IdTrad)');
   	}
		$rr=$s->fetch(PDB::FETCH_OBJ) ;
		if (isset ($rr->maxi)) {
			$IdTrad = $rr->maxi + 1;
		} else {
			$IdTrad = 1;
		}
	}

	$IdOwner = $IdMember;
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$Sentence = $ss;
	$str = "insert into forum_trads(TableColumn,IdRecord,IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
	$str .= "Values('".$TableColumn."',".$IdRecord.",". $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . $Sentence . "\",now())";
   $s = $this->dao->query($str);
   if (!$s) {
      throw new PException('Failed in InsertInFTrad for inserting in forum_trads!');
   }
	// Now save the redudant reference
	if (($IdRecord>0) and (!empty($TableColumn))) {
	   $table=explode(".",$TableColumn) ;
	   $str="update ".$table[0]." set ".$TableColumn."=".$IdTrad." where id=".$IdRecord ;
      $s = $this->dao->query($str);
      if (!$s) {
      	  throw new PException("InsertInFTrad Failed in updating ".$TableColumn." for IdRecord=#".$IdRecord." with value=[".$IdTrad."]");
      }
	   
	}
	return ($IdTrad);
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
	$DefLanguage=$this->GetLanguageChoosen() ;
	if ($IdOwner == 0) {
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $IdOwner;
	}
	if ($IdTrad == 0) {
		return ($this->InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember,$DefLanguage)); // Create a full new translation
	}
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
  	$s = $this->dao->query("SELECT * FROM forum_trads WHERE IdTrad=" . $IdTrad . " AND IdLanguage=" . $DefLanguage." /* in forum->ReplaceInFTrad */");
  	if (!$s) {
  	   throw new PException('Failed in ReplaceInFTrad searching previous IdTrad=#'.$IdTrad.' for IdLanguage='.$DefLanguage);
  	}
	$rr=$s->fetch(PDB::FETCH_OBJ) ;
	if (!isset ($rr->id)) {
		//	  echo "[$str] not found so inserted <br />";
		return ($this->InsertInFTrad($ss,$TableColumn,$IdRecord, $IdMember, $DefLanguage, $IdTrad)); // just insert a new record in memberstrads in this new language
	} else {
		if ($ss != addslashes($rr->Sentence)) { // Update only if sentence has changed
			$this->MakeRevision($rr->id, "forum_trads"); // create revision
			$str = "UPDATE forum_trads SET TableColumn='".$TableColumn."',IdRecord=".$IdRecord.",IdTranslator=" . $IdTranslator . ",Sentence='" . $ss . "' WHERE id=" . $rr->id;
   		$s = $this->dao->query($str);
   		if (!$s) {
      		   throw new PException('Failed in ReplaceInFTrad for updating in forum_trads!');
   		}
		}
	}
	return ($IdTrad);
} // end of ReplaceInFTrad


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

    public function __construct() {
        parent::__construct();
		$this->THREADS_PER_PAGE=Forums::CV_THREADS_PER_PAGE  ; //Variable because it can change wether the user is logged or no
		$this->POSTS_PER_PAGE=Forums::CV_POSTS_PER_PAGE ; //Variable because it can change wether the user is logged or no
		
		if (!isset($_SESSION['IdMember'])) {
			$this->THREADS_PER_PAGE=100  ; // Variable because it can change wether the user is logged or no
			$this->POSTS_PER_PAGE=200 ; // Variable because it can change wether the user is logged or no
		}

		$this->words= new MOD_words();
		$this->IdGroup=0 ; // By default no group
		$this->ByCategories=false ; // toggle or not toglle the main view is TopCategories or TopLevel
		$this->ForumOrderList='No' ;
		if (isset($_SESSION["IdMember"])) {
// Preload the member preference for sort order
			$ss="select Value,IdMember,IdPreference from preferences,memberspreferences  where codeName='PreferenceForumOrderListAsc'  and preferences.id=memberspreferences.IdPreference and memberspreferences.IdMember=".$_SESSION['IdMember'] ;
			$qq = $this->dao->query($ss);
			$rr=$qq->fetch(PDB::FETCH_OBJ) ;
			if (!empty($rr->Value)) {
				$this->ForumOrderList=$rr->Value ;
			}
		}
    }
	
	// This switch the preference ForumOrderList
	public function SwitchForumOrderList() {
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
            throw new PException('SwitchForumOrderList '.$ss.' !');
		}
        MOD_log::get()->write("Switching PreferenceForumOrderListAsc to [".$this->ForumOrderList."]", "ForumModerator");
	} // end of SwitchForumOrderList
    
    public static $continents = array(
        'AF' => 'Africa',
        'AN' => 'Antarctica',
        'AS' => 'Asia',
        'EU' => 'Europe',
        'NA' => 'North America',
        'SA' => 'South Amercia',
        'OC' => 'Oceania'
    );
    
    private function boardTopLevelLastPosts() {
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
            
            if (count($this->tags)>0) {
               $title = $taginfo[$this->tags[count($this->tags) -1]];
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
        $this->board->initThreads($this->getPage());
    } // end of boardTopLevelLastPosts
    

/**
* This retrieve the list of categories
* and the X last post under categories
*/ 
    private function boardTopLevelCategories() {
					
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
    }
    
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
    private function boardGroup() {

        $query = sprintf("SELECT `Name` FROM `groups` WHERE `id` = %d",$this->IdGroup);
        $gr = $this->dao->query($query);
        if (!$gr) {
            throw new PException('No such IdGroup=#'.$this->IdGroup);
        }
        $group = $gr->fetch(PDB::FETCH_OBJ);

        $subboards = array();
		$gtitle= $this->words->getFormatted("ForumGroupTitle",$this->words->getFormatted("Group_" . $group->Name)) ;
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
            
						
            if (count($this->tags)>0) {
               $title =$gtitle." ".$taginfo[$this->tags[count($this->tags) -1]];
               $href = $url.'/t'.$this->tags[count($this->tags) -1].'-'.$title;
            }
            else {
               $title =  $gtitle." "."no tags";
               $href = $url.'/t'.'-'.$title;
            }
            
			 
            $this->board = new Board($this->dao, $title, $href, $subboards, $this->tags, $this->continent,false,false,false,false,$this->IdGroup);
            $this->board->initThreads($this->getPage());
        } else {
            $this->board = new Board($this->dao, $gtitle, ".", $subboards, $this->tags, $this->continent,false,false,false,false,$this->IdGroup);
//            foreach (Forums::$continents as $code => $name) {
//                $this->board->add(new Board($this->dao, $name, 'k'.$code.'-'.$name));
//            }
            $this->board->initThreads($this->getPage());
        }
    } // end of boardGroup
    
    private function pboardGroup()    {
        $query = sprintf("SELECT `Name` FROM `groups` WHERE `id` = %d",$this->IdGroup);
        $gr = $this->dao->query($query);
        if (!$gr) {
            throw new PException('No such IdGroup=#'.$this->IdGroup);
        }
        $group = $gr->fetch(PDB::FETCH_OBJ);

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
          $title = $this->words->getFormatted("Group_" . $group->Name) ;
        }
        
        $this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode, $this->IdGroup);
        
        $this->board->initThreads($this->getPage());
    } // end of boardGroup

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
    
    private function boardLocation()
    {
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
    }
    
    /**
    * Fetch all required data for the view to display a forum
		* this data are stored in $this->board
    */
    public function prepareForum() {
        if (!$this->geonameid && !$this->countrycode && !$this->continent && !$this->IdGroup) {
			if ($this->TopMode==Forums::CV_TOPMODE_CATEGORY) {
				$this->boardTopLevelCategories();
			}
			elseif ($this->TopMode==Forums::CV_TOPMODE_LASTPOSTS) {
				$this->boardTopLevelLastPosts();
			}
			else {
				if (PVars::get()->debug) {
					throw new PException('Invalid Request at TopLevel');
				} else {
					PRequest::home();
				}
			}
		} else if ($this->continent && !$this->geonameid && !$this->countrycode) { 
            $this->boardContinent();
        } else if ($this->IdGroup) { 
            $this->boardGroup();
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
            return PVars::getObj('env')->baseuri.'forums/s'.$topicid;
        } else {
            return false;
        }
    
    }
    
    /*
     * Fill the Vars in order to edit a post
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
    `forums_posts`.`threadid` as `threadid`,
    `message` AS `topic_text`,
		`OwnerCanStillEdit`,
	 `IdContent`,
    `title` AS `topic_title`, `first_postid`, `last_postid`, `IdTitle`,
    `forums_threads`.`continent`,
    `forums_threads`.`IdGroup`,
    `forums_threads`.`geonameid`,
    `forums_threads`.`admincode`,
    `forums_threads`.`countrycode`
FROM `forums_posts`
LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
WHERE `postid` = $this->messageId
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
    `first_postid`,
	`OwnerCanStillEdit`,
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
        
//        if (HasRight("ForumModerator","Edit") || ($User->hasRight('edit_own@forums') && $postinfo->authorid == $User->getId())) {
        if (HasRight("ForumModerator","Edit") ||  ($postinfo->IdWriter == $_SESSION["IdMember"] and $postinfo->OwnerCanStillEdit=="Yes")) {
            $is_topic = ($postinfo->postid == $postinfo->first_postid);
            
            if ($is_topic) {
                $vars_ok = $this->checkVarsTopic($vars);
            } else {
                $vars_ok = $this->checkVarsReply($vars);
            }
            if ($vars_ok) {
                $this->dao->query("START TRANSACTION");
        
                $this->editPost($vars, $User->getId());
                if ($is_topic) {
                    $this->editTopic($vars, $postinfo->threadid);
                }
        
                $this->dao->query("COMMIT");
                
                PPostHandler::clearVars();
                return PVars::getObj('env')->baseuri.'forums/s'.$postinfo->threadid;
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
		 $this->MakeRevision($id, "forum_trads",$_SESSION["IdMember"], $DoneBy = "DoneByModerator")  ;
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
	 
        $query = "SELECT message,forums_posts.threadid,OwnerCanStillEdit,IdWriter,forums_posts.IdFirstLanguageUsed as post_IdFirstLanguageUsed,forums_threads.IdFirstLanguageUsed as thread_IdFirstLanguageUsed,forums_posts.id,IdWriter,IdContent,forums_threads.IdTitle,forums_threads.first_postid from `forums_posts`,`forums_threads` WHERE forums_posts.threadid=forums_threads.id and forums_posts.id = ".$this->messageId ;
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
		 
        $s=$this->dao->query("select IdWriter,forums_threads.id as IdThread,forums_threads.IdTitle,forums_threads.IdFirstLanguageUsed as thread_IdFirstLanguageUsed from forums_threads,forums_posts where forums_threads.first_postid=forums_posts.id");
        if (!$s) {
            throw new PException('editTopic:: previous infor for firtst post in the thread!');
        }
        $rBefore = $s->fetch(PDB::FETCH_OBJ);
		 
		 $this->ReplaceInFTrad($this->dao->escape(strip_tags($vars['topic_title'])),"forums_threads.IdTitle",$rBefore->IdThread, $rBefore->IdTitle, $rBefore->IdWriter) ;

		 // case the update concerns the reference language of the posts
		if ($rBefore->thread_IdFirstLanguageUsed==$this->GetLanguageChoosen()) {
		 	$query="update forums_threads set title='".$this->dao->escape($this->cleanupText($vars['topic_title']))."' where forums_threads.id=".$rBefore->IdThread ;
        	$s=$this->dao->query($query);
		}
		 
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
        return PVars::getObj('env')->baseuri.'forums/s'.$this->threadid;
    } // end of replyProcess
    
	 
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
		 	$expiredate="'".$vars["expiredate"]."'"  ;
		 	$stickyvalue=(int)$vars["stickyvalue"];
			if (empty($expiredate)) {
			   $expiredate="NULL" ;
			}
        	MOD_log::get()->write("Updating Thread=#".$IdThread." IdGroup=#".$IdGroup." Setting expiredate=[".$expiredate."] stickyvalue=".$stickyvalue,"ForumModerator");
				$sql="update forums_threads set IdGroup=".$IdGroup.",stickyvalue=".$stickyvalue.",expiredate=".$expiredate." where id=".$IdThread ;
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

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."]","ForumModerator");
       	$this->dao->query("update forums_posts set OwnerCanStillEdit=".$OwnerCanStillEdit." where id=".$IdPost);
		 }

		 if (isset($vars["submit"]) and ($vars["submit"]=="add translated post")) { // if a new translation is to be added for a title
		 		$IdPost=(int)$vars["IdPost"] ;
        $qry=$this->dao->query("select * from forum_trads where IdTrad=".$vars["IdTrad"]." and IdLanguage=".$vars["IdLanguage"]);
				$rr=$qry->fetch(PDB::FETCH_OBJ) ;
				if (empty($rr->id)) { // Only proceed if no such a post exists
		 			$ss=$vars["NewTranslatedPost"]  ;
					$this->InsertInFTrad($ss,"forums_posts.IdContent",$IdPost, $_SESSION["IdMember"], $vars["IdLanguage"],$vars["IdTrad"]) ;
       		MOD_log::get()->write("Updating Post=#".$IdPost." Adding translation for title in language=[".$vars["IdLanguage"]."]","ForumModerator");
				} 
		 }

	   $IdPost=(int)$vars['IdPost'] ;

		 if (isset($vars["submit"]) and ($vars["submit"]=="update post")) { // if an effective update was chosen for a forum trads
		 	$OwnerCanStillEdit="'".$vars["OwnerCanStillEdit"]."'"  ;

        	MOD_log::get()->write("Updating Post=#".$IdPost." Setting OwnerCanStillEdit=[".$OwnerCanStillEdit."]","ForumModerator");
       	$this->dao->query("update forums_posts set OwnerCanStillEdit=".$OwnerCanStillEdit." where id=".$IdPost);
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
		 
     return PVars::getObj('env')->baseuri.'forums/modfulleditpost/'.$IdPost;
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
		 
        return PVars::getObj('env')->baseuri.'forums/modedittag/'.$IdTag;
    } // end of ModeratorEditTagProcess
    
    public function delProcess() {
        if (!($User = APP_User::login())) {
            return false;
        }
        
        if (HasRight("ForumModerator","Delete")) {
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
                
                $redir = 'forums/s'.$topicinfo->threadid;
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
INSERT INTO `forums_posts` (`authorid`, `threadid`, `create_time`, `message`,`IdWriter`,`IdFirstLanguageUsed`)
VALUES ('%d', '%d', NOW(), '%s','%d',%d)
            ",
            $User->getId(),
            $this->threadid,
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            $_SESSION["IdMember"],$this->GetLanguageChoosen()
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
				if (isset($vars['IdGroup'])) {
				  $IdGroup=$vars['IdGroup'] ;
				}
				
        $this->dao->query("START TRANSACTION");
        
        $query = sprintf(
            "
INSERT INTO `forums_posts` (`authorid`, `create_time`, `message`,`IdWriter`,`IdFirstLanguageUsed`)
VALUES ('%d', NOW(), '%s','%d',%d)
            ",
            $User->getId(),
            $this->dao->escape($this->cleanupText($vars['topic_text'])),
            $_SESSION["IdMember"],$this->GetLanguageChoosen()
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

		
		// todo one day, remove this line (aim to manage the redudancy with the new id)
		$query="update `forums_posts` set `id`=`postid` where id=0" ;		 
        $result = $this->dao->query($query);

 		 $this->InsertInFTrad($this->dao->escape($this->cleanupText($vars['topic_text'])),"forums_posts.IdContent",$postid) ;
        
        $query = sprintf(
            "
INSERT INTO `forums_threads` (`title`, `first_postid`, `last_postid`, `geonameid`, `admincode`, `countrycode`, `continent`,`IdFirstLanguageUsed`,`IdGroup`)
VALUES ('%s', '%d', '%d', %s, %s, %s, %s,%d,%d)
            ",
            $this->dao->escape(strip_tags($vars['topic_title'])),
            $postid,
            $postid, 
            "'".$d_geoname."'",
            "'".$d_admin."'",
            "'".$d_country."'",
            "'".$d_continent."'",$this->GetLanguageChoosen(),$IdGroup
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
        MOD_log::get()->write("New Thread new Tread=#".$threadid." Post=#". $postid." IdGroup=#".$IdGroup." NotifyMe=[".$vars['NotifyMe']."]", "Forum");
        
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
                $query = "SELECT `tagid` FROM `forums_tags`,`forum_trads` WHERE `forum_trads`.`IdTrad`=`forums_tags`.`IdName` and `forum_trads`.`IdLanguage`=".$IdLanguage." and `forum_trads`.`Sentence` = '$tag' ";
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
WHERE `threadid` = '$this->threadid' "
        ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Thread=#".$this->threadid." !');
        }
        $topicinfo = $s->fetch(PDB::FETCH_OBJ);
				
//				echo "\$topicinfo->IdGroup=",$topicinfo->IdGroup ;
        
        // Now fetch the tags associated with this thread
        $topicinfo->NbTags=0 ;
        $query2="SELECT IdTag,IdName from tags_threads,forums_tags ".
							  "WHERE IdThread=".$topicinfo->IdThread." and forums_tags.id=tags_threads.IdTag";
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
        
				
				// Todo here use IdWriter instead of authorid
        $query = sprintf("
SELECT `postid`,UNIX_TIMESTAMP(`create_time`) AS `posttime`,`message`,`IdContent`,`IdWriter`,`user`.`id` AS `user_id`,`user`.`handle` AS `user_handle`,`geonames_cache`.`fk_countrycode`,`threadid`,`OwnerCanStillEdit`,`members`.`Username` as OwnerUsername

FROM `forums_posts`
LEFT JOIN `user` ON (`forums_posts`.`authorid` = `user`.`id`)
LEFT JOIN `members` ON (`forums_posts`.`IdWriter` = `members`.`id`)
LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
WHERE `threadid` = '%d' 
ORDER BY `posttime` ASC
LIMIT %d, %d",$this->threadid,$from,$this->POSTS_PER_PAGE);
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Posts)!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
		   if ($WithDetail) { // if details are required retrieve all thhe Posts of this thread
          	  $sw = $this->dao->query("select  forum_trads.IdLanguage,forum_trads.created as trad_created, forum_trads.updated as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,languages.ShortCode,languages.EnglishName,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,languages,members as mOwner, members as mTranslator
			                           where languages.id=forum_trads.IdLanguage and forum_trads.IdTrad=".$row->IdContent." and mOwner.id=IdOwner and mTranslator.id=IdTranslator order by forum_trads.id asc");
        	  while ($roww = $sw->fetch(PDB::FETCH_OBJ)) {
			    $row->Trad[]=$roww ;
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
        
        $query = sprintf(  "
SELECT
    `forums_threads`.`title`,
    `forums_threads`.`IdTitle`,
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

        // Increase the number of views
        $query = "
UPDATE `forums_threads`
SET `views` = (`views` + 1)
WHERE `threadid` = '$this->threadid' LIMIT 1
            "     ;
        $this->dao->query($query);
        
    } // end of prepareTopic
    
    public function initLastPosts() {
				// Todo here use IdWriter instead of authorid
        $query = sprintf("
SELECT
    `postid`,
    UNIX_TIMESTAMP(`create_time`) AS `posttime`,
    `message`,
	 `IdContent`,
    `user`.`id` AS `user_id`,
    `members`.`Username` AS `user_handle`,
    `members`.`Username` AS `OwnerUsername`,
    `IdWriter`,
	 `threadid`,
		`OwnerCanStillEdit`,
    `geonames_cache`.`fk_countrycode`
FROM `forums_posts`
LEFT JOIN `user` ON (`forums_posts`.`authorid` = `user`.`id`)
LEFT JOIN `members` ON (`forums_posts`.`IdWriter` = `members`.`id`)
LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
WHERE `threadid` = '%d'
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
          	$sw = $this->dao->query("select  forum_trads.IdLanguage,forum_trads.created as trad_created, forum_trads.updated as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,languages.ShortCode,languages.EnglishName,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,languages,members as mOwner, members as mTranslator
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
        if (($cid!=0) and (HasRight("ForumModerator","SeeSubscriptions"))) {
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
      
        if (!empty($IdThread) and (HasRight("ForumModerator","SeeSubscriptions"))) {
            // In this case we will browse all the threads
            $query = sprintf(
                "
SELECT
    `members_threads_subscribed`.`id` as IdSubscribe,
    `members_threads_subscribed`.`created` AS `subscribedtime`, 
    `forums_threads`.`threadid` as IdThread,
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

        if (!empty($IdTag) and (HasRight("ForumModerator","SeeSubscriptions"))) {
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
       if (isset($_SESSION["IdMember"]) and $IdMember==0) {
                 $IdMember=$_SESSION["IdMember"] ;
       }
       
       // Check if there is a previous Subscription
       if ($this->IsTagSubscribed($IdTag,$_SESSION["IdMember"])) {
             MOD_log::get()->write("Allready subscribed to IdTag=#".$IdTag, "Forum");
          return(false) ;
       }
       $key=MD5(rand(100000,900000)) ;
       $query = "insert into members_tags_subscribed(IdTag,IdSubscriber,UnSubscribeKey)  values(".$IdTag.",".$_SESSION["IdMember"].",'".$this->dao->escape($key)."')" ; 
       $s = $this->dao->query($query);
       if (!$s) {
              throw new PException('Forum->SubscribeTag failed !');
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
        else {
           $query = "select id from members where username='".$this->dao->escape($cid)."'" ; 
           $s = $this->dao->query($query);
           if (!$s) {
              throw new PException('Could not retrieve members id via username !');
           }
           $row = $s->fetch(PDB::FETCH_OBJ) ;
           if (isset($row->id)) {
                 $IdMember=$row->id ;
           }
        }

				// Todo here use IdWriter instead of authorid
        $query = sprintf(
            "SELECT    `postid`, UNIX_TIMESTAMP(`create_time`) AS `posttime`,  `message`,
    `OwnerCanStillEdit`,`IdContent`,  `forums_threads`.`threadid`,   `forums_threads`.`title`,
    `forums_threads`.`IdTitle`,`forums_threads`.`IdGroup`,   `user`.`id` AS `user_id`,`IdWriter`,   `members`.`Username` AS `user_handle`, `members`.`Username` AS `OwnerUsername`, `groups`.`Name` AS `GroupName`,    `geonames_cache`.`fk_countrycode` 
		FROM (`forums_posts`,`members`,`forums_threads`,`user`) 
LEFT JOIN `groups` ON (`forums_threads`.`IdGroup` = `groups`.`id`)
LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
WHERE `forums_posts`.`IdWriter` = %d AND `forums_posts`.`IdWriter` = `members`.`id` 
AND `user`.`handle` = `members`.`Username` AND `forums_posts`.`threadid` = `forums_threads`.`threadid` 
AND `forums_posts`.`authorid` = `user`.`id` 
ORDER BY `posttime` DESC    ",    $IdMember   );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve Posts via searchUserposts !');
        }
        $posts = array();
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
          	$sw = $this->dao->query("select forum_trads.IdLanguage,forum_trads.created as trad_created, forum_trads.updated as trad_updated, forum_trads.Sentence,IdOwner,IdTranslator,languages.ShortCode,languages.EnglishName,mTranslator.Username as TranslatorUsername ,mOwner.Username as OwnerUsername from forum_trads,languages,members as mOwner,members as mTranslator    where languages.id=forum_trads.IdLanguage and forum_trads.IdTrad=".$row->IdContent." and mTranslator.id=IdTranslator  and mOwner.id=IdOwner order by forum_trads.id asc");
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
    private $TopMode=Forums::CV_TOPMODE_CATEGORY; // define which top mode is to be use latest post or CATGORIES


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
* and returns an array
*/
    public function getAllTags() {
        $tags = array();
        
        $query = "SELECT `tag`, `tagid`, `counter`,`IdName` FROM `forums_tags` ORDER BY `counter` DESC LIMIT 50 ";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve tags!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
		 	 $row->tag=$this->words->fTrad($row->IdName) ; // Retrieve the real tags content
            $tags[$row->tagid] = $row;
        }
        shuffle($tags);
        return $tags;
    } // end of getAllTags
    
    public function getTagsMaximum() {
        $tagscloud = array();

        $query = "SELECT `tag`, `counter`,`IdName` FROM `forums_tags` ORDER BY `counter` DESC LIMIT 1";
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('Could not retrieve countries!');
        }
        while ($row = $s->fetch(PDB::FETCH_OBJ)) {
            $tag = $row->tag;
            $counter = $row->counter;
            $tagscloud[] = array($tag => $counter);
        }
        // Then we want to determine the maximum counter and shuffle the array (unless you want to retain the order from most searched to least searched).

        // extract maximum counter

        $maximum = max($tagscloud);
        $maximum = max($maximum);

        return $maximum;
    }


    public function getTopLevelTags() {
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
    } // end of getTopLevelTags
    
/*
* cleanupText
*
*
*
*/
    private function cleanupText($txt) {
        $str = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$txt.'</body></html>'; 
        $doc = DOMDocument::loadHTML($str);
        if ($doc) {
            $sanitize = new PSafeHTML($doc);
            $sanitize->allow('html');
            $sanitize->allow('body');
            $sanitize->allow('p');
            $sanitize->allow('div');
            $sanitize->allow('b');
            $sanitize->allow('i');
            $sanitize->allow('u');
            $sanitize->allow('a');
            $sanitize->allow('img');
            $sanitize->allow('em');
            $sanitize->allow('strong');
            $sanitize->allow('hr');
            $sanitize->allow('span');
            $sanitize->allow('ul');
            $sanitize->allow('li');
            $sanitize->allow('font');
            $sanitize->allow('strike');
            $sanitize->allow('br');
            $sanitize->allow('blockquote');
        
            $sanitize->allowAttribute('color');    
            $sanitize->allowAttribute('bgcolor');            
            $sanitize->allowAttribute('href');
            $sanitize->allowAttribute('style');
            $sanitize->allowAttribute('class');
            $sanitize->allowAttribute('width');
            $sanitize->allowAttribute('height');
            $sanitize->allowAttribute('src');
            $sanitize->allowAttribute('alt');
            $sanitize->allowAttribute('title');
            $sanitize->clean();
            $doc = $sanitize->getDoc();
            $nodes = $doc->x->query('/html/body/node()');
            $ret = '';
            foreach ($nodes as $node) {
                $ret .= $doc->saveXML($node);
            }
            return $ret;
        } else {
            // invalid HTML
            return '';
        }
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
				$query="select id as IdLanguage,Name,EnglishName,ShortCode,WordCode from languages where id=".$IdLanguage ;
            	$s = $this->dao->query($query);
            	if (!$s) {
                  throw new PException('Could not retrieve IdLanguage in GetLanguageName entries');
            	}
				else {
					 $row = $s->fetch(PDB::FETCH_OBJ) ;
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
			   array_push($tt,$row) ;
			}
			// Then next will be english (if not allready in the list)
			if (!in_array(0,$allreadyin)) {
			   $row=$this->GetLanguageName(0) ;
		   	   array_push($allreadyin,$row->IdLanguage) ;
			   array_push($tt,$row) ;
			}
			// Then next will the current user language
			if ((isset($_SESSION["IdLanguage"]) and (!in_array($_SESSION["IdLanguage"],$allreadyin)))) {
			   $row=$this->GetLanguageName($_SESSION["IdLanguage"]) ;
		   	   array_push($allreadyin,$row->IdLanguage) ;
			   array_push($tt,$row) ;
			}
			
			// then now all available languages
			$query="select id as IdLanguage,Name,EnglishName,ShortCode,WordCode from languages where id>0 order by FlagSortCriteria asc";
          	$s = $this->dao->query($query);
        	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			   if (!in_array($row->IdLanguage,$allreadyin)) {
			   	  array_push($allreadyin,$row->IdLanguage) ;
			  	  array_push($tt,$row) ;
			   }
			}
			return($tt) ; // returs the array of structures
			
	 
	 } // end of LanguageChoices 

    // This fonction will prepare a list of group in an array that the moderator can use
	 public function ModeratorGroupChoice() {
	 		$tt=array() ;

			$query="select groups.id as IdGroup,Name,count(*) as cnt from groups,membersgroups
										 where HasMembers='HasMember' and membersgroups.IdGroup=groups.id group by groups.id order by groups.id ";
      $s = $this->dao->query($query);
      while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$row->GroupName=$this->words->getFormatted("Group_" . $row->Name);
	  	  array_push($tt,$row) ;
			}
			return($tt) ; // returs the array of structures
			
	 } // end of ModeratorGroupChoices 

    // This fonction will prepare a list of group in an array that the user can use
		// (according to his member ship)
	 public function GroupChoice() {
 		$tt=array() ;

		$query="select groups.id as IdGroup,Name,count(*) as cnt from groups,membersgroups,members 
										 where HasMembers='HasMember' and membersgroups.IdGroup=groups.id and members.id=membersgroups.IdMember and
										  members.Status in ('Active','ChoiceInactive','ActiveHidden') and members.id=".$_SESSION['IdMember']." and membersgroups.Status='In' group by groups.id order by groups.id ";
     	$s = $this->dao->query($query);
     	while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$row->GroupName=$this->words->getFormatted("Group_" . $row->Name) ;
			array_push($tt,$row) ;
		}
		return($tt) ; // returs the array of structures
			
	 } // end of GroupChoices 

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
		 	$DataPost->Error="No Post for Thread=#".$DataPost->Post->threadid ;
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
		 
// retrieve alltag NOT connected to this thread
        $query = "SELECT forums_tags.id AS IdTag, forums_tags.IdName AS IdName,forums_tags.counter  as cnt 
				FROM forums_tags
RIGHT JOIN tags_threads ON ( tags_threads.IdTag != forums_tags.id ) WHERE IdThread =".$DataPost->Thread->id." order by cnt desc" ;
        $s = $this->dao->query($query);
		 $DataPost->AllNoneTags=array() ;
		 while ($row=$s->fetch(PDB::FETCH_OBJ)) {
		 	   $DataPost->AllNoneTags[]=$row ;
		 }
		$DataPost->PossibleGroups=$this->ModeratorGroupChoice() ;		
		return ($DataPost) ;
	 } // end of prepareModeratorEditPost

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
    // This will compute the needed notification and will prepare enqueing
    // @IdPost : Id of the post to notify about
    // @Type : Type of notification "newthread", "reply","moderatoraction","deletepost","deletethread","useredit","translation"
    // Nota this private function must not make any transaction since it can be called from within a transaction
    // it is not a very big deal if a notification is lost so no need to worry about transations here
    private function prepare_notification($IdPost,$Type) {
        $alwaynotified = array() ;// This will be the list of people who will be notified about every forum activity

        // retrieve the post data
        $query = sprintf("select forums_posts.threadid as IdThread from forums_posts where  forums_posts.postid=%d",$IdPost) ;
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('prepare_notification Could not retrieve the post data!');
        }
        $rPost = $s->fetch(PDB::FETCH_OBJ) ;



        // retrieve the forummoderator with Scope ALL
        $query = sprintf("
SELECT `rightsvolunteers`.`IdMember` 
FROM `rightsvolunteers`,`rights` 
WHERE `rightsvolunteers`.`IdRight`=`rights`.`id` and `rights`.`Name`= 'ForumModerator' 
AND `rightsvolunteers`.`Scope` = '\"All\"' and `rightsvolunteers`.`level` >1 " 
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
               continue ; // We dont introduce another subscription if there is allready a pending one for this post for this member
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
        $query = sprintf("select IdSubscriber,members_groups_subscribed.id as IdSubscription from members_groups_subscribed,forums_threads where forums_threads.IdGroup=members_groups_subscribed.IdGroup and forums_threads.threadid=%d ",$rPost->IdThread) ;
        $s1 = $this->dao->query($query);
        if (!$s1) {
            throw new PException('prepare_notification Could not retrieve the members_tags_subscribed !');
        }
        while ($rSubscribed = $s1->fetch(PDB::FETCH_OBJ)) { // for each subscriber to this thread Group
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
               continue ; // We dont introduce another subscription if there is allready a pending one for this post for this member
            }

            $query = "INSERT INTO `posts_notificationqueue` (`IdMember`, `IdPost`, `created`, `Type`, `TableSubscription`, `IdSubscription`)  VALUES (".$IdMember.",".$IdPost.",now(),'".$Type."','members_groups_subscribed',".$rSubscribed->IdSubscription.")" ;
            $result = $this->dao->query($query);
                   
            if (!$result) {
               throw new PException('prepare_notification  for group for Thread=#'.$rPost->IdThread.' failed : for Type='.$Type);
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
    

    public function initThreads($page = 1) {
        
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

        $wherein="" ;
        $tabletagthread="" ;
        if ($this->tags) { // Does this mean if there is a filter on threads ?
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
				  `forums_threads`.`views`, 
				  `forums_threads`.`continent`,
				  `first`.`postid` AS `first_postid`, 
				  `first`.`authorid` AS `first_authorid`, 
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`postid` AS `last_postid`, 
				  `last`.`authorid` AS `last_authorid`, 
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
		$query .= "`first_user`.`handle` AS `first_author`,`last_user`.`handle` AS `last_author`,`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`," ;
		$query .= "`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`" ; 
		$query .= "FROM ".$tabletagthread."`forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)" ;
		$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
		$query .= "LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)" ;
		$query .= "LEFT JOIN `user` AS `first_user` ON (`first`.`authorid` = `first_user`.`id`)" ;
		$query .= "LEFT JOIN `user` AS `last_user` ON (`last`.`authorid` = `last_user`.`id`)" ;
		$query .= "LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)"; 
		$query .= "LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)" ; 
		$query .= "LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)" ;
		$query .= " WHERE 1 ".$wherethread." ORDER BY `stickyvalue` asc,`last_create_time` DESC LIMIT ".$from.", ".$this->THREADS_PER_PAGE ;


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
//            echo $row2->IdTag," " ;
                  $row->IdTag[]=$row2->IdTag ;
                  $row->IdName[]=$row2->IdName ;
                  $row->NbTags++ ;
            }
            $this->threads[] = $row;
        }
        
    } // end of initThreads
    
	/**
		This load the treads for a category or which does not belong to a category list
		first case : IdTagCategory is teh category the thread  must be declared in
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
				  `forums_threads`.`views`, 
				  `forums_threads`.`continent`,
				  `first`.`postid` AS `first_postid`, 
				  `first`.`authorid` AS `first_authorid`, 
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`postid` AS `last_postid`, 
				  `last`.`authorid` AS `last_authorid`, 
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
			$query .= "`first_user`.`handle` AS `first_author`,`last_user`.`handle` AS `last_author`,`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`," ;
			$query .= "`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`" ; 
			$query .= "FROM `tags_threads`,`forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)" ;
			$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
			$query .= "LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)" ;
			$query .= "LEFT JOIN `user` AS `first_user` ON (`first`.`authorid` = `first_user`.`id`)" ;
			$query .= "LEFT JOIN `user` AS `last_user` ON (`last`.`authorid` = `last_user`.`id`)" ;
			$query .= "LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)"; 
			$query .= "LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)" ; 
			$query .= "LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)" ;
			$query .= " where `tags_threads`.`IdThread`=`forums_threads`.`id` and  `tags_threads`.`IdTag` and  `tags_threads`.`IdTag` not in (".$NoInCategoryList.") ORDER BY `stickyvalue` asc,`last_create_time` DESC LIMIT 3" ;
		}
		else {
			$query = "SELECT SQL_CALC_FOUND_ROWS `forums_threads`.`threadid`,
		 		  `forums_threads`.`id` as IdThread, `forums_threads`.`title`, 
				  `forums_threads`.`IdTitle`, 
				  `forums_threads`.`IdGroup`, 
				  `forums_threads`.`replies`, 
				  `groups`.`Name` as `GroupName`, 
				  `forums_threads`.`views`, 
				  `forums_threads`.`continent`,
				  `first`.`postid` AS `first_postid`, 
				  `first`.`authorid` AS `first_authorid`, 
				  UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				  `last`.`postid` AS `last_postid`, 
				  `last`.`authorid` AS `last_authorid`, 
				  UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`," ;
			$query .= "`first_user`.`handle` AS `first_author`,`last_user`.`handle` AS `last_author`,`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`," ;
			$query .= "`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`" ; 
			$query .= "FROM `tags_threads`,`forums_threads` LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)" ;
			$query .= "LEFT JOIN `groups` ON (`groups`.`id` = `forums_threads`.`IdGroup`)" ;
			$query .= "LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)" ;
			$query .= "LEFT JOIN `user` AS `first_user` ON (`first`.`authorid` = `first_user`.`id`)" ;
			$query .= "LEFT JOIN `user` AS `last_user` ON (`last`.`authorid` = `last_user`.`id`)" ;
			$query .= "LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)"; 
			$query .= "LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)" ; 
			$query .= "LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)" ;
			$query .= " where `tags_threads`.`IdThread`=`forums_threads`.`id` and  `tags_threads`.`IdTag` and  `tags_threads`.`IdTag`=".$IdTagCategory." ORDER BY `stickyvalue` asc,`last_create_time` DESC LIMIT 3" ;
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

}


?>
