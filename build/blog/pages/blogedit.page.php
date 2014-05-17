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
     * displays a page for editing a blog post
     *
     * @package Apps
     * @subpackage Blog
     */

class BlogEditPage extends BlogBasePage
{

    protected function column_col3()
    {
        $post = $this->post;
        $member = $this->member;
        $blogId = $post->blog_id;
        $vars = $this->vars;
        $callback = $this->getCallbackOutput('BlogController', 'editProcess'); 

        $errors = array();
        $lang = array();
        $i18n = new MOD_i18n('apps/blog/editcreate.php');
        $words = new MOD_words();
        $errors = $i18n->getText('errors');
        $lang = $i18n->getText('lang');
        $monthNames = array();
        $i18n = new MOD_i18n('date.php');
        $monthNames = $i18n->getText('monthNames');

        $catIt = $this->_model->getCategoryFromUserIt($member->id);
        $tripIt = $this->_model->getTripFromUserIt($member->id);
        $google_conf = PVars::getObj('config_google');
        $defaultVis = new StdClass;
        $defaultVis->valueint = 2; // hack: TB settings are disabled as they reference app_user - default visibility is public
        //$defaultVis = A PP_User::getSetting($member->id, 'APP_blog_defaultVis');

        if (!isset($request[3]) || $request[3] != 'finish') {
            echo '<h2>'.$words->get('BlogEditTitle').'</h2>';
        } else { // $request[2] == 'finish'
            echo '<h2>'.$words->get('BlogEditFinishTitle')."</h2>\n";
            echo $words->get('BlogEditFinishText') ? '<p>'.$words->get('BlogEditFinishText')."</p>\n" : ''; 
            echo $words->get('BlogEditFinishInfo') ? '<p>'.$words->get('BlogEditFinishInfo')."</p>\n" : ''; 
        }   

        $actionUrl = 'blog/edit/'.$blogId;
        $submitName = 'submit_blog_edit';
        $submitValue = $words->getSilent('BlogEditSubmit');      

        $disableTinyMCE = $this->_model->getTinyMCEPreference();
        require_once SCRIPT_BASE . 'build/blog/templates/editcreateform.php';
    }

}

