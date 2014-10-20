<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminNewMembersListMembersPage extends AdminNewMembersBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminFlagsListMembers');
    }

    public function getLateLoadScriptFiles() {
        $scripts = parent::getLateLoadScriptfiles();
//        $scripts[] = 'adminflagstooltip.js';
        return $scripts;
    }

    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
        return $stylesheets;
    }

    /*
     * @return HTML snippet with a form to select the status of a user
     */
    public function statusForm($memberId, $memberStatus)
    {
        $form = '';
        $words = $this->model->getWords();
        if ($this->_statuses) {
            $layoutkit = $this->layoutkit;
            $formkit = $layoutkit->formkit;
            $callbackTags = $formkit->setPostCallback('AdminNewMembersController', 'setStatusCallback');
            if (($logged_member = $this->model->getLoggedInMember()) && $logged_member->hasOldRight(array('Admin' => '', 'SafetyTeam' => '', 'Accepter' => '', 'Profile' => ''))) {
                $form .= '<div><form method="post" name="member-status" id="member-status" action="' . $this->url . '">' . $callbackTags;
                $form .= '<input type="hidden" name="member-id" value="' . $memberId . '">';
                $form .= '<select name="new-status">';
                foreach ($this->_statuses as $status) {
                    $form .= '<option value="' . $status . '"';
                    if ($status == $memberStatus) {
                        $form .= ' selected="selected"';
                    }
                    $form .= '>' . $words->getBuffered('MemberStatus' . $status) . '</option>';
                }
                $form .= '</select>&nbsp;&nbsp;<input type="submit" value="Submit"/>';
                $form .= '</form>' . $words->flushBuffer() . '</div>';
                $form .= '<div>';
            }
        }
        return $form;
    }

    public function localGlobal($words, $details) {
        $greetings = "";
        $bewelcomed = $details->bewelcomed;
        if (($bewelcomed & 1) != 1) {
            $greetings .= '<a href="/admin/newmembers/local/' . $details->Username .'" target="_blank">
                <img src="images/icons/map.png" alt="' . $words->getSilent('AdminNewMembersLocalGreetingUsername', $details->Username) . '
                title="' . $words->getSilent('AdminNewMembersLocalGreetingUsername', $details->Username) . ' /></a><br />
                <a href="/admin/newmembers/local/' . $details->Username . '" target="_blank" onclick="location.reload(true);">' .
                $words->getBuffered("AdminNewMembersLocalGreeting") . '</a><br /><br />';
        }
        if (($bewelcomed & 2) != 2) {
            $greetings .= '<a href="/admin/newmembers/global/' . $details->Username .'" target="_blank">
                <img src="images/icons/world.png" alt="' . $words->getSilent('AdminNewMembersGlobalGreetingUsername', $details->Username) . '
                title="' . $words->getSilent('AdminNewMembersGlobalGreetingUsername', $details->Username) . ' /></a><br />
                <a href="/admin/newmembers/global/' . $details->Username . '" target="_blank" onclick="location.reload(true);">' .
                $words->getBuffered("AdminNewMembersGlobalGreeting") . '</a>';
        }
        return $greetings;
    }
}
