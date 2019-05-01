<?php

use App\Utilities\SessionTrait;

/**
 * @author Philipp Hunstein & Seong-Min Kang <info@respice.de>
 * @version v2.0.0 Pre-Alpha
 */
abstract class MOD_user {
    use SessionTrait;

    protected $authId;
    protected $sessionName;
    protected $tableName;
    protected $dao;
    
    protected function __construct($sessionName = false, $tableName = false) {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
        
        if ($sessionName)
            $this->sessionName = $sessionName;
        if ($tableName)
            $this->tableName = $tableName;
    }
    
    public function __destruct() {
        unset($this->_dao);
    }
    
    public function getAuth() {
        if (!$this->loggedIn)
            return false;
        if (!$this->authId)
            return false;
        $A = new MOD_user_Auth($this->sessionName, $this->tableName, $this->authId);
        return $A;
    }

    public function hasRight($right) {
        $Auth = $this->getAuth();
        if (!$Auth)
            return false;
        return $Auth->hasRight($right);
    }
    
    protected function setAuth($authId) {
        $this->authId = $authId;
    }

    protected function doLogin($handle, $pw) {
        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle) || empty($pw))
            return false;
        $handle = $this->dao->escape($handle);
        $q = $this->dao->query(
            "
SELECT
    id,
    auth_id,
    pw
FROM
    `$this->tableName`
WHERE
    handle = '$handle'  AND
    active = 1
            "
        );
        $d = $q->fetch(PDB::FETCH_OBJ);
        if (!$d)
            return false;
        $matches = array();
        if (preg_match('/^\{([^}]+?)\}(.*)$/', $d->pw, $matches)) {
            switch ($matches[1]) {
                default:
                case 'crypt':
                    if (crypt($pw, $matches[2]) != $matches[2])
                        return false;
                    break;
                    
                case 'md5':
                    if (md5($pw) != $matches[2])
                        return false;
                    break;
                
                case 'sha1':
                    if (sha1($pw) != $matches[2])
                        return false;
                    break;
            }
        } elseif (crypt($pw, $d->pw) != $d->pw) {
            return false;
        }
        $this->setAuth($d->auth_id);
        session_regenerate_id();
        $this->_session->set( $this->sessionName, (int)$d->id );
        $this->loggedIn = true;
        
        $s = $this->dao->prepare(
            "
UPDATE
    `$this->tableName`
SET
    lastlogin = NOW()
WHERE
    id = ?
            "
        );
        $s->bindParam(0, $d->id);
        $s->execute();
        
        return true;
    }


    protected function doBWLogin($handle) {
        if (!isset($this->tableName) || !isset($this->sessionName))
            return false;
        if (empty($handle))
            return false;
        $handle = $this->dao->escape($handle);
        $q = $this->dao->query(
            "
SELECT
    id,
    auth_id
FROM
    `$this->tableName`
WHERE
    handle = '$handle'  AND
    active = 1
            "
        );
        $d = $q->fetch(PDB::FETCH_OBJ);
       
        if (!$d)
            return false;
          
        $this->setAuth($d->auth_id);
        session_regenerate_id();
        $this->_session->set( $this->sessionName, (int)$d->id );
        $this->loggedIn = true;

        $s = $this->dao->prepare('UPDATE `'.$this->tableName.'` SET `lastlogin` = NOW() WHERE `id` = ?');
        $s->bindParam(0, $d->id);
        $s->execute();
        
        return true;
    }
    


    
    protected function getAuthId($userId) {
        if (!isset($this->tableName))
            return false;
        if (isset($this->authId))
            return $this->authId;
        $q = $this->dao->prepare(
            "
SELECT
    auth_id
FROM
    `$this->tableName`
WHERE
    id = ?
            "
        );
        $q->execute(array(1=>(int)$userId));
        $d = $q->fetch(PDB::FETCH_OBJ);
        if (!$d)
            return false;
        return $d->auth_id;
    }

    public function logout() {
        if (!isset($this->sessionName))
            return false;
        if (!$this->_session->has( $this->sessionName ))
            return false;
        $this->loggedIn = false;
        $this->_session->remove($this->sessionName);
        session_regenerate_id();
        return true;
    }

    public static function passwordEncrypt($password) {
        if (CRYPT_MD5) {
            $salt = '$1$' . self::randomString (9);
        } else if (CRYPT_EXT_DES) {
            $salt = self::randomString (9);
        } else {
            $salt = self::randomString (2);
        }
        return crypt ($password, $salt);
    }

    public static function randomString($len) {
        $times = ($len % 40) + 1;
        $random = '';
        for ($i = 0; $i < $times; $i++) {
            mt_srand((double)microtime()*1000000);
            $r = mt_rand();
            $random .= sha1(uniqid($r,TRUE));
        }
        return substr ($random, 0, $len);   
    }

    public static function getTranslations($idMember) {
        
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $localDao =& $dao;
        
        $result = $localDao->query(
            "
SELECT DISTINCT
    memberstrads.IdLanguage,
    languages.ShortCode
FROM
    memberstrads,
    languages
WHERE
    memberstrads.IdLanguage = languages.id  AND
    IdOwner = $idMember
            "
        );

        $a = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            array_push($a, $row);
        }
        return $a;
    }
    
    public static function getImage($paramIdMember=0)
    {
        if ($paramIdMember==0) {
            $IdMember=$this->_session->get('IdMember');
        } else {
            $IdMember=$paramIdMember ;
        }
    
        if ($IdMember==0) {
            return MOD_User::getDummyImage();
        } 

        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $localDao =& $dao;
        
        $result = $localDao->query(
            "
SELECT
    FilePath
FROM
    membersphotos
WHERE
    IdMember = $IdMember  AND
    SortOrder = 0
            "
        );
        $record = $result->fetch(PDB::FETCH_OBJ);
        
        if (isset($record->FilePath)) {
            return $path = PVars::getObj('env')->baseuri . $record->FilePath;
        } else {
            $result = $localDao->query(
                "
SELECT
    Gender,
    HideGender
FROM
    members
WHERE
    id = $IdMember
                "
            );
            $record = $result->fetch(PDB::FETCH_OBJ);
            return MOD_User::getDummyImage($record->Gender, $record->HideGender);
        }
    }
    
    /**
     * Returns the path to an appropriate dummy image in case
     * no image is found.
     * 
     * 
     *
     * @param unknown_type $Gender
     * @param unknown_type $HideGender
     * @return unknown
     */
    static function getDummyImage($Gender='IDontTell', $HideGender='Yes')
    {
            return PVars::getObj('env')->baseuri . 'images/misc/empty_avatar.png';
    }
}



?>
