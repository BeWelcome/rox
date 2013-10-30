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
        $nav['level'] = $this->rights['Words']['Level'];
        $nav['grep'] = $this->rights['Grep']['Level'];
        return $nav;
    }

    public function editTranslation(){
        $page = new AdminWordEditPage();
        $nav = $this->getNavigationData();
        $page->langarr = $this->_model->getLangarr($nav['scope']);
        // default language can be overridden through url
        if (isset($this->route_vars['shortcode'])){
            $nav['shortcode'] = $this->route_vars['shortcode'];
        }
        // register if language is within scope
        if ($this->checkScope($nav['scope'],$nav['shortcode'])) {
            $page->noScope = false;
        } else {
            $page->noScope = true;
        }
        $page->nav = $nav;

        if (isset($this->route_vars['wordcode'])){
            // specific wordcode selected
            $wordcode = $this->route_vars['wordcode'];
            $page->data = $this->_model->getTranslationData('edit',$page->nav['shortcode'],$wordcode);
        } else {
            // no wordcode selected
            $wordcode = false;
            $page->data = null;
        }
        return $page;
    }

    /**
     * displays translation lists for a language
     *
     * @access public
     * @return object
     */
    public function showList(){
        $page = new AdminWordListPage;
        $page->type = $this->route_vars['type'];
        $page->nav = $this->getNavigationData();

        if (!$this->checkScope($page->nav['scope'],$page->nav['shortcode'])){
            $page->noScope = true;
        } else {
            $page->noScope = false;
            $page->stat = $this->getStatistics($page->nav['idLanguage']);
            $page->data = $this->_model->getTranslationData($page->type,$page->nav['shortcode']);
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
        return (strpos($scope,'"'.$shortcode.'"')>-1 || $scope == '"All"');
    }

    public function trListCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (empty($args->post)) {return false;}
        $nav = $this->getNavigationData();
        foreach(array_keys($args->post) as $key){
            if (preg_match('#^Edit_(.*)$#',$key,$wordcode)){
                return $this->router->url('admin_word_editone', array('wordcode'=>$wordcode[1]), false);    
            }
            if (preg_match('#^ThisIsOk_(.*)$#',$key,$wordcode)){
                $this->_model->updateNoChanges($wordcode[1],$nav['idLanguage']);
                return false;            
            }
        }
        return false;
    }

    public function trEditCreateCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (empty($args->post)) {return false;}
        $nav = $this->getNavigationData();
        switch($args->post['DOACTION']){
        case 'Submit' :
            $res = $this->_model->UpdateSingleTranslation($args->post);
            // prepare the result message
            switch($res){
            case 0 :
                $type = 'setFlashError';
                $msg = 'Processing the translation has failed.';
                break;
            case 1 :
                $type = 'setFlashNotice';
                $msg = $args->post['code'].' has been added succesfully';
                break;
            case 2 :
                $type = 'setFlashNotice';
                $msg = $args->post['code'].' has been updated succesfully';
                break;
            }
            // get the flash notice/error
            $this->$type($msg);
            break;
        case 'Find'   :
            $searchparams = array();
            foreach (array('code','Description','Sentence','lang') as $item){
                if (isset($args->post[$item])){
                    if (strlen($args->post[$item])>0){
                        $searchparams[strtolower(substr($item,0,4))] = $args->post[$item];
                    }
                }
            }
            $_SESSION['trData'] = $this->_model->findTranslation($searchparams);
            foreach($_SESSION['trData'] as $key => $item){
                if ($this->checkScope($this->rights['Words']['Scope'],$item->TrShortcode)){
                    $_SESSION['trData'][$key]->inScope = true;
                } else {
                    $_SESSION['trData'][$key]->inScope = false;
                }
            }
            break;
        case 'Delete' :
            $res = $this->_model->archiveSingleTranslation($args->post);
            break;
        }
        return false;
    }
}
