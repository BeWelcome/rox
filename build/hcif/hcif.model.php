<?php
/**
 * HC Interface model
 *
 * @package hcif
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: hcif.model.php 198 2007-02-01 21:16:25Z marco $
 * http://ecommunity.ifi.unizh.ch/newlayout/htdocs/ExAuth.php?k=fh457Hg36!pg29G&u=marcoext&e=marco.prestipino@ifi.unizh.ch&OnePad=12828ed&p=change
 */
class Hcif extends PAppModel {
    //private $dao;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function updateTanTable($userName, $onePad) {
        $s = $this->dao->prepare('REPLACE INTO `tantable` ( `Username` , `OnePad` ) VALUES (?, ?)');
        $s->execute(array(0=>$userName, 1=>$onePad));
        return $s->affectedRows();
    }
     public function registerextuser($u,$e,$p) {
     	$nu=new User();
       // $s = $this->dao->prepare('REPLACE INTO `userfrombw` ( `Username` , `pw` , `authid`, `email`) VALUES (?, ?, ?, ?)');
     //   $s->execute(array(0=>$userName, 1=>$onePad));
        //return $s->affectedRows();     
        $c = PFunctions::hex2base64(sha1(__METHOD__));
        
            $errors = array();
            // check username
            if (!preg_match(User::HANDLE_PREGEXP, $u) || strpos($u, 'xn--') !== false) {
                $errors[] = 'username';
                echo "username";
            } elseif ($nu->handleInUse($u)) {
                $errors[] = 'uinuse';
                  echo "username in use";
            }
            // email
            if (!PFunctions::isEmailAddress($e)) {
                $errors[] = 'email';
                  echo "email";
            } elseif ($nu->emailInUse($e)) {
                $errors[] = 'einuse';
                  echo "email in use";
            }
            // password
            if (!isset($p))  {
                $errors[] = 'pw';
              echo "pw";
            } else {
                 
                    // set encoded pw
                    $pwenc = MOD_user::passwordEncrypt($p);
                                   
                
            }
            if (count($errors) > 0) {
             $errors[] = $u;
             error_log(print_r($errors,true),0);
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
    \''.$this->dao->escape($u).'\', 
    \''.$this->dao->escape($e).'\', 
    \''.$this->dao->escape($pwenc).'\', 
    1
)';
            $s = $this->dao->query($query);
            if (!$s->insertId()) {
              echo "insert";
                $errors = array('inserror');
                return false;
            }
            $userId = $s->insertId();
            $key = PFunctions::randomString(16);
            // save register key
            if (!APP_User::addSetting($userId, 'regkey', $key)) {
                $errors = array('inserror');
                return false;
            }
            // save lang
            if (!APP_User::addSetting($userId, 'lang', PVars::get()->lang)) {
                $errors = array('inserror');
                return false;
            }
         
            return true;
         
	  }
}
?>