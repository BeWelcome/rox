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
     * @author Fake51
     */

    /**
     * This page asks if the user wants to join the group
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupJoinPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        $a = new APP_User();
        if (!$a->isBWLoggedIn('NeedMore,Pending'))
        {
            $widg = $this->createWidget('LoginFormWidget');
            $widg->render();
        }
        else
        {
        //crumbking: this is just a copy/paste of the membersettings layout with small changes in some words, needs a coder ;)
    
            $layoutkit = $this->layoutkit;
            $words = $layoutkit->getWords();

            $formkit = $layoutkit->formkit;
            $callback_tag = $formkit->setPostCallback('GroupsController', 'joined');

            $error = $formkit->mem_from_redirect ? $words->get('GroupsErrorJoiningGroup') : '';

            $group_name_html = htmlspecialchars($this->getGroupTitle(), ENT_QUOTES);

            echo <<<HTML
        {$error}
        <form action="" method="post">
            {$callback_tag}

            <fieldset class="px-3">
             <legend>{$words->get('GroupsJoinTheGroup')} {$group_name_html}</legend>
            <input type='hidden' name='member_id' value='{$this->member->id}'>
                <input type='hidden' name='group_id' value='{$this->group->id}'>
            <div class="row mt-3">
                <div class="col-auto">
                    <label for="comment">{$words->get('GroupsMemberComments')}</label><br>
                    <textarea id="comment" name="membershipinfo_comment" cols="30" rows="3"></textarea>
                </div> <!-- row -->
                <div class="col-auto">
                    <label>{$words->get('GroupsMemberAcceptMail')}:</label><br>

                    <div class="d-flex btn-group mb-3" data-toggle="buttons">
                        <label class="btn btn-primary btn-radio active" for="yes_option">
                            <input id='yes_option' class="noradio" checked="checked" autocomplete="off" type="radio" value="yes" name="membershipinfo_acceptgroupmail">Yes
                        </label>
                        <label for="no_option" class="btn btn-primary btn-radio">
                            <input id='no_option' class="noradio" autocomplete="off" type="radio" value="no" name="membershipinfo_acceptgroupmail">No
                        </label>
                    </div>
                </div>
                <div class="w-100 px-3">
                    <input type='submit' class='btn btn-primary' value='{$words->getSilent('GroupsGetMeIn')}' name='join'/>
                </div>
            </div>
       
            </fieldset>
             
        </form>
HTML;
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'join';
    }
}

?>
