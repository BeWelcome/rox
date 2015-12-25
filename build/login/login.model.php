<?php
/*
Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.
*/

    /**
     * @author Lupochen
     */
    /**
     * model of the login MVC
     *
     * @package Apps
     * @subpackage Login
     */

class LoginModel extends RoxModelBase
{
    const KEY_IN_SESSION = 'APP_User_id';


//    function encryptPasswordBW($password)
//    {
//        $password = $this->dao->escape(trim($password));
//        if (!$row = $this->singleLookup(
//            "
//SELECT  PASSWORD('$password')  AS  pw_enc_bw
//            "
//        )) {
//            MOD_log::get()->write("qry_jyh failed do retrieve encrypted value for password", "Login");
//            return false;
//        } else {
//            // pw match
//            return $row->pw_enc_bw;
//        }
//    }


    function encryptPasswordTB($password)
    {
        if (CRYPT_MD5) {
            $salt = '$1$' . $this->randomString (9);
        } else if (CRYPT_EXT_DES) {
            $salt = $this->randomString (9);
        } else {
            $salt = $this->randomString (2);
        }
        return crypt ($password, $salt);
    }


    protected function randomString($len)
    {
        $times = ($len % 40) + 1;
        $random = '';
        for ($i = 0; $i < $times; $i++) {
            mt_srand((double)microtime()*1000000);
            $r = mt_rand();
            $random .= sha1(uniqid($r,TRUE));
        }
        return substr ($random, 0, $len);
    }



    /**
     * check if given auth name exists, creates if it does not
     *
     * @param string $authName
     * @return mixed id or false
     */
    function checkAuth($authName)
    {
        try {
            $q = $this->dao->query("
SELECT  id
FROM    mod_user_auth
WHERE   name = '".$this->dao->escape($authName)."'
            ");

            if ($q->numRows() == 1) {
                return $q->fetch(PDB::FETCH_OBJ)->id;
            }

            if ($q->numRows() != 0) {
                throw new PException('D.i.e.!');
            }

            $q = $this->dao->prepare(
                "
INSERT INTO     mod_user_auth (id, name)
VALUES          (?, ?)
                "
            );

            $id = $this->dao->nextId('mod_user_auth');
            $q->bindParam(0, $id);
            $q->bindParam(1, $authName);
            $q->execute();
            $id = $q->insertId();
            if (!$id || $id == -1)
                return false;
            return $id;
        } catch (PException $e) {
            if ($e->getCode() == 1000) {
                $e->addInfo('('.$this->dao->getErrNo().') '.$this->dao->getErrMsg());
            }
            throw $e;
        }
    }



    //--------------------------------------------------------------------



    function createMissingTBUser($member, $password)
    {
        $esc_handle = $this->dao->escape($member->Username);
        $esc_pwenc = $this->dao->escape($this->encryptPasswordTB($password));
        $member_id = (int)$member->id;
        $int_authId = (int)($this->checkAuth('defaultUser'));

        if ($this->singleLookup(
            "
INSERT IGNORE INTO
    user
SET
    id      = $member_id,
    auth_id = $int_authId,
    handle  = '$esc_handle',
    email   = '',
    pw      = '$esc_pwenc',
    active  = 1
            "
        )) {
            // ok, we have created a new tb user record
            // with the same id and username as the bw record.
            return 'same_id';
        } else if ($this->singleLookup(
            "
INSERT INTO
    user
SET
    auth_id = $int_authId,
    handle  = '$esc_handle',
    email   = '',
    pw      = '$esc_pwenc',
    active  = 1
            "
        )) {
            // ok, we have created a new tb user record
            // with the same username as the bw record, but a new id (AUTO_INCREMENT)
            return 'different_id';
        } else {
            // it didn't work..
            return false;
        }
    }

    /*
    function repairTBUser($member, $tb_user, $password)
    {
        $esc_handle = $this->dao->escape($handle);
        $esc_pwenc = $this->dao->escape($this->encryptPasswordTB($password));
        $member_id = $_SESSION['IdMember'];
        $int_authId = (int)($this->checkAuth('defaultUser'));

        if (!$this->singleLookup(
            "
UPDATE  user
SET     auth_id = $int_authId,  pw = '$esc_pwenc'
WHERE   id = $tb_user->id
            "
        )) {
            // oh shit..
        } else {
            // cool.
        }
    }
    */


    //--------------------------------------------------------------------



    function checkBWPassword($member, $password)
    {
        $password = $member->preparePassword($password);
        if (!$pw_enc_lookup = $this->singleLookup(
            "
SELECT  PASSWORD('$password')  AS  PassMysqlEncrypted
            "
        )) {
            MOD_log::get()->write("qry_jyh failed do retrieve encrypted value for password", "Login");
            return false;
        } else if ($member->PassWord != $pw_enc_lookup->PassMysqlEncrypted) {
            // pw mismatch
            return false;
        } else {
            // pw match
            return true;
        }
    }


    function checkTBPassword($tb_user, $password)
    {
        $password = trim($password);
        if (!preg_match('/^\{([^}]+?)\}(.*)$/', $tb_user->pw, $matches)) {
            if (crypt($password, $tb_user->pw) != $tb_user->pw) {
                return false;
            }
        } else switch ($matches[1]) {

            case 'md5':
                if (md5($password) != $matches[2])
                    return false;
                break;

            case 'sha1':
                if (sha1($password) != $matches[2])
                    return false;
                break;

            case 'crypt':
            default:
                if (crypt($password, $matches[2]) != $matches[2])
                    return false;
                break;
        }
        return true;
    }



    /**
     * gets a BW member by username
     *
     * @param string $username - user handle
     *
     * @access public
     * @return false|Member
     */
    public function getBWMemberByUsername($username)
    {
        if (!$m = $this->createEntity('Member')->findByUsername($username))
        {
            return false;
        }

        // member found,
        // but look for alias (in case username was changed)
        while ($m->ChangedId > 0)
        {
            $m = $this->createEntity('Member')->findById($m->ChangedId);
            if (!$m) return false;
        }
        return $m;
    }



    function getTBUserForBWMember($member)
    {
        $esc_handle = $this->dao->escape($member->Username);
        if ($tb_user = $this->singleLookup(
            "
SELECT  *
FROM    user
WHERE   handle = '$esc_handle'
            "
        )) {
            // found one!
            return $tb_user;
        } else {
            return false;
        }
    }



    //-----------------------------------------------------------------------



    function setBWMemberAsLoggedIn($m)
    {
        // Process the login of the member according to his status
        $member_id = (int)$m->id;
				unset($_SESSION['MemberStatus']) ; // For the case where it is set to empty
				unset($_SESSION['Status']) ;  // For the case where it is set to empty
				if (!$m->Status)
                {
					die ("Alarm : in setBWMemberAsLoggedIn with empty \$m->Status") ;
				}
				else {
        	$_SESSION['MemberStatus'] = $_SESSION['Status'] = $m->Status ;
				}
        switch ($m->Status) {
            case "OutOfRemind" :  // in case an inactive member comes back
                $this->singleLookup(
                    "
UPDATE  members
SET     Status     = 'Active'
WHERE   members.id = $member_id and Status='OutOfRemind'
                    "
                );
                // the following is needed for MOD_log::get,
                // because otherwise it would not link the log with the right member
                $_SESSION['IdMember'] = $m->id ;
                $_SESSION['MemberStatus'] = $_SESSION['Status'] = $m->Status='Active' ;
                MOD_log::get()->write("Successful login, becoming active again (was OutOfRemind), with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                break ;
            case "Active" :
            case "ActiveHidden" :
            case "ChoiceInactive" :
                // the following is needed for MOD_log::get,
                // because otherwise it would not link the log with the right member
                $_SESSION['IdMember'] = $m->id ;
                MOD_log::get()->write("Successful login with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b> (".$m->Username.")", "Login");
                break ;

            case "ToComplete" :
                // TODO: This case seems to be nonsense.. ?
                // TODO: Redirects are not the model's task!
                MOD_log::get()->write("Login with (tocomplete)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                // FIXME: completeprofile.php does not exist - why used here? (steinwinde 2007-12-05)
                header("Location: " . PVars::getObj('env')->baseuri . "bw/completeprofile.php");
                PPHP::PExit();

            case "MailToConfirm" :  // I just add this here in case someone try to log with mail to confirm
                MOD_log::get()->write("Login with (MailToConfirm)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false ;
                break;

            case "NeedMore" :
                $_SESSION['IdMember'] = $m->id ;
                MOD_log::get()->write("Login with (needmore)<b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                $this->_immediateRedirect = PVars::getObj('env')->baseuri . "bw/updatemandatory.php";
                break;

            case "Banned" :
            case "TakenOut" :
            case "CompletedPending" :
            case "SuspendedBeta" :
                MOD_log::get()->write("Logging Refused because of status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false ;
                break ;

            case "Pending" :
                $_SESSION['IdMember'] = $m->id ;
                MOD_log::get()->write("Successful login (Pending State)with <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b> (".$m->Username.")", "Login");
                break ;
            default:
                MOD_log::get()->write("Logging Refused because of unknown status<b>".$m->Status."</b> <b>" . $_SERVER['HTTP_USER_AGENT'] . "</b>", "Login");
                return false;
        }
        return true;
    }


    function setupBWSession( $m )
    {
        $member_id = (int)$m->id;

        // Set the session identifier
        $_SESSION['IdMember'] = $m->id;
        $_SESSION['Username'] = $m->Username;
        $_SESSION['MemberStatus'] = $_SESSION['Status'] = $m->Status ;

        if ($_SESSION['IdMember'] != $m->id)
        { // Check is session work of
            $this->logout();
            throw new PException('Login sanity check failed miserably!');
        }; // end Check is session work of

        $_SESSION['MemberCryptKey'] = crypt($m->PassWord, "rt"); // Set the key which will be used for member personal cryptation
        $_SESSION['LogCheck'] = Crc32($_SESSION['MemberCryptKey'] . $m->id); // Set the key for checking id and LohCheck (will be restricted in future)


				if ($m->NbRemindWithoutLogingIn>0) {
            MOD_log::get()->write("This member was having a NbRemindWithoutLogingIn=" .$m->NbRemindWithoutLogingIn, "Login");
				}

        $this->dao->query(
            "
UPDATE
    members
SET
    LogCount  = LogCount+1,
    LastLogin = NOW(),
    NbRemindWithoutLogingIn = 0

WHERE
    id = $member_id
            "
        ); // update the LastLogin date

        // Load language prederence (IdPreference=1)

        // todo: come up with interesting idea like ... using just ONE query to load preferences

        if ($preference_language = $this->singleLookup(
            "
SELECT
    memberspreferences.Value  AS language_id,
    ShortCode                 AS language_code
FROM
    memberspreferences,
    languages,
	preferences
WHERE
    IdMember                 = $member_id    AND
    memberspreferences.Value = languages.id  AND
    IdPreference=preferences.id and preferences.CodeName='PreferenceLanguage'
            "
        )) {
            $_SESSION['IdLanguage'] = $preference_language->language_id;
            $_SESSION['lang']       = $preference_language->language_code;
        }

        if ($preference_PreferenceDayLight = $this->singleLookup(
            "
SELECT
    memberspreferences.Value
FROM
    memberspreferences,
	preferences
WHERE
    IdMember                 = $member_id    AND
    IdPreference=preferences.id and preferences.CodeName='PreferenceDayLight'
            "
        )) {
            $_SESSION['PreferenceDayLight'] = $preference_PreferenceDayLight->Value;
        }

        if ($preference_PreferenceLocalTime = $this->singleLookup(
            "
SELECT
    memberspreferences.Value
FROM
    memberspreferences,
	preferences
WHERE
    IdMember                 = $member_id    AND
    IdPreference=preferences.id and preferences.CodeName='PreferenceLocalTime'
            "
        )) {
            $_SESSION["TimeOffset"] = $preference_PreferenceLocalTime->Value;
        }

        // Process the login of the member according to his status
        switch ($m->Status) {
            case "Active" :
            case "ActiveHidden" :
            case "ChoiceInactive" :
            case "NeedMore" :
            case "Pending" :
                //if (HasRight("Words"))
                //  $_SESSION['switchtrans'] = "on"; // Activate switchtrans oprion if its a translator
                break;

            default:
                throw new PException('SetupBWSession Weird Status!');
                break;
        }
    }



    function setTBUserAsLoggedIn($tb_user)
    {
        session_regenerate_id();

        $_SESSION[self::KEY_IN_SESSION] = $tb_user_id = (int)$tb_user->id;

        $this->dao->query(
            "
UPDATE  user
SET     lastlogin = NOW()
WHERE   id = $tb_user_id
            "
        );
    }

    public function setPreferredLanguage( Member $member ) {
        // Check if language is set (in case we used a Symfony route to get here)
        if (!isset($_SESSION['IdLanguage'])) {
            $frontRouter = new \RoxFrontRouter();
            $frontRouter->setLanguage();
        }
        $prefLang = $member->getPreference('PreferenceLanguage', 'none');
        if ($prefLang === 'none') {
            // the member has no preferred language set
            // we use the current one and set that (for stats and to please the translators)
            $update="
                INSERT INTO
                    memberspreferences
                SET
                    IdMember = " . $member->id . ",
                    IdPreference = 1,
                    Value = " . $_SESSION['IdLanguage'];
            $this->dao->query($update);
            return true;
        }
        return false;
    }

    public function logout()
    {
        if (!$member = $this->getLoggedInMember())
        {
            return false;
        }
        return $member->logOut();
    }
}
