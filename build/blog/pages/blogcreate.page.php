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
     * displays a page for creating a blog post
     *
     * @package Apps
     * @subpackage Blog
     */

class BlogCreatePage extends BlogBasePage
{

    protected function column_col3()
    {
        $member = $this->_model->getLoggedInMember();
        $Blog = new Blog;
        $callback = $this->getCallbackOutput('BlogController', 'createProcess');

        // get the saved post vars

        // get current request
        $request = PRequest::get()->request;

        $errors = array();
        $lang = array();
        $words = new MOD_words($this->getSession());
        $i18n = new MOD_i18n('apps/blog/editcreate.php');
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

        if (!isset($vars['errors']) || !is_array($vars['errors'])) {
            $vars['errors'] = array();
        }

        if (!isset($request[2]) || $request[2] != 'finish') {
            $actionUrl = 'blog/create';
            $submitName = '';
            $submitValue = $words->getSilent('BlogCreateSubmit');
            echo '<h2>'.$words->get('Blog_CreateEntry').'</h2>';
        } else { // $request[2] == 'finish'
            echo '<h2>'.$words->get('BlogCreateFinishTitle')."</h2>\n";
            echo '<p>'.$words->get('BlogCreateFinishText')."</p>\n";
            echo '<p>'.$words->get('BlogCreateFinishInfo')."</p>\n";
        }
        $disableTinyMCE = $this->_model->getTinyMCEPreference();
        require SCRIPT_BASE . 'build/blog/templates/editcreateform.php';
    }
}
