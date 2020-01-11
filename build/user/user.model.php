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
    //const HANDLE_PREGEXP = '%^[a-z][a-z0-9_]{3,}$%i';
    const HANDLE_PREGEXP = '/^[a-z](?!.*[-_.][-_.])[a-z0-9-._]{2,18}[a-z0-9]$/i';

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

    public function getFriends($userId)
    {
    	$query = '
SELECT
    u.`id`,
    u.`handle`,
    u.`email`
FROM `user_friends` AS f
LEFT JOIN `user` AS u ON
    u.`id` = f.`user_id_foreign_friend`
WHERE
    f.`user_id_foreign` = '.(int)$userId.'
GROUP BY u.`id`
ORDER BY u.`handle`
        ';
        $s = $this->dao->query($query);
        if( !$s->numRows())
            return false;
        return $s;
    }

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

            $query = "select id from members where id=" . $this->session->get("IdMember") . " and PassWord=PASSWORD('" . trim($vars['OldPassword']) . "')";
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
                $query = 'UPDATE `members` SET `PassWord` = PASSWORD(\''.trim($vars['NewPassword']).'\') WHERE `id` = '.$this->session->get('IdMember');
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
        $s = $this->dao->query('SELECT `user`.`location`, `geonames`.`latitude`, `geonames`.`longitude`, `geonames`.`name` AS `location`, `geonamescountries`.`name` AS `country`, `geonames`.`country` AS `code`, `geonames`.`admin1`
            FROM `user`
            LEFT JOIN `geonames` ON( `user`.`location` = `geonames`.`geonameid`)
            LEFT JOIN `geonamescountries` ON( `geonames`.`country` = `geonamescountries`.`country`)
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
}
?>
