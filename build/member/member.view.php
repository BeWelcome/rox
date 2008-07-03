<?php
/**
 * member view
 *
 * @package member
 * @author Michael Dettbarn (bw: lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
  * @version $Id: member.view.php 217 2007-03-03 10:21:10Z lupochen $
 */
class MemberView extends PAppView
{
    /**
     * Instance of User model
     *
     * @var User
     */
    private $_model;

    /**
     * @param User $model
     */
    public function __construct(Member $model)
    {
        $this->_model = $model;
    }

    public function avatar($userId)
    {
    	if (!$this->_model->hasAvatar($userId)) {
    		header('Content-type: image/png');
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($_GET['xs']) ? '_xs' : '').'.png', 'php://output');
            PPHP::PExit();
    	}
        $file = (int)$userId;
        if (isset($_GET['xs']))
            $file .= '_xs';
        $img = new MOD_images_Image($this->_model->avatarDir->dirName().'/'.$file);
        if (!$img->isImage()) {
            header('Content-type: image/png');
            @copy(HTDOCS_BASE.'images/misc/empty_avatar'.(isset($_GET['xs']) ? '_xs' : '').'.png', 'php://output');
            PPHP::PExit();
        }
        $size = $img->getImageSize();
        header('Content-type: '.image_type_to_mime_type($size[2]));
        $this->_model->avatarDir->readFile($file);
        PPHP::PExit();
    }

    public function friends($friends)
    {
    	require 'templates/friends.php';
    }

    /**
     * Loading Official Message template
     *
     * @param void
     */
    public function ShowInfoMessage($message, $messagetitle)
    {
        require 'templates/infomessage.php';
    }

    /**
     * Loading login form template
     *
     * @param void
     */
    public function loginForm($redirect_url = false)
    {
        if (!$redirect_url) {
            $request = PRequest::get()->request;
            $redirect_url = PVars::getObj('env')->baseuri . implode('/', $request);
        }
        require 'templates/loginform.php';
    }


    /**
     * Loading register confirm error template
     *
     * @param void
     */
    public function registerConfirm($error = false)
    {
        require 'templates/confirmerror.php';
    }

    /**
     * Loading register form template
     *
     * @param void
     */
    public function registerForm()
    {
        require 'templates/registerform.php';
    }
    
    /**
     * Sends a confirmation e-mail
     *
     * @param string $userId
     */
    public function registerMail($userId)
    {
        $User = $this->_model->getUser($userId);
        if (!$User)
            return false;
        $handle = $User->handle;
        $email  = $User->email;
        $key    = APP_User::getSetting($userId, 'regkey');
        if (!$key)
            return false;
        $key = $key->value;
        $confirmUrl = PVars::getObj('env')->baseuri.'user/confirm/'.$handle.'/'.$key;

        $registerMailText = array();
        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/apps/member/register.php';
        $from    = $registerMailText['from_name'].' <'.PVars::getObj('config_mailAddresses')->registration.'>';
        $subject = $registerMailText['subject'];

        $Mail = new MOD_mail_Multipart;
        $logoCid = $Mail->addAttachment(HTDOCS_BASE.'images/logo.png', 'image/png');

        ob_start();
        require 'templates/mail/register_html.php';
        $mailHTML = ob_get_contents();
        ob_end_clean();
        $mailText = '';
        require 'templates/mail/register_plain.php';

        $Mail->addMessage($mailText);
        $Mail->addMessage($mailHTML, 'text/html');
        $Mail->buildMessage();

        $Mailer = Mail::factory(PVars::getObj('config_smtp')->backend, PVars::get()->config_smtp);
        if (is_a($Mailer, 'PEAR_Error')) {
            $e = new PException($Mailer->getMessage());
            $e->addMessage($Mailer->getDebugInfo());
            throw $e;
        }
        $rcpts = $email;
        $header = $Mail->header;
        $header['From'] = $from;
        $header['To'] = $email;
        $header['Subject'] = $subject;
        $header['Message-Id'] = '<reg'.$userId.'.'.sha1(uniqid(rand())).'@myTravelbook>';
        $r = @$Mailer->send($rcpts, $header, $Mail->message);
        if (is_object($r) && is_a($r, 'PEAR_Error')) {
            $e = new PException($r->getMessage());
            $e->addInfo($r->getDebugInfo());
            throw $e;
        }
    }

    public function searchResult($res)
    {
    	require 'templates/searchresult.php';
    }

    public function settingsForm()
    {
        $User = APP_User::get();
        if ($User) {
            $location = $this->_model->getLocation($User->getId());
        } else {
            $location = false;
        }
    	require 'templates/settingsform.php';
    }

    public function passwordForm()
    {
        require 'templates/passwordform.php';
    }

    public function userPage($userHandle)
    {
        if (!$userId = APP_User::userId($userHandle))
            return false;
        $userHandle = $this->_model->getRealHandle($userId);

        $groupChange = $this->getGroupChangeForm($userHandle, $userId);

        require 'templates/userpage.php';
    }

    public function profile($m,$TGroups)
    {
        require 'templates/profile.php';
    }    
    
    public function profileteaser($m) {
        require 'templates/teaser_profile.php';
    }
    
    public function relations($Relations)
    {
        require 'templates/userbar_relations.php';
    }    
    public function profilemenu($IdMember,$m)
    {
        require 'templates/userbar_profile_menu.php';
    }    
    
    private function getGroupChangeForm($userHandle, $userId) {
    	if (!($User = APP_User::login())) {
			return '';
		}
		if ($User->hasRight('groupchange@user')) {
			$current_user = $this->_model->getUser($userId);
			$groups = $this->_model->getUserAuth();
			$callbackid = $this->_model->groupChangeProcess();
			
			$out = '<form method="post">
				<input type="hidden" name="'.$callbackid.'" value="1" />
				<input type="hidden" name="userid" value="'.$userId.'" />
				Group of '.$userHandle.':
				<select name="newgroup">';
			foreach ($groups as $groupid => $group){$out .= '<option value="'.$groupid.'"'.($groupid == $current_user->auth_id ? ' selected="selected"' : '').'>'.$group.'</option>';}
			$out .= '</select><input type="submit" value="Change" /></form>';
			
			return $out;
		}
		return '';
	}
	
    public function teaser() {
        require 'templates/teaser.php';
    }

	public function customStyles() {
		$out = '';
		/* 2column layout */
	//	$out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/bw_basemod_2col.css" type="text/css"/>';
		return $out;
    }

    public function rightContent() {
    	$User = new UserController;
		$User->displayLoginForm();
	}

    public function picture($picture)
    {
    	require 'templates/picture.php';
    }    

}
?>
