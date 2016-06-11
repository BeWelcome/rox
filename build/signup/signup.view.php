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
        $body = $words->get("SignupTextRegistration", $vars['firstname'], $vars['secondname'], $vars['lastname'], PVars::getObj('env')->sitename, $confirmUrl_html);

        // set the sender & receiver
        $from    = PVars::getObj('mailAddresses')->registration;
        $to  = $vars['email'];

        // set the subject
        $subject = $words->get('SignupSubjRegistration', PVars::getObj('env')->sitename);

        // Use MOD_mail to create and send a message
        $result = MOD_mail::sendEmail($subject, $from, $to, $title, $body, $member->getLanguagePreference());

        //Now check if Swift actually sends it
        if (!$result)
            MOD_log::get()->write(" in signup view registerMail: Failed to send a mail to [".$to."]", "signup");

        return $result;
    }

    /**
     * Sends a confirmation e-mail
     *
     * @param string $userId
     */
    public function sendActivationMail(Member $member)
    {
        if (!$member)
            return false;
        $words = new MOD_words();

        $body = $words->get("SignupBodyActivationMail", $member->Firstname, $member->Secondname, $member->Lastname,
                    PVars::getObj('env')->sitename, $member->Username);

        // set the sender & receiver
        $from    = PVars::getObj('mailAddresses')->registration;
        $to  = $member->getEmailWithoutPermissionChecks();

        // set the subject
        $subject = $words->get('SignupSubjectActivationMail', PVars::getObj('env')->sitename);

        // Use MOD_mail to create and send a message
        $result = MOD_mail::sendEmail($subject, $from, $to, '', $body);

        //Now check if Swift actually sends it
        if (!$result)
            MOD_log::get()->write(" in signup view " . __FUNCTION__ . ": Failed to send a mail to [".$to."]", "signup");

        return $result;
    }
}
