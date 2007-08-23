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
    const HANDLE_PREGEXP_HOUSENUMBER = '%^[^°!"§\$\%\}\#\{<>_=]{1,}$%i';
    
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
    
    // TODO: obviously this should be standardized
    const BW_TRUE = 'Yes';
    const BW_FALSE = 'No';
    const BW_TRUE_A = 'True';
    const BW_FALSE_A = 'False';
    
    /**
     * Constructor
     *
     * @param void
     */
    public function __construct($data = false)
    {
        parent::__construct($data);
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
/*
        $query = '
SELECT `id`
FROM `user`
WHERE `email` = \''.$this->dao->escape(strtolower($email)).'\'';
*/
        $query = '
SELECT `id`
FROM `members`
WHERE `Email` = \'' . $this->dao->escape(strtolower($email)).'\'';
        
        $s = $this->dao->query($query);
        if (!$s) {    // TODO: always integrate this check?
            throw new PException('Could not determine if email is in use!');
        }
        return $s->numRows();
    }
    
    public function takeCareForNonUniqueEmailAddress($email)
    {
        /*
		$cryptedemail=LoadRow("select AdminCryptedValue from members,".$_SYSHCVOL['Crypted']."cryptedfields where members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.Email=".$_SYSHCVOL['Crypted']."cryptedfields.id and members.id=".$_SESSION['IdMember']); 
		$str="select Username,members.Status,members.id as IdAllreadyMember from members,".$_SYSHCVOL['Crypted']."cryptedfields where AdminCryptedValue='".$cryptedemail->AdminCryptedValue."' and members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.id!=".$_SESSION['IdMember'];
		$qry=sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
			  if ($rr->IdAllreadyMember== $_SESSION['IdMember']) continue;
			  $Feedback.="<font color=red>Same Email as ".LinkWithUserName($rr->Username,$rr->Status)."</font>\n";
			  LogStr("Signup with same email than <b>".$rr->Username."</b> ","Signup");
		} 
		// end of check if email already exist*/
    }
    
    /**
     * check, if computer has previously been used by BW member
     * 
     * TODO: I wonder, why BW people care for my box; member Bin L.
     * has been logged in before at this computer, - should be nothing to them.
     */
    public function takeCareForComputerUsedByBWMember(&$vars)
    {
        if (isset($_COOKIE['MyBWusername'])) {
            $vars['feedback'] .= ' Registration computer was already used by ' . 
                LinkWithUserName($_COOKIE['MyBWusername']);	// FIXME
				$_COOKIE['MyBWusername'];
			// LogStr("Signup on a computer previously used by ".
			// $_COOKIE['MyBWusername'], "Signup"); // TODO
        }
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

    /**
     * Processing registration
     *
     * This is a POST callback function
     *
     * @see /htdocs/bw/signup.php
     * @param void
     */
    public function registerProcess()
    {
        $c = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling()) {
            $vars =& PPostHandler::getVars();
            $errors = $this->checkRegistrationForm($vars);
            
            if (count($errors) > 0) {
                $vars['errors'] = $errors;
                return false;
            }
            
            $this->polishFormValues($vars);
            
            $idTB = $this->registerTBMember($vars);
            if (!$idTB) {
                return false;
            }
            
            $id = $this->registerBWMember($vars);
            $_SESSION['IdMember'] = $id;
            
            $this->takeCareForNonUniqueEmailAddress(
                        $this->dao->escape($vars['email']));

            $this->takeCareForComputerUsedByBWMember($vars);
            
            $this->writeFeedback($vars['feedback']);
                                    
            $View = new SignupView($this);
            // TODO: BW 2007-08-19: $_SYSHCVOL['EmailDomainName']
            define('DOMAIN_MESSAGE_ID', 'bewelcome.org');    // TODO: config
            $View->registerMail($idTB);
            $View->signupTeamMail($vars);
            // PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.'signup/register/finish';
        } else {
            PPostHandler::setCallback($c, __CLASS__, __FUNCTION__);
            return $c;
        }
    }
    
    /**
     * TODO: use a language column with character codes
     * instead of integers (!?)
     */
    public function writeFeedback($feedback)
    {
        if (!empty($feedback)) {
            define('FEEDBACK_CATEGORY_SIGNUP', 3);
            $lang = $this->determineLangInteger();
            $query = '
INSERT INTO
	`feedbacks`
(
	`created`,
	`Discussion`,
	`IdFeedbackCategory`,
	`IdVolunteer`,
	`Status`,
	`IdLanguage`,
	`IdMember`
)
VALUES(
	now(),
	\'' . $feedback . '\',
	' . FEEDBACK_CATEGORY_SIGNUP . ',
	0,
	\'closed by member\',
	' . $lang . ',
	' . $_SESSION['IdMember'] . '
)';
            $s = $this->dao->query($query);
        }
    }
    
    private function determineLangInteger()
    {
        $query = '
SELECT `id`
FROM `languages`
WHERE `ShortCode` = \'' . $_SESSION['lang'] . '\'';
        $q = $this->dao->query($query);
        $result = $q->fetch(PDB::FETCH_OBJ);
        return $result->id;
    }   
    
    /**
     * 
     * This has NOT been executed:
     * ALTER TABLE members
     * MODIFY COLUMN `id` int( 11 ) NOT NULL COMMENT 'IdMember'
     * As a result, we do NOT use
     * '.$this->dao->nextId('members').',
     * 
     */
    public function registerBWMember($vars)
    {
        // FIXME: genderhidden is missing in members table

        // ********************************************************************
        // members
        // ********************************************************************
        $query = '
INSERT INTO `members`
(
	`Username`,
	`IdCity`,
	`Gender`,
	`created`,
	`Password`,
	`BirthDate`,
	`HideBirthDate`
)
VALUES
(
	\'' . $vars['username'] . '\',
	' . $vars['city_id'] . ',
	\'' . $vars['gender'] . '\',
	now(),
	password(\'' . $vars['passwordenc'] . '\'),
	\'' . $vars['iso_date'] . '\',
	\'' . $vars['agehidden'] . '\'
)';
        $members = $this->dao->query($query);
        $memberID = $members->insertId(); // better $_SESSION['IdMember']?
        
        // ********************************************************************
        // e-mail/members
        // ********************************************************************
        $cryptedfieldsEmail = $this->insertData($vars['email'], $memberID);
        $query = '
UPDATE `members`
SET `Email`=' . $cryptedfieldsEmail . '
WHERE `id` = ' . $memberID;
        $this->dao->query($query);
        
        // ********************************************************************
        // address/addresses
        // ********************************************************************
        $cryptedfieldsHousenumber = $this->insertData($vars['housenumber'], $memberID);
        $cryptedfieldsStreet = $this->insertData($vars['street'], $memberID);
        $cryptedfieldsZip = $this->insertData($vars['zip'], $memberID);

        $query = '
INSERT INTO addresses
(
	`IdMember`,
	`IdCity`,
	`HouseNumber`,
	`StreetName`,
	`Zip`,
	`created`,
	`Explanation`
)
VALUES
(
	' . $memberID . ',
	' . $vars['city_id'] . ',
    ' . $cryptedfieldsHousenumber . ',
	' . $cryptedfieldsStreet . ',
	' . $cryptedfieldsZip . ',
	now(),
	"Signup addresse")';
        $addresses = $this->dao->query($query);

        return $memberID;
    }
    
    /**
     * TODO: move to dedicated module
     */
    private function insertData($stuff, $memberID) {
        $query = '
INSERT INTO `cryptedfields`
(
	`AdminCryptedValue`,
	`MemberCryptedValue`,
	`IsCrypted`,
	`IdMember`,
	`ToDo`
)
VALUES
(
	\'' . $stuff . '\',
	\'' . $stuff . '\',
	\'not crypted\',
	' . $memberID . ',
	\'nothing\'
)';
        $cryptedfields = $this->dao->query($query);
        return $cryptedfields->insertId();
    }
    
    /**
     * $vars is required to contain an e-mail
     */
    public function polishFormValues(&$vars)
    {
        if (!(isset($vars['agehidden']) &&
                strcmp($vars['agehidden'], Signup::BW_TRUE) == 0)) {
            $vars['agehidden'] = Signup::BW_FALSE;
        }
        if (!(isset($vars['genderhidden']) &&
                strcmp($vars['genderhidden'], Signup::BW_TRUE) != 0)) {
            $vars['genderhidden'] = Signup::BW_FALSE;
        }
        
        // $vars['city'] = 
        // MOD_geo::get()->getCityID($this->dao->escape($vars['city']));
        
        // TODO: this is not done so in BW 2007-08-14!
        $vars['email'] = strtolower($vars['email']);
        
        $escapeList = array('username', 'email', 'passwordenc', 'gender',
                            'feedback', 'housenumber', 'street', 'zip');
        foreach($escapeList as $formfield) {
            if(!empty($vars[$formfield])) {  // e.g. feedback...
                $vars[$formfield] = $this->dao->escape($vars[$formfield]);
            }
        }
    }

    public function registerTBMember($vars)
    {
        $Auth = new MOD_bw_user_Auth;
        $authId = $Auth->checkAuth('defaultUser');
        // TODO: we shouldn't use mysql's password(),
        // but for now it's to get nearer to the BW style
        $query = '
INSERT INTO `user`
(`id`, `auth_id`, `handle`, `email`, `pw`, `active`)
VALUES
(
    '.$this->dao->nextId('user').',
    '.(int)$authId.',
    \'' . $vars['username'] . '\',
    \'' . $vars['email'] . '\',
	password(\'' . $vars['passwordenc'] . '\'),
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
        
        return true;
    }
    
    /**
     * Check form values of registration form,
     * do some cautious corrections
     *
     * @param unknown_type $vars
     * @return unknown
     */
	public function checkRegistrationForm(&$vars)
    {
        $errors = array();

        // country
        if (empty($vars['country'])) {
            $errors[] = 'SignupErrorProvideCountry';
        }

        // city
        // FIXME: the current technique does NOT work
        // for cities, which have a non unique name in their country
        if (empty($vars['city']) && empty($vars['city_id'])) {
            $errors[] = 'SignupErrorProvideCity';
        } else {
            
            $bingo = false; 
            
            // if we get both city and city_id, city wins
            // if we only get city_id, we're at the end of our dreams :)
            if (!empty($vars['city']) && !empty($vars['city_id'])) {
                unset($vars['city_id']);
            } else if (!empty($vars['city_id'])) {
                $bingo = true;
                $vars['city'] = MOD_geo::get()->getCityName($vars['city_id']);
            }
            
            if (!$bingo) {
	            $cities = array();
		        $geo = MOD_geo::get();
		        $cities = 
		            MOD_geo::get()->guessCity($vars['country'], $vars['city']);
		        if (count($cities) == 0) {
		            // error_log("Hit 0 city\n", 3, "/tmp/my.log");
		            // TODO: probably inappropriate error message
		            $errors[] = 'SignupErrorProvideCity';
		            unset($vars['city_id']);
		        } else if (count($cities) == 1) {
		            // error_log("Hit 1 city\n", 3, "/tmp/my.log");
		            $tempArray = current($cities);   // bingo
		            $vars['city_id'] = key($tempArray);    // used for INSERT
		            $vars['city'] = $tempArray[$vars['city_id']];
		        } else {
		            // error_log("Hit many cities\n", 3, "/tmp/my.log");
		            $vars['city'] = $cities;      // array of arrays
		            // TODO: probably inappropriate error message
		            $errors[] = 'SignupErrorProvideCity';
		            unset($vars['city_id']);
		        }
            }
        }
        
        // (skipped:) region

        // housenumber
        if (!isset($vars['housenumber']) || 
            !preg_match(Signup::HANDLE_PREGEXP_HOUSENUMBER, $vars['housenumber'])) {
            $errors[] = 'SignupErrorProvideHouseNumber';
        }

        // street
        if (empty($vars['street']) || 
            !preg_match(Signup::HANDLE_PREGEXP_STREET, $vars['street'])) {
            $errors[] = 'SignupErrorProvideStreetName';
        }
        
        // zip
        if (!isset($vars['zip'])) {
            $errors[] = 'SignupErrorProvideZip';
        }

        // username
        if (!isset($vars['username']) || 
                !preg_match(Signup::HANDLE_PREGEXP, $vars['username']) ||
                strpos($vars['username'], 'xn--') !== false) {
            $errors[] = 'SignupErrorWrongUsername';
        } elseif ($this->handleInUse($vars['username'])) {
            $errors[] = 'SignupErrorUsernameAlreadyTaken';
        }
        
        // email (e-mail duplicates in BW database allowed)
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
        } else {
            if (substr_count($vars['password'], '*') != strlen($vars['password'])) {
                // set encoded pw
                // TODO: later use TB's
                // MOD_user::passwordEncrypt($vars['password']);
                $vars['passwordenc'] = $vars['password'];
                $shadow = str_repeat('*', strlen($vars['password']));
                $vars['password']  = $shadow;
                $vars['passwordcheck'] = $shadow;
            }
        }
        
        // firstname, lastname
        if (empty($vars['firstname']) || !preg_match(Signup::HANDLE_PREGEXP_FIRSTNAME, $vars['firstname']) ||
            empty($vars['lastname']) || !preg_match(Signup::HANDLE_PREGEXP_LASTNAME, $vars['lastname'])
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
            if ($this->ageValue($vars['iso_date']) < Signup::YOUNGEST_MEMBER) {
                $errors[] = 'SignupErrorBirthDateToLow';
            }
        }
        
        // (skipped:) birthmonth

        // (skipped:) birthday

        // (skipped:) age hidden

        // terms
        if (empty($vars['terms']) || strcmp($vars['terms'], Signup::BW_TRUE) != 0) {
            $errors[] = 'SignupMustacceptTerms';    // TODO: looks like a wrong case in "Accept"
        }
        
        return $errors;
    }
    
    /** @return float (?!) value corresponding date
	 * 
	 * FIXME: copied from FunctionsTools.php fage_value;
	 * used in several places in BW website;
	 * where should this been moved to?
	 */
	public function ageValue($dd)
	{
		$iDate = strtotime($dd);
		$age = (time() - $iDate) / (365 * 24 * 60 * 60);
		return ($age);
	}
	
	/**
	 * (stolen from FunctionsTools->CheckEmail)
	 * @return true , if e-mail address looks valid 
	 */
	public function checkEmail($email)
	{
		return ereg(Signup::HANDLE_PREGEXP_EMAIL, $email);
	}
	
	/**
	 * @see FunctionsTools.php (plain copy)
	 * compute a nearly unique key according to parameters
	 */ 
	public function createKey($s1, $s2, $IdMember = "", $ss = "default")
	{
	    $key = sprintf("%X", crc32($s1 . " " . $s2 . " " . $IdMember . "_" . $ss));
	    return ($key);
	}
	
	public function test()
	{
	    // just defaults
	    assert_options(ASSERT_ACTIVE, 1);
	    assert_options(ASSERT_WARNING, 1);
	    assert_options(ASSERT_BAIL, 0);
	    assert_options(ASSERT_QUIET_EVAL, 0);
	    
	    // checkEmail
	    assert($this->checkEmail('f1f@ddd.info'));
	    assert($this->checkEmail('zungi@gmx.de'));
	    
	    // polishFormValues
	    $vars['email'] = "stOCKHAUSen@nIcEmUsiKa.net"; 
	    $vars['agehidden'] = "YES";
	    $vars['username'] = "' OR ''='";
	    $this->polishFormValues($vars);
	    assert(strcmp($vars['agehidden'], Signup::BW_TRUE) != 0);
	    assert(strcmp($vars['email'], 'stockhausen@nicemusika.net') == 0);
	    assert(strcmp($vars['username'], "\\' OR \\'\\'=\\'") == 0);
	    
	    // specifyCity
	    $rows = MOD_geo::get()->guessCity(2921044, 'Hamburg');
	    assert(count($rows) == 1);
	    
	    // checkRegistrationForm
	    //$vars['country'] = "2921044";
	    $vars['country'] = "2077456";
	    $vars['city'] = "Magdeburg";
	    //$vars['city_id'] = "2155571";
	    $vars['housenumber'] = "0";
	    $vars['street'] = "Schanzenstrasse";
	    $vars['zip'] = "20357";
	    $vars['username'] = "Felixo";
	    $vars['email'] = "quatsch@kannweg.de";
	    $vars['emailcheck'] = 'quatsch@kannweg.de';
	    $vars['password'] = 'tschangtschang';
	    $vars['passwordcheck'] = 'tschangtschang';
	    $vars['firstname'] = 'Felixaa';
	    $vars['lastname'] = 'van So';
	    $vars['gender'] = 'female';
	    $vars['birthyear'] = '1900';
	    $vars['terms'] = 'Yes';
	    
	    $errors = $this->checkRegistrationForm($vars);
	    assert(count($errors) == 0);
	    echo "";
	    var_dump($errors);
	    echo "";
	    var_dump($vars['city_id']);
	    echo "";
	    var_dump($vars['city']);

	    //$V = new SignupView($this);
	    //$elem = $V->getCityElement($vars['city']);
	    //echo "<br>" . $elem;

	    //assert(in_array("SignupErrorProvideHouseNumber", $errors));
	    
	    //$S = new MOD_secshield(0,0);
	    //$S->test();
	}
	
}
?>
