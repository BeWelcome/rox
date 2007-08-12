<?php
/**
 * signup model
 *
 * @package signup
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class Signup extends PAppModel
{
    /**
     * PERL regular expression for handles
     */

    /**
     * user
     * TODO: should get a more specific name - refactoring needed!
     */
    const HANDLE_PREGEXP = '%^[a-z][a-z0-9_]{3,}$%i';
    
    /**
     * FIXME: pay attention for non ISO-8859-x-characters, but build something
     * reasonable...
     */
    const HANDLE_PREGEXP_STREET = '%^[^°!"§\$\%\}\#\{<>_=]{1,}$%i';
    
    /**
     * FIXME: pay attention for non ISO-8859-x-characters, but build something
     * reasonable...
     */
    const HANDLE_PREGEXP_FIRSTNAME = '%^[^°!"§\$\%\}\#\{<>_=]{1,}$%i';

    /**
     * FIXME: pay attention for non ISO-8859-x-characters, but build something
     * reasonable...
     */
    const HANDLE_PREGEXP_LASTNAME = '%^[^°!"§\$\%\}\#\{<>_=]{1,}$%i';
    
    /**
     * TODO: check, if this is indeed the best form; I don't believe it (steinwinde, 2008-08-04)
     */
    const HANDLE_PREGEXP_EMAIL =
'^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$';

    /**
     * FIXME: use BW constant from config file instead of this one
     */
    const YOUNGEST_MEMBER = 18;
    
    const BW_TRUE = 'Yes';    // TODO: move to appropriate place, best replace by boolean
    const BW_FALSE = 'No';    // TODO: move to appropriate place, best replace by boolean

    /**
     * Constructor
     *
     * @param void
     */
    public function __construct()
    {
        parent::__construct();
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
        if (!$userId)
            return false;
        $keyDB = APP_User::getSetting($userId, 'regkey');
        if (!$keyDB)
            return false;
        if ($keyDB->value != $key)
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
        if (!$s) {
            throw new PException('Could not determine if email is in use!');
        }
        return $s->numRows();
    }

    public function find($str)
    {
    	if (!preg_match(self::HANDLE_PREGEXP, $str))
            return 'format';
        $query = '
SELECT
    u.`id`,
    u.`handle`,
    COUNT(f.`user_id_foreign`) AS `is_friend`
FROM `user` AS u
LEFT JOIN `user_friends` AS f ON
    f.`user_id_foreign` = '.(APP_User::login() ? (int)APP_User::login()->getId() : 'null').'
    AND
    f.`user_id_foreign_friend` = u.`id`
WHERE
    u.`handle` LIKE \'%'.$this->dao->escape($str).'%\'
GROUP BY u.`id`
ORDER BY u.`handle`
        ';
        $q = $this->dao->query($query);
        if (!$q->numRows())
            return false;
        return $q;
    }

    /**
     * returns handle as written in DB
     */
    public function getRealHandle($userId)
    {
        $s = $this->dao->query('SELECT `handle` FROM `user` WHERE `id` = '.(int)$userId);
        if ($s->numRows() != 1)
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
        if ($s->numRows() == 0)
            return false;
        if ($s->numRows() != 1)
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
        if (!$s) {
            throw new PException('Could not determine if handle is in use!');
        }
        if ($s->numRows() == 0)
            return false;
        if ($s->numRows() != 1)
            throw new PException('Data inconsistency');
        return $s->fetch(PDB::FETCH_OBJ)->id;
    }

    public function hasAvatar($userId)
    {
    	return $this->avatarDir->fileExists((int)$userId);
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
     * @see /htdocs/bw/signup.php
     * @param void
     */
    public function registerProcess()
    {
        $c = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            // TODO: is the security check for the HTTP parameters already done?!
            $vars =& PPostHandler::getVars();
            $errors = $this->checkRegistrationForm(&$vars);
            if (!(in_array('pw', $errors) || in_array('pwmismatch', $errors))) {
                if (substr_count($vars['password'], '*') != strlen($vars['password'])) {
                    // set encoded pw
                    $vars['passwordenc'] = MOD_user::passwordEncrypt($vars['password']);
                    $shadow = str_repeat('*', strlen($vars['password']));
                    $vars['password']  = $shadow;
                    $vars['passwordcheck'] = $shadow;
                }
            }
            
            if (count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }

            // =============
            // BW (1)
            $agehidden = Signup::BW_FALSE;
            if (!empty($vars['agehidden'])) {
                $agehidden = ($this->dao->escape($vars['agehidden']) === 'Yes' ? 
                        Signup::BW_TRUE : Signup::BW_FALSE); // TODO: always Yes?!
            }
            
            // FIXME: $genderhidden is missing in members table!
            $genderhidden = Signup::BW_TRUE;
            if (!empty($vars['genderhidden'])) {
                $genderhidden = ($this->dao->escape($vars['genderhidden']) === 'Yes' ?
                        Signup::BW_TRUE : Signup::BW_FALSE); // TODO: always Yes?!
            }
            
            // we believe we can skip GetStrParam completely

            // we suppose this has been executed:
            //ALTER TABLE members
            //MODIFY COLUMN `id` int( 11 ) NOT NULL COMMENT 'IdMember'
            
            // does this work? BW has: $_SESSION['IdMember'] = mysql_insert_id(); after INSERT
            $id = $this->dao->nextId('members');    // used both for BW and TB
            $_SESSION['IdMember'] = $id;            // BW compatibility code...

            
            $vars['city'] = 0;    // FIXME
            
            $query = '
INSERT INTO `members`
(`id`, `Username`, `IdCity`, `Gender`, `created`, `Password`, `BirthDate`, `HideBirthDate`)
VALUES
(
	'.$id.',
	\''.$this->dao->escape($vars['username']).'\',
	'.$this->dao->escape($vars['city']).',
	\''.$this->dao->escape($vars['gender']).'\',
	now(),
	password(\''.$this->dao->escape($vars['password']).'\'),
	\''. $this->dao->escape($vars['iso_date']).'\',
	\''.$agehidden.'\'
)';
            $s = $this->dao->query($query);
            
            // =============
            // TB
            $Auth = new MOD_user_Auth;
            $authId = $Auth->checkAuth('defaultUser');
            
            $query = '
INSERT INTO `user`
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`)
VALUES
(
    '.$id.',
    '.(int)$authId.',
    \''.$this->dao->escape($vars['username']).'\',
    \''.$this->dao->escape($vars['email']).'\',
    \''.$this->dao->escape($vars['passwordenc']).'\',
    0
)';
            $s = $this->dao->query($query);
            if (!$s->insertId()) {
                $vars['errors'] = array('inserror');
                return false;
            }
            $userId = $s->insertId();
            $key = PFunctions::randomString(16);
            // save register key
            if (!APP_User::addSetting($userId, 'regkey', $key)) {
                $vars['errors'] = array('inserror');
                return false;
            }
            // save lang
            if (!APP_User::addSetting($userId, 'lang', PVars::get()->lang)) {
                $vars['errors'] = array('inserror');
                return false;
            }

            // =============
            // BW (2)
            require '/htdocs/bw/lib/FunctionsCrypt.php'; 
            $encEmail = InsertInCrypted($this->dao->escape('username'), $_SESSION['IdMember'], "always");
            $query = '
UPDATE `members`
SET `Email`=' . $encEmail . '
WHERE `id`=' . $_SESSION['IdMember'];
            $s = $this->dao->query($query);
            // compute a nearly unique key for cross checking
            $key = createKey($this->dao->escape($vars['username']), $this->dao->escape($vars['lastname']),
                                $_SESSION['IdMember'], "registration");
        
            $query = '
INSERT INTO addresses 
(IdMember, IdCity, HouseNumber, StreetName, Zip, created, Explanation)
VALUES (' .
            $_SESSION['IdMember'] . ',' .
            $this->dao->escpape($vars['city']) . ',' .
            InsertInCrypted($this->dao->escpape($vars['housenumber'])) . ',' .
            InsertInCrypted($this->dao->escpape($vars['street'])) . ',' .
            InsertInCrypted($this->dao->escpape($vars['zip'])) . ',' .
            'now(),' .
            '"Signup addresse"
)';
            $s = $this->dao->query($query);
/*		
            $query = '
UPDATE members
SET
	FirstName=' . InsertInCrypted($this->dao->escpape($vars['firstname'])) . ',
    SecondName=' . InsertInCrypted($this->dao->escpape($vars['secondname'])) . ',
	LastName=' . InsertInCrypted($this->dao->escpape($vars['lastname'])) . ',
	ProfileSummary=' . InsertInMTrad($ProfileSummary) . '
WHERE id=' . $_SESSION['IdMember'];
            $s = $this->dao->query($query);


            // check, if e-mail already in use
            // FIXME:
            //$cryptedemail=LoadRow("select AdminCryptedValue from members,".$_SYSHCVOL['Crypted']."cryptedfields where members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.Email=".$_SYSHCVOL['Crypted']."cryptedfields.id and members.id=".$_SESSION['IdMember']);
            
            $query = '
SELECT Username, members.Status, members.id AS IdAllreadyMember
FROM members,' . $_SYSHCVOL['Crypted'] . 'cryptedfields
WHERE AdminCryptedValue=\'' . $cryptedemail->AdminCryptedValue . '\'
AND members.id=' . $_SYSHCVOL['Crypted'] . 'cryptedfields.IdMember
AND members.id!=' . $_SESSION['IdMember'];
            $s = $this->dao->query($query);

		if ($Feedback == "") $Feedback=$Feedback."\n"; 
		// check if this email already exist
		$cryptedemail=LoadRow("select AdminCryptedValue from members,".$_SYSHCVOL['Crypted']."cryptedfields where members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.Email=".$_SYSHCVOL['Crypted']."cryptedfields.id and members.id=".$_SESSION['IdMember']); 
		$str="select Username,members.Status,members.id as IdAllreadyMember from members,".$_SYSHCVOL['Crypted']."cryptedfields where AdminCryptedValue='".$cryptedemail->AdminCryptedValue."' and members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.id!=".$_SESSION['IdMember'];
		$qry=sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
			  if ($rr->IdAllreadyMember== $_SESSION['IdMember']) continue;
			  $Feedback.="<font color=red>Same Email as ".LinkWithUserName($rr->Username,$rr->Status)."</font>\n";
			  LogStr("Signup with same email than <b>".$rr->Username."</b> ","Signup");
		} 
		// end of check if email already exist

		// Checking of previous cookie was already there
		if (isset ($_COOKIE['MyBWusername'])) {
			  $Feedback.="<font color=red>Registration computer was already used by  ".LinkWithUserName($_COOKIE['MyBWusername'])."</font>\n";
			  LogStr("Signup on a computer previously used by  <b>".$_COOKIE['MyBWusername']."</b> ","Signup");
		} 		
		// End of previous cookie was already there
		
		if ($Feedback != "") {
			// feedbackcategory 3 = FeedbackAtSignup
			$str = "insert into feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . $Feedback . "',3,0,'closed by member'," . $_SESSION['IdLanguage'] . "," . $_SESSION['IdMember'] . ")";
			sql_query($str);
		}

		$subj = ww("SignupSubjRegistration", $_SYSHCVOL['SiteName']);
		$urltoconfirm = $_SYSHCVOL['SiteName'] . $_SYSHCVOL['MainDir'] . "main.php?action=confirmsignup&username=$Username&key=$key&id=" . abs(crc32(time())); // compute the link for confirming registration
		$text = ww("SignupTextRegistration", $FirstName, $SecondName, $LastName, $_SYSHCVOL['SiteName'], $urltoconfirm, $urltoconfirm);
		$defLanguage = $_SESSION['IdLanguage'];
		bw_mail($Email, $subj, $text, "", $_SYSHCVOL['SignupSenderMail'], $defLanguage, "html", "", "");

		// Notify volunteers that a new signupers come in
		$subj = "New member " . $Username . " from " . getcountryname($IdCountry) . " has signup";
		$text = " New signuper is " . $FirstName . " " . $LastName . "\n";
		$text .= "country=" .getcountryname($IdCountry)." city=".getcityname($IdCity)."\n";
		$text = " Signuper email is "  . $Email . "\n";
		$text .= "using language " . LanguageName($_SESSION['IdLanguage']) . "\n";
		$text .= stripslashes(GetStrParam("ProfileSummary"));
		$text .= "<br /><a href=\"http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir']."admin/adminaccepter.php\">go to accepting</a>\n";
		bw_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'], $subj, $text, "", $_SYSHCVOL['SignupSenderMail'], 0, "html", "", "");

		DisplaySignupResult(ww("SignupResutlTextConfimation", $Username, $Email));                                
                                
                                
*/          
                                
    // old TB code
                                
                                
            
            // finish
            $View = new SignupView($this);
            $View->registerMail($userId);
            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.'signup/register/finish';    // changed
        } else {
            PPostHandler::setCallback($c, __CLASS__, __FUNCTION__);
            return $c;
        }
    }

    public function settingsProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $vars =& PPostHandler::getVars();
            $errors = array();
            // password
            if (isset($vars['p']) && strlen($vars['p']) > 0) {
            	if (strlen($vars['p']) < 8) {
            		$errors[] = 'pwlength';
            	}
                if (!isset($vars['pc'])) {
                	$errors[] = 'pwc';
                } elseif ($vars['p'] != $vars['pc']) {
                	$errors[] = 'pwmismatch';
                }
            }
            if (count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }
            $messages = array();
            if (isset($vars['p']) && strlen($vars['p']) > 0) {
            	$pwenc = MOD_user::passwordEncrypt($vars['p']);
                $query = 'UPDATE `user` SET `pw` = \''.$pwenc.'\' WHERE `id` = '.(int)$User->getId();
                if ($this->dao->exec($query)) {
                	$messages[] = 'password_updated';
                } else {
                	$errors[] = 'password_not_updated';
                }
            }

            // Location
            // Check if the location already exists in our DB and add it if necessary
            if (isset($vars['geonameid']) && $vars['geonameid'] && $vars['latitude'] && $vars['longitude'] && $vars['geonamename'] && $vars['geonamecountrycode'] && $vars['admincode']) {
                $Blog = new Blog();
                $geoname_ok = $Blog->checkGeonamesCache($vars['geonameid'], $vars['latitude'], $vars['longitude'], $vars['geonamename'], $vars['geonamecountrycode'], $vars['admincode']);
            } else {
                $geoname_ok = false;
            }
            if ($geoname_ok) {
                $query = 'UPDATE `user` SET `location` = \''.$vars['geonameid'].'\' WHERE `id` = '.(int)$User->getId();
                if ($this->dao->exec($query)) {
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
            LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
            LEFT JOIN `geonames_countries` ON (`geonames_cache`.`fk_countrycode` = `geonames_countries`.`iso_alpha2`)
            WHERE `user`.`id` = '.(int)$userId);
        if ($s->numRows() != 1)
            return false;
        $location = $s->fetch(PDB::FETCH_OBJ);
        return $location;
    }

	public function getAllCountries() {
	    $query = "select SQL_CACHE `id`, `Name` from `countries` order by `Name`";
	    $s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve countries!');
		}
		$countries = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->id] = $row->Name;
		}
		return $countries;
	}
	
	private function checkRegistrationForm($vars)
    {
        $errors = array();
        // TODO:	FunctionsTools.GetStrParam() has been skipped; what have we missed,
        //			what is still to be done?

        // country
        if (empty($vars['country'])) {
            $errors[] = 'SignupErrorProvideCountry';
        }

        // city
        if (empty($vars['city'])) {
            $errors[] = 'SignupErrorProvideCity';
        }
        
        // (skipped:) region

        // (skippd:) housenumber
        // TODO: BW had an error SignupErrorProvideHouseNumber,
        // but I'm sure Germany has addresses without
        // housenumbers; start a discussion with people from the Geo team...

        // street
        if (empty($vars['street']) || !preg_match(User::HANDLE_PREGEXP_STREET, $vars['street'])) {
            $errors[] = 'SignupErrorProvideStreetName';
        }
        
        // (skipped:) zip
        // TODO: BW had an error SignupErrorProvideZip, but I'm sure Albania doesn't have zip codes!
        // start a discussion with people from the Geo team...

        // username
        if (!isset($vars['username']) || 
                !preg_match(User::HANDLE_PREGEXP, $vars['username']) ||
                strpos($vars['username'], 'xn--') !== false) {
            $errors[] = 'SignupErrorWrongUsername';
        } elseif ($this->handleInUse($vars['username'])) {
            $errors[] = 'SignupErrorUsernameAlreadyTaken';
        }
        
        // email
        if (!isset($vars['email']) || !PFunctions::isEmailAddress($vars['email']) ||
            !isset($vars['emailcheck'])) {
            $errors[] = 'SignupErrorInvalidEmail';
        } elseif (strcasecmp($vars['email'], $vars['emailcheck']) != 0) {
            $errors[] = 'SignupErrorEmailCheck';
        }
        
        // password
        if (!isset($vars['password']) || !isset($vars['passwordcheck']) ||
                !$vars['password'] || !$vars['passwordcheck'] ||
                strlen($vars['password']) < 8 || 
                strcmp($vars['password'], $vars['passwordcheck']) != 0
        ) {
            $errors[] = 'SignupErrorPasswordCheck';
        }
        
        // firstname, lastname
        if (empty($vars['firstname']) || !preg_match(User::HANDLE_PREGEXP_FIRSTNAME, $vars['firstname']) ||
            empty($vars['lastname']) || !preg_match(User::HANDLE_PREGEXP_LASTNAME, $vars['lastname'])
        ) {
            $errors[] = 'SignupErrorFullNameRequired';
        }
             
        // (skipped:) secondname

        // gender
        if (empty($vars['gender']) || ($vars['gender']!='female' && $vars['gender']!='male')) {
            $errors[] = 'SignupErrorProvideGender';
        }
        
        // birthyear
        $birthmonth = 12;
        if (!empty($vars['birthmonth'])) {
            $birthmonth = $vars['birthmonth'];
        }
        $birthday = 28;    // TODO: could sometimes be 29, 30, 31
        if (!empty($vars['birthday'])) {
            $birthday = $vars['birthday'];
        }
        if (empty($vars['birthyear']) || !checkdate($birthmonth, $birthday, $vars['birthyear'])) {
            $errors[] = 'SignupErrorBirthDate';
        } else {
            $vars['iso_date'] =  $vars['birthyear'] . "-" . $birthmonth . "-" . $birthday;
            if ($this->ageValue($vars['iso_date']) < User::YOUNGEST_MEMBER) {
                $errors[] = 'SignupErrorBirthDateToLow';
            }
        }
        
        // (skipped:) birthmonth

        // (skipped:) birthday

        // (skipped:) age hidden

        // terms
        if (empty($vars['terms']) || strcmp($vars['terms'], 'Yes') != 0) {
            $errors[] = 'SignupMustacceptTerms';    // TODO: looks like a wrong case in "Accept"
        }
        
        return $errors;
    }
    
    /** @return float (?!) value corresponding date
	 * 
	 * FIXME: copied from FunctionsTools.php fage_value; used in several places in BW website;
	 * where should this been moved to?
	 */
	function ageValue($dd) {
		$iDate = strtotime($dd);
		$age = (time() - $iDate) / (365 * 24 * 60 * 60);
		return ($age);
	}
	
	/**
	 * (stolen from FunctionsTools->CheckEmail)
	 * @return true , if e-mail address looks valid 
	 */
	private function checkEmail($email) {
		return ereg(HANDLE_PREGEXP_EMAIL, $email);
	}
	
	/**
	 * @see FunctionsTools.php (plain copy)
	 * compute a nearly unique key according to parameters
	 */ 
	private function createKey($s1, $s2, $IdMember = "", $ss = "default") {
	    $key = sprintf("%X", crc32($s1 . " " . $s2 . " " . $IdMember . "_" . $ss));
	    return ($key);
	}
	
}
?>