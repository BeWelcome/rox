<?php
/**
 * user model
 *
 * @package user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright( c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License( GPL)
 * @version $Id:user.model.php 217 2007-02-12 16:47:14Z marco $
 */
class User extends PAppModel
{
    
    public $avatarDir;
    /**
     * PERL regular expression for user handle
     */
    const HANDLE_PREGEXP = '%^[a-z][a-z0-9_]{3,}$%i';

    /**
     * Constructor
     *
     * @param void
     */
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap();
    }

    public function avatarProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
            if( !$User = APP_User::login())
                return false;
            $vars =& PPostHandler::getVars();
            if( !isset($_FILES['av']))
                return false;
            if( $_FILES['av']['error'] != UPLOAD_ERR_OK)
                return false;
            $img = new MOD_images_Image($_FILES['av']['tmp_name']);
            if( !$img->isImage())
                return false;
            $size = $img->getImageSize();
            $type = $size[2];
            // maybe this should be changed by configuration
            if( $type != IMAGETYPE_GIF && $type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG)
                return false;
            $newWidth = $size[0];
            $newHeight = $size[1];
            if( $newWidth > 100)
                $newWidth = 100;
            if( $newHeight > 100)
                $newHeight = 100;
            $img->createThumb($this->avatarDir->dirName(), $User->getId(), $newWidth, $newHeight, true);
            $img->createThumb($this->avatarDir->dirName(), $User->getId().'_xs', 50, 50, true);
        	return false;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function bootstrap()
    {
    	$this->avatarDir = new PDataDir('user/avatars');
    }

    /**
     * confirmation process
     *
     * @param string $handle
     * @param string $key
     * @return boolean
     */
    public function confirmRegister($handle, $key)
    {
        $userId = APP_User::userId($handle);
        if( !$userId)
            return false;
        $keyDB = APP_User::getSetting($userId, 'regkey');
        if( !$keyDB)
            return false;
        if( $keyDB->value != $key)
            return false;
        APP_User::activate($userId);
        return true;
    }

    /**
     * checks if e-mail address is in use
     *
     * @param string $email
     * @return boolean
     */
    public function emailInUse($email)
    {
        $query = 'SELECT `id` FROM `user` WHERE `email` = \''.$this->dao->escape(strtolower($email)).'\'';
        $s = $this->dao->query($query);
        if( !$s) {
            throw new PException('Could not determine if email is in use!');
        }
        return $s->numRows();
    }

    public function find($str)
    {
    	if( !preg_match(self::HANDLE_PREGEXP, $str))
            return 'format';
        $query = '
SELECT
    u.`id`,
    u.`handle`,
    COUNT(f.`user_id_foreign`) AS `is_friend`
FROM `user` AS u
LEFT JOIN `user_friends` AS f ON
    f.`user_id_foreign` = '.(APP_User::login() ?( int)APP_User::login()->getId() : 'null').'
    AND
    f.`user_id_foreign_friend` = u.`id`
WHERE
    u.`handle` LIKE \'%'.$this->dao->escape($str).'%\'
GROUP BY u.`id`
ORDER BY u.`handle`
        ';
        $q = $this->dao->query($query);
        if( !$q->numRows())
            return false;
        return $q;
    }

	
	/**
	* discontinued
	* please use build/link instead
	**/
		    // public function getFriends($userId)
		    // {
		    	// $query = '
// SELECT
		    // u.`id`,
		    // u.`handle`,
		    // u.`email`
// FROM `user_friends` AS f
// LEFT JOIN `user` AS u ON
		    // u.`id` = f.`user_id_foreign_friend`
// WHERE
		    // f.`user_id_foreign` = '.(int)$userId.'
// GROUP BY u.`id`
// ORDER BY u.`handle`
		        // ';
		        // $s = $this->dao->query($query);
		        // if( !$s->numRows())
		            // return false;
		        // return $s;
		    // }

    /**
     * returns handle as written in DB
     */
    public function getRealHandle($userId)
    {
        $s = $this->dao->query('SELECT `handle` FROM `user` WHERE `id` = '.(int)$userId);
        if( $s->numRows() != 1)
            return false;
        return $s->fetch(PDB::FETCH_OBJ)->handle;
    }

    /**
     * Get all user fields
     *
     * @param int $userId
     * @return stdClass
     */
    public function getUser($userId)
    {
        $query = '
SELECT
    `id`,
    `handle`,
    `auth_id`,
    `email`,
    `active`,
    `lastlogin`
FROM `user` WHERE
    `id` = '.(int)$userId.'
        ';
        $s = $this->dao->query($query);
        if( $s->numRows() == 0)
            return false;
        if( $s->numRows() != 1)
            throw new PException('Data inconsistency');
        return $s->fetch(PDB::FETCH_OBJ);
    }

    /**
     * returns "true" if handle is in use
     *
     * @param string $handle
     * @return boolean
     */
    public function handleInUse($handle)
    {
        $query = 'SELECT `id` FROM `user` WHERE `handle` = \''.$this->dao->escape(strtolower($handle)).'\'';
        $s = $this->dao->query($query);
        if( !$s) {
            throw new PException('Could not determine if handle is in use!');
        }
        if( $s->numRows() == 0)
            return false;
        if( $s->numRows() != 1)
            throw new PException('Data inconsistency');
        return $s->fetch(PDB::FETCH_OBJ)->id;
    }

    public function hasAvatar($userId)
    {
    	return $this->avatarDir->fileExists((int)$userId);
    }


    /**
     * Processing login
     *
     * This is a POST callback function
     *
     * @param void
     */
    public function loginProcess()
    {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
            $vars =& PPostHandler::getVars();
            $User = APP_User::login($vars['u'], $vars['p']);
            if( !$User || !$User->IsBWLoggedIn("Pending,NeedMore")) {
                $vars['errors'][] = 'not_logged_in';
            }
            $redirect_url = $vars['redirect'];
            header('Location: '.$redirect_url);
            PPHP::PExit();
            return false;
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function logoutProcess()
    {
        $c = PFunctions::hex2base64(sha1(__METHOD__));
        if ( PPostHandler::isHandling()) {
            $this->logout();
            return false;
        } else {
            PPostHandler::setCallback($c, __CLASS__, __FUNCTION__);
            return $c;
        }
    }
    
    public function logout()
    {
		$User = APP_User::get();
		$User->logout();
    }

    /**
     * Processing registration
     *
     * This is a POST callback function
     *
     * Sets following errors in POST-vars:
     * username   - general username fault
     * uinuse     - username already in use
     * email      - general email fault, email format error
     * einuse     - email in use
     * pw         - general password fault
     * pwmismatch - password mismatch
     * inserror   - error performing db insertion
     *
     * @param void
     */
    public function registerProcess()
    {
        $c = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
            $vars =& PPostHandler::getVars();
            $errors = array();
            // check username
            if( !isset($vars['u']) || !preg_match(User::HANDLE_PREGEXP, $vars['u']) || strpos($vars['u'], 'xn--') !== false) {
                $errors[] = 'username';
            } elseif( $this->handleInUse($vars['u'])) {
                $errors[] = 'uinuse';
            }
            // email
            if( !isset($vars['e']) || !PFunctions::isEmailAddress($vars['e'])) {
                $errors[] = 'email';
            } elseif( $this->emailInUse($vars['e'])) {
                $errors[] = 'einuse';
            }
            // password
            if( !isset($vars['p']) || !isset($vars['pc']) || !$vars['p'] || !$vars['pc'] || strlen($vars['p']) < 8) {
                $errors[] = 'pw';
            } elseif( $vars['p'] != $vars['pc']) {
                $errors[] = 'pwmismatch';
            } else {
                if( substr_count($vars['p'], '*') != strlen($vars['p'])) {
                    // set encoded pw
                    $vars['pwenc'] = MOD_user::passwordEncrypt($vars['p']);
                    $shadow = str_repeat('*', strlen($vars['p']));
                    $vars['p']  = $shadow;
                    $vars['pc'] = $shadow;
                }
            }
            if( count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }
            $Auth = new MOD_user_Auth;
            $authId = $Auth->checkAuth('defaultUser');
            $query = '
INSERT INTO `user`
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`)
VALUES
(
    '.$this->dao->nextId('user').',
    '.(int)$authId.',
    \''.$this->dao->escape($vars['u']).'\',
    \''.$this->dao->escape($vars['e']).'\',
    \''.$this->dao->escape($vars['pwenc']).'\',
    0
)';
            $s = $this->dao->query($query);
            if( !$s->insertId()) {
                $vars['errors'] = array('inserror');
                return false;
            }
            $userId = $s->insertId();
            $key = PFunctions::randomString(16);
            // save register key
            if( !APP_User::addSetting($userId, 'regkey', $key)) {
                $vars['errors'] = array('inserror');
                return false;
            }
            // save lang
            if( !APP_User::addSetting($userId, 'lang', PVars::get()->lang)) {
                $vars['errors'] = array('inserror');
                return false;
            }
            $View = new UserView($this);
            $View->registerMail($userId);
            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.'user/register/finish';
        } else {
            PPostHandler::setCallback($c, __CLASS__, __FUNCTION__);
            return $c;
        }
    }

    public function passwordProcess()
    {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
            if( !($User = APP_User::login()))
                return false;
            $vars =& PPostHandler::getVars();
            $errors = array();
            $messages = array();

            $query = "select id from members where id=" . $_SESSION["IdMember"] . " and PassWord=PASSWORD('" . trim($vars['OldPassword']) . "')";
            $qry = $this->dao->query($query);
            $rr = $qry->fetch(PDB::FETCH_OBJ);
            if (!$rr || !array_key_exists('id', $rr))
                $errors[] = 'ChangePasswordInvalidPasswordError';
            if( isset($vars['NewPassword']) && strlen($vars['NewPassword']) > 0) {
                if( strlen($vars['NewPassword']) < 8) {
                    $errors[] = 'ChangePasswordPasswordLengthError';
                }
                if(isset($vars['ConfirmPassword'])) {
                    if(strlen(trim($vars['ConfirmPassword'])) == 0) {
                        $errors[] = 'ChangePasswordConfirmPasswordError';
                    } elseif(trim($vars['NewPassword']) != trim($vars['ConfirmPassword'])) {
                        $errors[] = 'ChangePasswordMatchError';
                    }
                }
            }
            if( count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }
            if( isset($vars['NewPassword']) && strlen($vars['NewPassword']) > 0) {
//            	$pwenc = MOD_user::passwordEncrypt($vars['NewPassword']);
//              $query = 'UPDATE `user` SET `pw` = \''.$pwenc.'\' WHERE `id` = '.(int)$User->getId();
                $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($vars['NewPassword']).'\') WHERE `id` = '.$_SESSION['IdMember'];
                if( $this->dao->exec($query)) {
                    $messages[] = 'ChangePasswordUpdated';
                    $L = MOD_log::get();
                    $L->write("Password changed", "change password");
                } else {
                    $errors[] = 'ChangePasswordNotUpdated';
                }
            }

            $vars['errors'] = $errors;
            $vars['messages'] = $messages;
            return false;
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function settingsProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
            if( !$User = APP_User::login())
                return false;
            $vars =& PPostHandler::getVars();
            $errors = array();
            // password
            if( isset($vars['p']) && strlen($vars['p']) > 0) {
            	if( strlen($vars['p']) < 8) {
            		$errors[] = 'pwlength';
            	}
                if( !isset($vars['pc'])) {
                	$errors[] = 'pwc';
                } elseif( $vars['p'] != $vars['pc']) {
                	$errors[] = 'pwmismatch';
                }
            }
            if( count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }
            $messages = array();
            if( isset($vars['p']) && strlen($vars['p']) > 0) {
            	$pwenc = MOD_user::passwordEncrypt($vars['p']);
                $query = 'UPDATE `user` SET `pw` = \''.$pwenc.'\' WHERE `id` = '.(int)$User->getId();
                if( $this->dao->exec($query)) {
                	$messages[] = 'password_updated';
                } else {
                	$errors[] = 'password_not_updated';
                }
            }

            // Location
            // Check if the location already exists in our DB and add it if necessary
            if( isset($vars['geonameid']) && $vars['geonameid'] && $vars['latitude'] && $vars['longitude'] && $vars['geonamename'] && $vars['geonamecountrycode'] && $vars['admincode']) {
                $Blog = new Blog();
                $geoname_ok = $Blog->checkGeonamesCache($vars['geonameid'], $vars['latitude'], $vars['longitude'], $vars['geonamename'], $vars['geonamecountrycode'], $vars['admincode']);
            } else {
                $geoname_ok = false;
            }
            if( $geoname_ok) {
                $query = 'UPDATE `user` SET `location` = \''.$vars['geonameid'].'\' WHERE `id` = '.(int)$User->getId();
                if( $this->dao->exec($query)) {
                    $messages[] = 'location_updated';
                } else {
                    $errors[] = 'location_not_updated';
                }
            }

            $vars['errors'] = $errors;
            $vars['messages'] = $messages;
        	return false;
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }

    public function getLocation($userId) {
        $s = $this->dao->query('SELECT `user`.`location`, `geonames_cache`.`latitude`, `geonames_cache`.`longitude`, `geonames_cache`.`name` AS `location`, `geonames_countries`.`name` AS `country`, `geonames_cache`.`fk_countrycode` AS `code`, `geonames_cache`.`fk_admincode`
            FROM `user`
            LEFT JOIN `geonames_cache` ON( `user`.`location` = `geonames_cache`.`geonameid`)
            LEFT JOIN `geonames_countries` ON( `geonames_cache`.`fk_countrycode` = `geonames_countries`.`iso_alpha2`)
            WHERE `user`.`id` = '.(int)$userId);
        if( $s->numRows() != 1)
            return false;
        $location = $s->fetch(PDB::FETCH_OBJ);
        return $location;
    }

	public function getUserAuth() {
		$query = "SELECT `id`, `name` FROM `mod_user_auth`";
		$s = $this->dao->query($query);
		if( !$s) {
			throw new PException('Could not retrieve Groups!');
		}
		$groups = array();
		while( $row = $s->fetch(PDB::FETCH_OBJ)) {
			$groups[$row->id] = $row->name;
		}
		return $groups;
	}

	public function groupChangeProcess() {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if( PPostHandler::isHandling()) {
        	if( !($User = APP_User::login())) {
				throw new PException('Access should not have been possible');
			}
			if( $User->hasRight('groupchange@user')) {
			
				$vars =& PPostHandler::getVars();
				$query = sprintf("UPDATE `user` SET `auth_id` = '%d' WHERE `id` = '%d'", $vars['newgroup'], $vars['userid']);
				$this->dao->query($query);
			} else {
				throw new PException('Access should not have been possible');
			}
        } else {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
	}
    public function getPicture($username) {
        $s = $this->dao->query('SELECT `membersphotos`.`FilePath` as photo
FROM 	`members` left join `membersphotos` on `membersphotos`.`IdMember`=`members`.`id` and `membersphotos`.`SortOrder`=0 
WHERE `members`.`username`=\'' . $username . '\' and `members`.`Status`=\'Active\' 
limit 1');
        return $s->fetch(PDB::FETCH_OBJ)->photo;       
    }    

    
//------------------------------------------------------------------------------
// This function return the name of a region according to the IdRegion parameter
private function getregionname($IdRegion) {
    $words = new MOD_words();
	if (empty($IdRegion)) { // let consider that in some case members can have a city without region 
	   return($words->getFormatted("NoRegionDefined")) ;
	}
	$sQuery = "select  SQL_CACHE Name from regions where id=" . $IdRegion;
    $s = $this->dao->query($sQuery);
	$rr = $s->fetch(PDB::FETCH_OBJ);

	if (!isset($rr->Name)) {
	   return($words->getFormatted("NoRegionDefined")) ;
	}
	else {
	   return ($rr->Name);
	}
}

/* Profile: A member's groups */
public function getmembersgroups($IdMember) {
		$query = "select SQL_CACHE membersgroups.id as IdMemberShip,membersgroups.Comment as Comment,groups.Name as Name,groups.id as IdGroup from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=" . $IdMember;
		$s = $this->dao->query($query);
		if( !$s) {
			throw new PException('Could not retrieve Groups!');
		}
		$TGroups = array();
		while( $rr = $s->fetch(PDB::FETCH_OBJ)) {
			//$TGroups[$row->id] = $row->name;
	$rr->Location="" ;
	$str="select IdLocation,countries.Name as CountryName,regions.Name as RegionName,cities.Name as CityName from groups_locations ";
	$str.=" left join  countries on countries.id=IdLocation" ;
	$str.=" left join  regions on regions.id=IdLocation" ;
	$str.=" left join  cities on cities.id=IdLocation" ;
	$str=	$str."	where IdGroupMemberShip=".$rr->IdMemberShip ;
	$qry_rLocation=$this->dao->query($str) ;
	while( $rrLocation = $qry_rLocation->fetch(PDB::FETCH_OBJ)) {
		if ($rr->Location=="") {
			$rr->Location="(" ;
		}
		else {
			$rr->Location.="," ;
		}
		if (isset($rrLocation->CountryName)) {
			$rr->Location=$rr->Location.$rrLocation->CountryName ;
		}
		else if (isset($rrLocation->RegionName)) {
			$rr->Location=$rr->Location.$rrLocation->RegionName ;
		}
		else if (isset($rrLocation->CityName)) {
			$rr->Location=$rr->Location.$rrLocation->CityName ;
		}
	}
	if ($rr->Location!="") {
		$rr->Location.=")" ;
	}

            array_push($TGroups, $rr);
		}
		return $TGroups;
}
/* Profile: A member's relations */
public function getmembersrelations($IdMember) {
		$query = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender,members.id as IdMember from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and members.Status='Active'";
		$s = $this->dao->query($query);
		if( !$s) {
			return false;
		}
        $Relations = array ();
		while( $row = $s->fetch(PDB::FETCH_OBJ)) {
            if ((!($User = APP_User::login())) and (hasPublicProfile($_userId = $rr->IdMember))) continue; // Skip non public profile is is not logged
			//$TGroups[$row->id] = $row->name;
            array_push($Relations, $row);
		}
		return $Relations;
}
    
public function prepareProfileContent($handle,$m) {

$IdMember = APP_User::memberId($handle);
$photorank=GetParam("photorank",0);
switch (GetParam("action")) {
	case "previouspicture" :
		$photorank--;
		if ($photorank < 0) {
	  	    $rr=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " order by SortOrder desc limit 1");
			if (isset($rr->SortOrder)) $photorank = $rr->SortOrder;
			else $photorank=0;
		}
		break;
	case "nextpicture" :
		$photorank++;
		break;
	case "logout" :
		Logout();
		exit (0);
}


// Load phone
if ($m->HomePhoneNumber > 0) {
	$m->DisplayHomePhoneNumber = PublicReadCrypted($m->HomePhoneNumber, $words->getFormatted("Hidden"));
}
if ($m->CellPhoneNumber > 0) {
	$m->DisplayCellPhoneNumber = PublicReadCrypted($m->CellPhoneNumber, $words->getFormatted("Hidden"));
}
if ($m->WorkPhoneNumber > 0) {
	$m->DisplayWorkPhoneNumber = PublicReadCrypted($m->WorkPhoneNumber, $words->getFormatted("Hidden"));
}

if ($m->Restrictions == "") {
	$m->TabRestrictions = array ();
} else {
	$m->TabRestrictions = explode(",", $m->Restrictions);
}

if ($m->OtherRestrictions > 0)
	$m->OtherRestrictions = FindTrad($m->OtherRestrictions,true);
else
	$m->OtherRestrictions = "";

if (IsLoggedIn()) {
	// check if the member is in mycontacts
	$rr=LoadRow("select SQL_CACHE * from mycontacts where IdMember=".$_SESSION["IdMember"]." and IdContact=".$IdMember);
	if (isset($rr->id)) {
	   $m->IdContact=$rr->id; // The note id
	}	
	else {
	   $m->IdContact=0; // there is no note
	}	

	// check if wether this profile has a special realtion
	$rr=LoadRow("select SQL_CACHE * from specialrelations where IdOwner=".$_SESSION["IdMember"]." and IdRelation=".$IdMember);
	if (isset($rr->IdRelation)) {
	   $m->IdRelation=$rr->IdRelation; // The note id
	}	
	else {
	   $m->IdRelation=0; // there is no note
	}	
}
	
// Load the language the members nows
$TLanguages = array ();
$str = "SELECT SQL_CACHE memberslanguageslevel.IdLanguage AS IdLanguage,languages.Name AS Name, " .
		"memberslanguageslevel.Level AS Level FROM memberslanguageslevel,languages " .
		"WHERE memberslanguageslevel.IdMember=" . $m->id . 
		" AND memberslanguageslevel.IdLanguage=languages.id AND memberslanguageslevel.Level != 'DontKnow'";

$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	$rr->Level=$words->getFormatted("LanguageLevel_".$rr->Level);   
	array_push($TLanguages, $rr);
}
$m->TLanguages = $TLanguages;

// Make some translation to have blankstring in case records are empty
$m->ILiveWith = FindTrad($m->ILiveWith,true);
$m->MaxLenghtOfStay = FindTrad($m->MaxLenghtOfStay,true);
$m->MotivationForHospitality = FindTrad($m->MotivationForHospitality,true);
$m->Offer = FindTrad($m->Offer,true);
$m->Organizations = FindTrad($m->Organizations,true);
$m->AdditionalAccomodationInfo = FindTrad($m->AdditionalAccomodationInfo,true);
$m->InformationToGuest = FindTrad($m->InformationToGuest,true);
$m->Hobbies = FindTrad($m->Hobbies,true);
$m->Books = FindTrad($m->Books,true);
$m->Music = FindTrad($m->Music,true);
$m->Movies = FindTrad($m->Movies,true);
$m->PleaseBring = FindTrad($m->PleaseBring,true);
$m->OfferGuests = FindTrad($m->OfferGuests,true);
$m->OfferHosts = FindTrad($m->OfferHosts,true);
$m->PublicTransport = FindTrad($m->PublicTransport,true);
$m->PastTrips = FindTrad($m->PastTrips,true);
$m->PlannedTrips = FindTrad($m->PlannedTrips,true);

if (stristr($m->WebSite,"http://") === FALSE &&
	stristr($m->WebSite,"https://") === FALSE &&
	strlen(trim($m->WebSite))>0)
	$m->WebSite = "http://".$m->WebSite;
	
// see if the visit of the profile need to be logged
if (IsLoggedIn() and 
	($IdMember != $_SESSION["IdMember"]) and 
	($_SESSION["Status"] != "ActiveHidden")) { // don't log ActiveHidden visits or visit on self profile

	$str="replace into profilesvisits(IdMember,IdVisitor,updated) values(".$m->id.",".$_SESSION["IdMember"].",now())" ;
	sql_query($str);
}
return $m;
}


    /**
     * Get all user fields
     *
     * @param int $userId
     * @return stdClass
     */
    public function prepareProfile($handle,$wherestatus="",$photorank=0)
    {
    $words = new MOD_words();

    require_once SCRIPT_BASE.'htdocs/bw/lib/FunctionsTools.php';
    require_once SCRIPT_BASE.'htdocs/bw/lib/FunctionsCrypt.php';
    require_once SCRIPT_BASE.'htdocs/bw/lib/bwdb.php';
    function ww($wordcode) {    $words = new MOD_words(); $words->getFormatted($wordcode);}
    $IdMember = APP_User::memberId($handle);
	if ($wherestatus == "")
		$wherestatus = " and Status='Active'";

	//if (HasRight("Accepter")) { // accepter right allow for reading member who are not yet active
  	//   	$wherestatus = "";
	//}
    
	$sQuery="select SQL_CACHE * from members where id=" . $IdMember . $wherestatus ;

	// Try to load the member
    $s = $this->dao->query($sQuery);

	//if (!isset ($m->id)) {
	//    $errcode = "ErrorNoSuchMember";
	//	DisplayError(ww($errcode, $IdMember));
		//		bw_error("ErrorMessage=".$ErrorMessage);
	//	exit (0);
	//}
 
    if( $s->numRows() == 0)
        return false;
    if( $s->numRows() != 1)
        throw new PException('Data inconsistency');
    $m = $s->fetch(PDB::FETCH_OBJ);

	// Load geography
	if ($m->IdCity > 0) {
        $s = ($sQuery);
	    $s = $this->dao->query("select SQL_CACHE cities.IdCountry as IdCountry,cities.Name as cityname,cities.id as IdCity,countries.Name as countryname,IdRegion,isoalpha2 from cities,countries where countries.id=cities.IdCountry and cities.id=" . $m->IdCity);
        $rWhere = $s->fetch(PDB::FETCH_OBJ);
		$m->cityname = $rWhere->cityname;
		$m->countryname = $rWhere->countryname;

		$m->regionname=$this->getregionname($rWhere->IdRegion) ;
		$m->IdRegion=$rWhere->IdRegion ;
		$m->IsoCountry=$rWhere->isoalpha2 ;
		$m->IdCountry=$rWhere->IdCountry ;
        }

	// Load nbcomments nbtrust
	$m->NbTrust = 0;
	$m->NbComment = 0;
    
    $s = $this->dao->query("select SQL_CACHE count(*) as cnt from comments where IdToMember=" . $m->id . " and Quality='Good'");
    $rr = $s->fetch(PDB::FETCH_OBJ);
	if (isset ($rr->cnt))
	    $m->NbTrust = $rr->cnt;

    $s = $this->dao->query("select SQL_CACHE count(*) as cnt from comments where IdToMember=" . $m->id);
    $rr = $s->fetch(PDB::FETCH_OBJ);
	if (isset ($rr->cnt))
	    $m->NbComment = $rr->cnt;

	if (($m->LastLogin == "11/30/99 00:00:00")or($m->LastLogin == "00/00/00 00:00:00"))
	    $m->LastLogin = $words->getFormatted("NeverLog");
	else
		$m->LastLogin = PDate::intlDate($m->LastLogin,"%d/%m/%y %Hh%M",PVars::get()->lang);

	// Load Age
	$m->age = fage($m->BirthDate, $m->HideBirthDate);

    // function fFullName return the FullName of the member with a special layout if some fields are crypted 
//    function fFullName($m) {
 //   	return (PublicReadCrypted($m->FirstName, "*") . " " . PublicReadCrypted($m->SecondName, "*") . " " . PublicReadCrypted($m->LastName, "*"));
 //   } // end of fFullName
	$m->FullName = fFullName($m);

	// Load Address data
	$rr = LoadRow("select SQL_CACHE * from addresses where IdMember=" . $m->id, " and Rank=0 limit 1");
	if (isset ($rr->id)) {
	    $m->Address = PublicReadCrypted($rr->HouseNumber, "*") . " " . PublicReadCrypted($rr->StreetName, $words->getFormatted("MemberDontShowStreetName"));
		$m->Zip = PublicReadCrypted($rr->Zip, $words->getFormatted("ZipIsCrypted"));
		$m->IdGettingThere = FindTrad($rr->IdGettingThere);
	}
	
	$m->Trad = MOD_user::getTranslations($IdMember);
	$m->CountTrad = count($m->Trad);
	
	$Relations = array ();
	$m->IdContact=0; // there is no note
	$m->IdRelation=0; // there is no special relation
	if (IsLoggedIn()) {
	   // Try to load specialrelations and caracteristics belong to
	   $str = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender,members.id as IdMember from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and members.Status='Active'";
	   $qry = mysql_query($str);
	   while ($rr = mysql_fetch_object($qry)) {
		  if ((!IsLoggedIn()) and (!IsPublic($rr->IdMember))) continue; // Skip non public profile is is not logged

		  $rr->Comment=FindTrad($rr->Comment,true);
   	  $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdRelation . " and SortOrder=0");
		  if (isset($photo->FilePath)) $rr->photo=$photo->FilePath; 
		  array_push($Relations, $rr);
	   }
	   // check if the member is in mycontacts
	   $rr=LoadRow("select SQL_CACHE * from mycontacts where IdMember=".$_SESSION["IdMember"]." and IdContact=".$IdMember);
	   if (isset($rr->id)) {
	   	  $m->IdContact=$rr->id; // The note id
	   }	

	   // check if wether this profile has a special relation with teh current member
	   $rr=LoadRow("select SQL_CACHE * from specialrelations where IdOwner=".$_SESSION["IdMember"]." and IdRelation=".$IdMember);
	   if (isset($rr->IdRelation)) {
	   	  $m->IdRelation=$rr->IdRelation; // there is no special relation
	   }	
	}
	$m->Relations=$Relations;

  return($m);
    
    
    }    

function prepareProfileHeader($handle,$wherestatus="",$photorank=0) {
    $R = MOD_right::get();
    require_once SCRIPT_BASE.'htdocs/bw/lib/FunctionsTools_short.php';
    //require_once SCRIPT_BASE.'htdocs/bw/lib/FunctionsCrypt.php';
    //require_once SCRIPT_BASE.'htdocs/bw/lib/bwdb.php';
    function ww($wordcode) {    $words = new MOD_words(); $words->getFormatted($wordcode);}
    $IdMember = APP_User::memberId($handle);
    $words = new MOD_words();
    //function ww($wordcode) {    $words = new MOD_words(); $words->getFormatted($wordcode);}
	if ($wherestatus == "")
		$wherestatus = " and Status='Active'";

	if ($R->hasRight("Accepter")) { // accepter right allow for reading member who are not yet active
  	   	$wherestatus = "";
	}

	$sQuery = "select SQL_CACHE * from members where id=" . $IdMember . $wherestatus;
    $s = $this->dao->query($sQuery);
	$m = $s->fetch(PDB::FETCH_OBJ);

	// Try to load the member
	//$m=LoadRow($sQuery);

	if (!isset ($m->id)) {
	    $errcode = "ErrorNoSuchMember";
		DisplayError($words->getFormatted($errcode, $IdMember));
		//		bw_error("ErrorMessage=".$ErrorMessage);
		exit (0);
	}

	// manage picture photorank (swithing from one picture to the other)

	$m->profilewarning = "";
	if ($m->Status != "Active") {
	    $m->profilewarning = "WARNING the status of " . $m->Username . " is set to " . $m->Status;
	}

	// Load geography
	if ($m->IdCity > 0) {

	    $s = $this->dao->query("select SQL_CACHE cities.IdCountry as IdCountry,cities.Name as cityname,cities.id as IdCity,countries.Name as countryname,IdRegion,isoalpha2 from cities,countries where countries.id=cities.IdCountry and cities.id=" . $m->IdCity);
	    $rWhere = $s->fetch(PDB::FETCH_OBJ);
		$m->cityname = $rWhere->cityname;
		$m->countryname = $rWhere->countryname;

		$m->regionname= $this->getregionname($rWhere->IdRegion) ;
		$m->IdRegion=$rWhere->IdRegion ;
		$m->IsoCountry=$rWhere->isoalpha2 ;
		$m->IdCountry=$rWhere->IdCountry ;
        }

	// Load nbcomments nbtrust
	$m->NbTrust = 0;
	$m->NbComment = 0;
    
	$sQuery = "select SQL_CACHE count(*) as cnt from comments where IdToMember=" . $m->id . " and Quality='Good'";
    $s = $this->dao->query($sQuery);
	$rr = $s->fetch(PDB::FETCH_OBJ);
	if (isset ($rr->cnt))
	    $m->NbTrust = $rr->cnt;
	$sQuery = "select SQL_CACHE count(*) as cnt from comments where IdToMember=" . $m->id;
    $s = $this->dao->query($sQuery);
	$rr = $s->fetch(PDB::FETCH_OBJ);
	if (isset ($rr->cnt))
	    $m->NbComment = $rr->cnt;

	if (($m->LastLogin == "11/30/99 00:00:00")or($m->LastLogin == "00/00/00 00:00:00"))
	    $m->LastLogin = $words->getFormatted("NeverLog");
	else
		$m->LastLogin = localdate($m->LastLogin,"%d/%m/%y %Hh%M");

	// Load Age
	$m->age = fage($m->BirthDate, $m->HideBirthDate);

	// Load full name
	$m->FullName = fFullName($m);

	// Load Address data
	$rr = LoadRow("select SQL_CACHE * from addresses where IdMember=" . $m->id, " and Rank=0 limit 1");
	if (isset ($rr->id)) {
	    $m->Address = PublicReadCrypted($rr->HouseNumber, "*") . " " . PublicReadCrypted($rr->StreetName, $words->getFormatted("MemberDontShowStreetName"));
		$m->Zip = PublicReadCrypted($rr->Zip, $words->getFormatted("ZipIsCrypted"));
		$m->IdGettingThere = FindTrad($rr->IdGettingThere);
	}
	
	$m->Trad = MOD_user::getTranslations($IdMember);
	$m->CountTrad = count($m->Trad);
	
	$Relations = array ();
	$m->IdContact=0; // there is no note
	$m->IdRelation=0; // there is no special relation
	if (IsLoggedIn()) {
	   // Try to load specialrelations and caracteristics belong to
	   $str = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender,members.id as IdMember from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and members.Status='Active'";
	   $qry = mysql_query($str);
	   while ($rr = mysql_fetch_object($qry)) {
		  if ((!IsLoggedIn()) and (!IsPublic($rr->IdMember))) continue; // Skip non public profile is is not logged

		  $rr->Comment=FindTrad($rr->Comment,true);
   	  $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdRelation . " and SortOrder=0");
		  if (isset($photo->FilePath)) {
				 $rr->photo=$photo->FilePath;
			}
			else {
				 $rr->photo="" ;
			} 
		  array_push($Relations, $rr);
	   }
	   // check if the member is in mycontacts
	   $rr=LoadRow("select SQL_CACHE * from mycontacts where IdMember=".$_SESSION["IdMember"]." and IdContact=".$IdMember);
	   if (isset($rr->id)) {
	   	  $m->IdContact=$rr->id; // The note id
	   }	

	   // check if wether this profile has a special relation with teh current member
	   $rr=LoadRow("select SQL_CACHE * from specialrelations where IdOwner=".$_SESSION["IdMember"]." and IdRelation=".$IdMember);
	   if (isset($rr->IdRelation)) {
	   	  $m->IdRelation=$rr->IdRelation; // there is no special relation
	   }	
	}
	$m->Relations=$Relations;
	
	// Check the number of Forum¨Post this member has made
	// todo in future, test it according to reader visibility for this member posts
	$rr=LoadRow("select count(*) as cnt from forums_posts where IdWriter=".$IdMember) ;
	$m->NbForumPosts=$rr->cnt ;

  return($m);
} // end of prepareProfileHeader
    
}
?>
