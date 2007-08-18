<?php
/**
 * signup view
 *
 * @package signup
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class SignupView extends PAppView
{
    /**
     * Instance of Signup model
     *
     * @var Signup
     */
    private $_model;

    /**
     * @param Signup $model
     */
    public function __construct(Signup $model)
    {
        $this->_model = $model;
    }

    /**
     * Loading register confirm error template
     *
     * @param void
     */
    public function registerConfirm($error = false)
    {
        require TEMPLATE_DIR.'apps/user/confirmerror.php';
    }

    /**
     * Loading register form template
     *
     * @param void
     */
    public function registerForm()
    {
        // instantiate signup model
        $Signup = new Signup;
        // retrieve the callback ID
		$callbackId = $Signup->registerProcess();
		// get the saved post vars
		$vars =& PPostHandler::getVars($callbackId);

		$selCountry = 0;
        if (isset($vars['country'])) {
            $selCountry = $vars['country'];
        }
		$countries = $this->getAllCountriesSelectOption($selCountry);
		
		$selCity = null;
        if (isset($vars['city'])) {
            $selCity = $vars['city'];
        }
		$city = $this->getCityElement($selCity);
        
        $selYear = 0;
        if (isset($vars['birthyear'])) {
            $selYear = $vars['birthyear'];
        }
        $birthYearOptions = $this->buildBirthYearOptions($selYear);
        
        require TEMPLATE_DIR.'apps/signup/registerform.php';
    }
    
    /**
     * @see signup.model.php method specifyCity 
     * @see signup.model.php method checkRegistrationForm
     */
    private function getCityElement($city)
    {
        if (empty($city)) {
            return '<input type="text" id="register-city" name="city">'."\n";
        } else if (!is_array($city)) {
            return '<input type="text" id="register-city" name="city"
				value="' . htmlentities($city, ENT_COMPAT, 'utf-8') . '">'."\n";
        } else {
            $dropDown = '<select name="city">';
            
            $dropDown .= '<option value=""></option>';    // FIXME

            $dropDown .= '</select>';
		    return $dropDown;
		}
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
        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/apps/user/register.php';
        $from    = $registerMailText['from_name'].' <'.PVars::getObj('config_mailAddresses')->registration.'>';
        $subject = $registerMailText['subject'];

        $Mail = new MOD_mail_Multipart;
        $logoCid = $Mail->addAttachment(HTDOCS_BASE.'images/logo.png', 'image/png');

        ob_start();
        require TEMPLATE_DIR.'apps/user/mail/register_html.php';
        $mailHTML = ob_get_contents();
        ob_end_clean();
        $mailText = '';
        require TEMPLATE_DIR.'apps/user/mail/register_plain.php';

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
    
    public function showTermsAndConditions()
    {
        require TEMPLATE_DIR.'apps/signup/termsandconditions.php';
    }

    private function getAllCountriesSelectOption($selCountry = 0) {
        $countries = MOD_geo::get()->getAllCountries();
		$out = '<select name="country" onChange="change_country(\'formname\');">'."\n";
		$out .= '<option value="0">';
		$words = new MOD_words();
		$out .= $words->get('MakeAChoice');
		$out .= '</option>'."\n";
		foreach ($countries as $countryId => $country) {
			$out .= '<option value="' . $countryId . '"';
			if ($countryId == $selCountry)
				$out .= ' selected';
			$out .= '>';
			$out .= $country;
			$out .= "</option>\n";
		}
		$out .= "</select>\n";
	    return $out;
    }
    
    private function buildBirthYearOptions($selYear = 0) {
    
        $old_member_born = date('Y') - 100;
        $young_member_born = date('Y') - Signup::YOUNGEST_MEMBER;
 
        $out = '';
        for ($i=$young_member_born; $i>$old_member_born; $i--) {
            if (!empty($selYear) && $selYear == $i) {
                $out .= "<option value=\"$i\" selected=\"selected\">$i</option>";
            } else {
                $out .= "<option value=\"$i\">$i</option>";
            }
        }
        return $out;
    }
}
?>
