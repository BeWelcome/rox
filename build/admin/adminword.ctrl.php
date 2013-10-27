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
     * @author Tsjoek
     */

    /**
     * adminwords controller
     * deals with actions that are available exclusively for translators
     *
     * @package apps
     * @subpackage Admin
     */
class AdminWordController extends RoxControllerBase
{
    private $_model;

    public function __construct() {
        parent::__construct();
        $this->_model = new AdminWordModel();
        // collect volunteerrights for this member;
        list($this->member, $this->rights) = $this->checkRights('Words');
    }

    public function __destruct() {
        unset($this->_model);
    }

    /**
     * redirects if the member has got no business
     * otherwise returns member entity and array of rights
     *
     * @access private
     * @return array
     */
    private function checkRights($right = '') {
        if (!$member = $this->_model->getLoggedInMember())
        {
            $this->redirectAbsolute($this->router->url('main_page'));
            exit(0);
        }
        $rights = $member->getOldRights();
        if (empty($rights) || (!empty($right) && !in_array($right, array_keys($rights))))
        {
            $this->redirectAbsolute($this->router->url('admin_norights'));
            exit(0);
        }
        return array($member, $rights);
    }

    /**
     * displays message about not having any admin rights
     *
     * @access public
     * @return object
     */
    public function noRights() {
        if (!$member = $this->_model->getLoggedInMember())
        {
            $this->redirectAbsolute($this->router->url('main_page'));
        }
        $page = new AdminNoRightsPage;
        $page->member = $member;
        return $page;
    }

    /**
     * collects data used for navigation-texts and links
     *
     * @access private
     * @return array Containing all data
     */
    private function getNavigationData(){
        $words = new MOD_words();
        $nav = array();
        $nav['idLanguage'] = (int)$_SESSION['IdLanguage'];
        $nav['shortcode'] = $_SESSION['lang'];
        $nav['currentLanguage'] = $words->get('lang_'.$nav['shortcode']);
        $nav['scope'] = $this->rights['Words']['Scope'];
        return $nav;
    }

    /**
     * displays translation lists for a language
     *
     * @access public
     * @return object
     */
    public function showList(){
        
        $page = new AdminWordListPage;
        $type = $this->route_vars['type'];
        $page->nav = $this->getNavigationData();
        $page->stat = $this->getStatistics($page->nav['idLanguage']);
        $page->type = $type;

        if (!$this->checkScope($page->nav['scope'],$page->nav['shortcode'])){
            $page->noScope = true;
        } else {
            $page->noScope = false;
            $page->data = $this->_model->getTrListData($type,$page->nav['idLanguage']);
        }
        return $page;
    }
    
    /**
     * displays translation statistics for all languages
     *
     * @access public
     * @return object
     */
    public function showStatistics(){
        if ($this->rights['Words']['Level']>0){
        $page = new AdminWordStatsPage;
        $page->nav = $this->getNavigationData();
        $page->data = $this->getStatistics();
        return $page;
        }
    }

    /**
     * calculates translation statistics for all languages or single language
     *
     * @access private
     * @return array Basic data for translation statistics
     */
    private function getStatistics($lang = null){
        $englishLength = $this->_model->getEnglishTotalLength();
        $allLength = $this->_model->getTranslationLength($lang);
        // prepare the data to be shown on the page
        foreach ($allLength as $key => $langData) {
            $data[$key]['perc'] = ($langData->translated / $englishLength->cnt) * 100;
            $data[$key]['name'] = $langData->englishName;
            $data[$key]['scope'] = $this->checkScope($this->rights['Words']['Scope'],$langData->shortCode);
        }
        return $data;
    }

    /**
     * checks if scope contains language
     *
     * @access private
     * @param string $scope Languagescope of the user
     * @param string $shortcode Shortcode of the language to be checked
     * @return boolean
     */
    private function checkScope($scope,$shortcode){
        return (strpos($scope,'"'.$shortcode.'"')>-1);
    }

}
