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
        echo "<link rel=\"stylesheet\" href=\"styles/YAML/screen/custom/signup.css?1\" type=\"text/css\"/>";
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

        $javascript = false;
        if (isset($vars['javascriptactive'])) {
}
        if (isset($vars['javascriptactive']) && $vars['javascriptactive'] === 'true') {
            $javascript = true;
        }

        $selYear = 0;
        if (isset($vars['birthyear'])) {
            $selYear = $vars['birthyear'];
        }
        $birthYearOptions = $this->buildBirthYearOptions($selYear);

        require 'templates/registerform.php';
        PPostHandler::clearVars($callbackId);
    }


    /**
     * Notify volunteers
     * // TODO: create appropriate template
     * @param array $vars with username
     */
    public function signupTeamMail($vars)
    {
        $language = $_SESSION['lang'];    // TODO: convert to something readable
        $subject = "[BW Signup Volunteer] New member " . $vars['username'] . " from " .
                   $vars['countryname'] .
                   " has signed up at" . PVars::getObj('env')->sitename;

        ob_start();
        require 'templates/teammail.php';
        $body = ob_get_contents();
        ob_end_clean();

        // set the receiver
        // $receiver = PVars::getObj('syshcvol')->MailToNotifyWhenNewMemberSignup;
        $MailToNotifyWhenNewMemberSignup=$_SESSION["Param"]->MailToNotifyWhenNewMemberSignup ;
        $MailToNotifyWhenNewMemberSignup=str_replace(array(" ",","),";",$MailToNotifyWhenNewMemberSignup) ; // we never know what separator has been used
        $to = explode(";",$MailToNotifyWhenNewMemberSignup) ;

        if (count($to)<=0)  {
            die("Problem, receive cannot work properly you must have at least one valid email in the table params->MailToNotifyWhenNewMemberSignup  [".
            $MailToNotifyWhenNewMemberSignup."]") ;
        }

        // set the sender
        $from = PVars::getObj('mailAddresses')->registration;

        // Use MOD_mail to create and send a message
        $result = MOD_mail::sendEmail($subject,$from,$to,$subject,$body);
        //Now check if Swift actually sends it
        if ($result) {
            $status = true;
        } else {
            MOD_log::get()->write("in signup view signupTeamMail: Failed to send a mail to [".$MailToNotifyWhenNewMemberSignup."]", "signup");
            $status = false;
        }
        return $status;
} // end of signupTeamMail

    /**
     * Sends a confirmation e-mail
     *
     * @param string $userId
     */
    public function registerMail($vars, $IdMember, $idTB)
    {
        $MembersModel = new MembersModel();
        $member = $MembersModel->getMemberWithId($IdMember);
        if (!$member)
            return false;
        $words = new MOD_words();

        // KEY-GENERATION the TB Way
        $key    = APP_User::getSetting($idTB, 'regkey');
        if (!$key)
            return false;
        $key = $key->value;
        $confirmUrl = PVars::getObj('env')->baseuri.'signup/confirm/'.$member->Username.'/'.$key;
        $confirmUrl_html ="<a href=\"".$confirmUrl."\">".$confirmUrl."</a>";

        $title = $words->get("Welcome").'!';
        $body_html = $words->get("SignupTextRegistration", $vars['firstname'], $vars['secondname'], $vars['lastname'], PVars::getObj('env')->sitename, $confirmUrl_html);
        $body = strip_tags($body_html);

        // set the sender & receiver
        $from    = PVars::getObj('mailAddresses')->registration;
        $to  = $vars['email'];

        // set the subject
        $subject = $words->get('SignupSubjRegistration', PVars::getObj('env')->sitename);

        // Use MOD_mail to create and send a message
        $result = MOD_mail::sendEmail($subject, $from, $to, $title, $body);

        //Now check if Swift actually sends it
        if (!$result)
            MOD_log::get()->write(" in signup view registerMail: Failed to send a mail to [".$to."]", "signup");

        return $result;
    }

    public function showTermsAndConditions()
    {
        require 'templates/termsandconditions.php';
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
