<?php
/*

Copyright (c) 2007 BeVolunteer

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
 * signup view
 *
 * @package signup
 * @author Felix van Hove <fvanhove@gmx.de>
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
    public function __construct(SignupModel $model)
    {
        $this->_model = $model;
    }

    /**
     * Loading Custom CSS for signup
     *
     * @param void
     */
    public function customStylesSignup()    {
        echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/signup.css\" type=\"text/css\"/>";
    }

    /**
     * Loading Simple Teaser - just needs defined title
     *
     * @param void
     */
    public function ShowSimpleTeaser($title)    {
        require 'templates/teaser_simple.php';
    }

    /**
     * Loading register confirm error template
     *
     * @param void
     */
    public function confirmation($username, $email)
    {
        require 'templates/confirmation.php';
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

        
		// probably not needed anymore
		// $selCountry = 0;
        // if (isset($vars['country'])) {
            // $selCountry = $vars['country'];
        // }
        // $countries = $this->getAllCountriesSelectOption($selCountry);

        $javascript = false;
        if (isset($vars['javascriptactive'])) {
}
        if (isset($vars['javascriptactive']) && $vars['javascriptactive'] === 'true') {
            $javascript = true;
        }
        // probably not needed anymore
		// $selCity = null;
        // if (isset($vars['city'])) {
            // $selCity = $vars['city'];
        // }
        // $city = $this->getCityElement($selCity, $javascript);

        $selYear = 0;
        if (isset($vars['birthyear'])) {
            $selYear = $vars['birthyear'];
        }
        $birthYearOptions = $this->buildBirthYearOptions($selYear);

        require 'templates/registerform.php';
        PPostHandler::clearVars($callbackId);
    }

    /**
	* probably not needed anymore but need to set city and country from geonames result to have it at hand when writing mails
     * @see geo.lib.php method guessCity
     * @see signup.model.php method checkRegistrationForm
     * @param object $city either empty or empty or string or array
     * @param boolean $javascript true or false
     * @return string displaying the city selection, either an
     *                   input text field or a select option box;
     *                   possibly accompanied by additional fields
     *                   needed
     */
    // public function getCityElement($city, $javascript)
    // {
        // if (empty($city)) {
            // return '<input type="text" id="city" name="city"  />'."\n";
        // } else if (!is_array($city)) {
            // return '<input type="text" id="city" name="city"
                // value="' . htmlentities($city, ENT_COMPAT, 'utf-8') . '"  />'."\n";
        // } else {

            // $html = '';
            // if (!$javascript) {
                // // TODO: needs an explanation in the page (words()...)
                // $html .= '<input type="text" id="city" name="city" />'."\n";
            // }
            // $html .= '<select name="city_id" />';
            // foreach ($city as $id => $arr) {
                // $text = $arr[0] . " --- " . $arr[1];
                // $html .= '<option value="' . $id . '">' . $text . '</option>';
            // }

            // $html .= "</select>\n";
            // return $html;
        // }
    // }

    /**
     * Notify volunteers
     * // TODO: create appropriate template
     * @param array $vars with username
     */
    public function signupTeamMail($vars)
    {
        $country = $vars['countryname'];
        $language = $_SESSION['lang'];    // TODO: convert to something readable
        $subject = "New member " . $vars['username'] . " from " .
                   $country .
                   " has signed up";
        $text = "Candidate: " . $vars['firstname'] . " " . $vars['lastname'] . "\n" .
                "country: " . $country . "\n" .
                "city: " . $vars['geonamename'] . "\n" .
                "e-mail: NOT SHOWN \n" .
                "used language: " . $language . "\n"
                // FIXME
                //"<a href=\"http://" .$_SYSHCVOL['SiteName'] . $_SYSHCVOL['MainDir'] .
                //"admin/adminaccepter.php\">go to accepting</a>\n";
        //bw_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'],
        //$subj, $text, "", $_SYSHCVOL['SignupSenderMail'], 0, "html", "", "");
        ;
        
        $receiver = PVars::getObj('syshcvol')->MailToNotifyWhenNewMemberSignup;
        $sender = PVars::getObj('mailAddresses')->registration;
        
        // $from = "";     // TODO
        // $Mail = new MOD_mail_Multipart;
        // $Mail->addMessage($text);
        // $Mail->buildMessage();


        // $Mailer = Mail::factory(PVars::getObj('config_smtp')->backend, PVars::get()->config_smtp);
        // if (is_a($Mailer, 'PEAR_Error')) {
            // $e = new PException($Mailer->getMessage());
            // $e->addMessage($Mailer->getDebugInfo());
            // throw $e;
        // }
        
        // $rcpts = $email;
        // $header = $Mail->header;
        // $header['From'] = $from;
        // $header['To'] = $email;
        // $header['Subject'] = $subject;
        // $header['Message-Id'] = '<reg'.$_SESSION['IdMember'].'.'.sha1(uniqid(rand())).
                                // '@' . DOMAIN_MESSAGE_ID . '>';
        // FIXME: comment for security reasons
        // $r = @$Mailer->send($rcpts, $header, $Mail->message);
        // $r = '';
        // if (is_object($r) && is_a($r, 'PEAR_Error')) {
            // $e = new PException($r->getMessage());
            // $e->addInfo($r->getDebugInfo());
            // throw $e;
        // }
        
            // partly copied from htdocs/bw/lib/mailer.php
            //Load the files we'll need
            require_once "bw/lib/swift/Swift.php";
            require_once "bw/lib/swift/Swift/Connection/SMTP.php";
            require_once "bw/lib/swift/Swift/Message/Encoder.php";
        
            //Start Swift
            $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
            
            // FOR TESTING ONLY (using Gmail SMTP Connection for example):
            //$smtp =& new Swift_Connection_SMTP("smtp.gmail.com", Swift_Connection_SMTP::PORT_SECURE, Swift_Connection_SMTP::ENC_TLS);
            //$smtp->setUsername("YOURUSERNAME");
            //$smtp->setpassword("YOURPASSWORD");
        	//$swift =& new Swift($smtp);
        	 
            //Create a message
        	$message =& new Swift_Message($subject);
            
        	//Add some "parts"
        	$message->attach(new Swift_Message_Part($text));
        	//$message->attach(new Swift_Message_Part($this->style(stripslashes(str_replace("\n","<br \>",$input['text'])),$input['attach_picture']), "text/html"));
            
            // set the sender
            // FIXME: Read & Uncrypt member's email address from the DB and make it the sender-address
            //$sender_uncrypted = new MOD_member->getFromMembersTable('email');
            //$sender = ???
            
        	//Now check if Swift actually sends it
        	if ($swift->send($message, $receiver, $sender)) {
                $status = true;
        	} else {
        		LogStr("bw_sendmail_swift: Failed to send a mail to ".$receiver, "hcvol_mail");
                $status = false;
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
        
        // KEY-GENERATION the TB Way
        $key    = APP_User::getSetting($userId, 'regkey');
        if (!$key)
            return false;
        $key = $key->value;
        
        // TODO: Replace that with a  better function, this one is not save (see above for an example)
        // KEY-GENERATION the old BW Way
        //------------------------------------------------------------------------------ 
        // function CreateKey compute a nearly unique key according to parameters 
        // function CreateKey($s1, $s2, $IdMember = "", $ss = "default") {
        	// $key = sprintf("%X", crc32($s1 . " " . $s2 . " " . $IdMember . "_" . $ss)); // compute a nearly unique key
        	// return ($key);
        // } // end of CreateKey
        // $key = CreateKey($User->handle, $User->email, $User->id, "registration"); // compute a nearly unique key for cross checking
        
        $words = new MOD_words();
        
        $confirmUrl = PVars::getObj('env')->baseuri.'signup/confirm/'.$handle.'/'.$key;
        
        $FirstName = '';
        $SecondName = '';
        $LastName = '';
        // $registerMailText = array();
        // require SCRIPT_BASE.'text/'.PVars::get()->lang.'/apps/user/register.php';
        $text = $words->get("SignupTextRegistration", $handle, '', '', PVars::getObj('env')->SiteName, $confirmUrl);
        $sender    = PVars::getObj('mailAddresses')->registration;
        $subject = $words->get('SignupSubjRegistration', PVars::getObj('env')->SiteName);
        // TODO change $words->get('subject') and ('from_name') to real values from the ini-settings

        // $Mail = new MOD_mail_Multipart;
        // $logoCid = $Mail->addAttachment(HTDOCS_BASE.'images/logo.png', 'image/png');

        // ob_start();
        // require 'templates/mail/register_html.php';
        // $mailHTML = ob_get_contents();
        // ob_end_clean();
        // $mailText = '';
        // require 'templates/mail/register_plain.php';

        // $Mail->addMessage($mailText);
        // $Mail->addMessage($mailHTML, 'text/html');
        // $Mail->buildMessage();

        // $Mailer = Mail::factory(PVars::getObj('config_smtp')->backend, PVars::get()->config_smtp);
        // if (is_a($Mailer, 'PEAR_Error')) {
            // $e = new PException($Mailer->getMessage());
            // $e->addMessage($Mailer->getDebugInfo());
            // throw $e;
        // }
        // $rcpts = $email;
        // $header = $Mail->header;
        // $header['From'] = $from;
        // $header['To'] = $email;
        // $header['Subject'] = $subject;
        // $header['Message-Id'] = '<reg'.$userId.'.'.sha1(uniqid(rand())).
                                // '@' . DOMAIN_MESSAGE_ID . '>';
        // FIXME: comment for security reasons
        // $r = @$Mailer->send($rcpts, $header, $Mail->message);
        // $r = '';

        // if (is_object($r) && is_a($r, 'PEAR_Error')) {
            // $e = new PException($r->getMessage());
            // $e->addInfo($r->getDebugInfo());
            // throw $e;
        // }
        
           // partly copied from htdocs/bw/lib/mailer.php
            //Load the files we'll need
            require_once "bw/lib/swift/Swift.php";
            require_once "bw/lib/swift/Swift/Connection/SMTP.php";
            require_once "bw/lib/swift/Swift/Message/Encoder.php";
        
            //Start Swift
            $swift =& new Swift(new Swift_Connection_SMTP("localhost"));
            
            // FOR TESTING ONLY (using Gmail SMTP Connection for example):
            //$smtp =& new Swift_Connection_SMTP("smtp.gmail.com", Swift_Connection_SMTP::PORT_SECURE, Swift_Connection_SMTP::ENC_TLS);
            //$smtp->setUsername("YOURUSERNAME");
            //$smtp->setpassword("YOURPASSWORD");
        	//$swift =& new Swift($smtp);
        	 
            //Create a message
        	$message =& new Swift_Message($subject);
            
        	//Add some "parts"
        	$message->attach(new Swift_Message_Part($text));
        	$message->attach(new Swift_Message_Part($this->style(stripslashes(str_replace("\n","<br \>",$text))), "text/html"));
            
            // set the sender
            // FIXME: Read & Uncrypt member's email address from the DB and make it the sender-address
            //$sender_uncrypted = new MOD_member->getFromMembersTable('email');
            //$sender = ???
            
        	//Now check if Swift actually sends it
        	if ($swift->send($message, $email, $sender)) {
                $status = true;
        	} else {
        		LogStr("bw_sendmail_swift: Failed to send a mail to ".$to, "hcvol_mail");
                $status = false;
        	}
    }

    public function showTermsAndConditions()
    {
        require 'templates/termsandconditions.php';
    }

    /**
	*probably not needed anymore
     * @param string $selCountry the selected country
     */
    // private function getAllCountriesSelectOption($selCountry) {
        // $countries = MOD_geo::get()->getAllCountries();
        // $out = '<select id="country" name="country" onchange="change_country(\'formname\');">'."\n";
        // $out .= '<option value="0">';
        // $words = new MOD_words();
        // $out .= $words->get('MakeAChoice');
        // $out .= '</option>'."\n";
        // foreach ($countries as $countryId => $country) {
            // $out .= '<option value="' . $countryId . '"';
            // if ($countryId == $selCountry)
                // $out .= ' selected';
            // $out .= '>';
            // $out .= $country;
            // $out .= "</option>\n";
        // }
        // $out .= "</select>\n";
        // return $out;
    // }

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
    
	public function style($text,$photo = false) {
        $html = '<p style="font-family: Arial; font-size: 12px; line-height: 1.5em">';
        if ($photo) {
            $src = MOD_layoutbits::smallUserPic_username($_SESSION['Username']);
            $html .= '<img alt="picture of '.$_SESSION['Username'].'" src="'.$src.'" style="border: 1px solid #ccc; padding: 6px; margin: 15px; float:left">';
        }
        $html .= $text.'</p>';
        $html .= '<h3 style="font-family: Arial; font-size: 12px; line-height: 1.5em"><a href="http://www.bewelcome.org" style="color: #333">www.bewelcome.org</a></h3>';
        return $html;
    }
}
?>
