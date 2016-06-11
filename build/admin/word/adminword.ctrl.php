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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
     * adminwords controller
     * deals with actions that are available exclusively for translators
     *
     * @package apps
     * @subpackage Admin
     */
class AdminWordController extends RoxControllerBase
{
    private $model;
    private $words;

    public function __construct(Session $session) {
        parent::__construct();
        $this->model = new AdminWordModel($session);
        $this->words = new MOD_words();
    }

    public function __destruct() {
        unset($this->model);
    }

    /**
     * redirects if the member has got no business
     * otherwise returns member entity and array of rights
     *
     * @access private
     * @return array
     */
    private function checkRights($right = '') {
        if (!$member = $this->model->getLoggedInMember())
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
        if (!$member = $this->model->getLoggedInMember())
        {
            $this->redirectAbsolute($this->router->url('main_page'));
        }
        $page = new AdminNoRightsPage;
        $page->member = $member;
        return $page;
    }



    /**
     * handles creation of a wordcode
     *
     * @access public
     * @return AdminWordCreateCodePage
     */
    public function createCode(){
        $page = new AdminWordCreateCodePage();
        $page->nav = $this->getNavigationData();
        if (isset($this->route_vars['wordcode'])){
            $page->data = $this->model->getTranslationData('create','long','en',$this->route_vars['wordcode']);
            if (!isset($page->data[0])) {
                $page->data = array(array('EngCode' => $this->route_vars['wordcode']));
            }
        }
        $page->formdata = $this->getFormData(array('EngCode','Sentence','EngDesc','EngDnt',
            'isarchived','EngPrio','lang'),(array)$page->data[0]);
        return $page;
    }

    /**
     * Handles submission of form on createCode page
     *
     * @access public
     * @param StdClass $args
     * @param ReadOnlyObject $action
     * @param ReadWriteObject $mem_redirect
     * @param ReadWriteObject $mem_resend
     * @return redirect / false
     */
    public function createCodeCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (!$nav = $this->baseCallback($args,$mem_redirect,'createCode')){return false;}
        list($id,$res) = $this->model->UpdateSingleTranslation($args->post);
        if ($res == 2) {$this->words->MakeRevision($id,'words');}
        // get the flash notice/error
        list($type,$msg) = $this->getResultMsg($res,$args->post['EngCode'],$args->post['lang']);
        $this->$type($msg);
        $this->_session->remove('form');
        return $this->router->url('admin_word_create', array(), false);
    }


    /**
     * handles edit action of wordcode variables
     *
     * @access public
     * @return AdminWordEditCodePage
     */
    public function editCode(){
        $page = new AdminWordEditCodePage();
        $page->nav = $this->getNavigationData();
        $page->data = $this->model->getTranslationData('edit',$page->nav['shortcode'],$this->_session->get('form']['EngCode'));
        $wcexist = $this->model->wordcodeExist($this->_session->get('form']['EngCode'),'en');
        $page->status = ($wcexist->cnt == 0?'AdminWordCreateCodeMsg':'AdminWordUpdateCodeMsg');
        
        if ($this->model->getEngSentByCode($this->_session->get('form']['EngCode']) == $_SESSION['form']['Sentence')){
            $page->status = 'AdminWordUpdateCodeParsMsg';
        }
        
        $page->formdata = $this->getFormData(array('EngCode','EngSent','EngDesc','EngDnt',
            'Sentence','lang','isarchived','EngPrio'),$page->nav);
        return $page;
    }
    
    /**
     * Handles submission of form on editCode page
     *
     * @access public
     * @param StdClass $args
     * @param ReadOnlyObject $action
     * @param ReadWriteObject $mem_redirect
     * @param ReadWriteObject $mem_resend
     * @return redirect / false
     */
    public function editCodeCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (!$nav = $this->baseCallback($args,$mem_redirect,'editCode')){return false;}
        switch($args->post['DOACTION']){
        case 'Submit' :
            list($id,$res) = $this->model->UpdateSingleTranslation($args->post);
            if ($res != 1) {$this->words->MakeRevision($id,'words');}
            // get the flash notice/error
            list($type,$msg) = $this->getResultMsg($res,$args->post['EngCode'],$args->post['lang']);
            $this->$type($msg);
            break;
        case 'Back' :
            $this->_session->set( 'form[Sentence]', $args->post['Sentence'] );
            break;
        }
        return $this->router->url('admin_word_editlang',
                                  array('wordcode'=>$args->post['EngCode'],
                                        'shortcode'=>'en')
                                  , false);
    }
    /**
     * handles search action for translations
     *
     * @access public
     * @return AdminWordFindTranslationsPage
     */
    public function findTranslations(){
        $page = new AdminWordFindTranslationsPage();
        $page->nav = $this->getNavigationData();
        $page->langarr = $this->model->getLangarr($page->nav['scope']);
        $page->formdata = $this->getFormData(array('EngCode','EngDesc','Sentence','lang'),$page->nav);
        return $page;
    }
    
    /**
     * Handles submission of form on findTranslations page
     *
     * @access public
     * @param StdClass $args
     * @param ReadOnlyObject $action
     * @param ReadWriteObject $mem_redirect
     * @param ReadWriteObject $mem_resend
     * @return redirect / false
     */
    public function findTranslationsCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (!$nav = $this->baseCallback($args,$mem_redirect,'findTranslations')){return false;}
        $searchparams = array();
        foreach (array('EngCode','EngDesc','Sentence','lang') as $item){
            if (isset($args->post[$item])){
                if (strlen($args->post[$item])>0){
                    $searchparams[$item] = $args->post[$item];
                }
            }
        }
        $this->_session->set( 'trData', $this->model->getFindData($searchparams) );
        foreach($this->_session->get('trData') as $key => $item){
            if ($this->checkScope($this->wordrights['Words']['Scope'],$item->TrShortcode)){
                $this->_session->get('trData'][$key)->inScope = true;
            } else {
                $this->_session->get('trData'][$key)->inScope = false;
            }
        }
        return false;
    }

    /**
     * handles edit action of a single translation
     *
     * @access public
     * @return AdminWordEditTranslationPage
     */
    public function editTranslation(){
        $page = new AdminWordEditTranslationPage();
        $nav = $this->getNavigationData();
        $page->langarr = $this->model->getLangarr($nav['scope']);
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
            $data = $this->model->getTranslationData('edit','',$page->nav['shortcode'],$wordcode);
            if (!isset($data->EngCode)){$data->EngCode = $wordcode;}
            $this->data = $data;
        } else {
            // no wordcode selected
            $wordcode = false;
            $this->data = null;
        }
        $page->formdata = $this->getFormData(array('EngCode','Sentence','EngDesc',
                                                    'EngDnt','EngSent','lang')
                                             ,$nav);
        return $page;
    }

    /**
     * Handles submission of form on editTranslation page
     *
     * @access public
     * @param StdClass $args
     * @param ReadOnlyObject $action
     * @param ReadWriteObject $mem_redirect
     * @param ReadWriteObject $mem_resend
     * @return redirect / false
     */
    public function editTranslationCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (!$nav = $this->baseCallback($args,$mem_redirect,'editTranslation')){return false;}
        if (isset($args->post['submitBtn'])){
            if ($args->post['lang']=='en'
                    && !($this->model->getEngSentByCode($this->_session->get('form']['EngCode']) == $_SESSION['form']['Sentence')
                            && $nav['level']<10)
                ){
                // dont do this if translation is equal to db and no admin rights
                
                // and continue with the second page
                return $this->router->url('admin_word_code', array(), false);
            }
            // check if the wordcode exists in English
            $wcexist = $this->model->wordcodeExist($args->post['EngCode'],'en');
            if ($wcexist->cnt > 0){
                list($id,$res) = $this->model->UpdateSingleTranslation($args->post);
                // write the old translation to the previousversion table
                if ($res == 2) {$this->words->MakeRevision($id,'words');}
                // get the flash notice/error
                list($type,$msg) = $this->getResultMsg($res,$args->post['EngCode'],$args->post['lang']);
            }
            else {
                $type = 'setFlashError';
                $msg = 'This wordcode does not yet exist. It needs to be created in English first.';
            }
            $this->$type($msg);
        }
        if (isset($args->post['findBtn'])){
            $this->_session->remove('form');
            return $this->router->url('admin_word_editlang',
                array('wordcode'=>$args->post['EngCode'],
                      'shortcode'=>$args->post['lang']), false);
        }        
//        case 'Delete' :
//            $res = $this->model->removeSingleTranslation($args->post);
//            break;
        
        return false;
    }
    /**
     * displays translation lists for a language
     *
     * @access public
     * @return object
     */
    public function showList(){
        $page = new AdminWordShowListPage;
        $page->type = $this->args_vars->request[3];
        $page->filter = $this->args_vars->request[4];        
        $page->nav = $this->getNavigationData();
        $page->langarr = $this->model->getLangarr($page->nav['scope']);
        
        if (!$this->checkScope($page->nav['scope'],$page->nav['shortcode'])){
            $page->noScope = true;
        } else {
            $page->noScope = false;
            $page->stat = $this->getStatistics($page->nav['idLanguage']);
            $page->data = $this->model->getTranslationData($page->type,
                                                            $page->filter,
                                                            $page->nav['shortcode']);
        }
        return $page;
    }
    
    /**
     * Handles submission of form on showList page
     *
     * @access public
     * @param StdClass $args
     * @param ReadOnlyObject $action
     * @param ReadWriteObject $mem_redirect
     * @param ReadWriteObject $mem_resend
     * @return redirect / false
     */
    public function showListCallback(StdClass $args, ReadOnlyObject $action, ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend){
        if (empty($args->post)) {return false;}
        $nav = $this->getNavigationData();
        foreach(array_keys($args->post) as $key){
            if (preg_match('#^Edit_(\d+)$#',$key,$id)){
                $wordcode = $this->model->getWordcodeById($id[1]);
                return $this->router->url('admin_word_editone', array('wordcode'=>$wordcode->code), false);    
            }
            if (preg_match('#^ThisIsOk_(\d+)$#',$key,$id)){
                $this->model->updateNoChanges($id[1]);
                return false;            
            }
        }
        return false;
    }


    /**
     * displays translation statistics for all languages
     *
     * @access public
     * @return object
     */
    public function showStatistics(){
        $page = new AdminWordShowStatisticsPage;
        $page->nav = $this->getNavigationData();
        $page->data = $this->getStatistics();
        return $page;
    }

    /**
     * define data to prefill the form with
     *
     * based on :
     * - POST values
     * - SESSION values
     * - Database values
     * - empty
     *
     * @access private
     * @param array $fields Array of fields that need to be filled
     * @vars array $vars Array of POST values
     * @return array Array with data to prefill the form with
     */
    private function getFormData($fields,$vars = null){
        $formdata = array();
        foreach ($fields as $field) {
            if (isset($vars[$field])){
                $formdata[$field] = $vars[$field];
            } elseif ($this->_session->has( 'form[' . $field . ']')){
                $formdata[$field] = $this->_session->get('form'][$field);
            } elseif (isset($this->data->$field)) {
                $formdata[$field] = $this->data->$field;
            } else {
                $formdata[$field] = '';
            }
            unset ($this->_session->get('form'][$field));
        }
        if ($formdata['lang']==''){
            if (isset($vars['shortcode'])){
                $formdata['lang'] = $vars['shortcode'];
            } else {
                $formdata['lang'] = 'en';
            }
        }
        return $formdata;      
    }

    /**
     * creates textmessage as result for insert/update action
     *
     * @access private
     * @param integer $res Number of affected rows of Insert-Update query
     * @param string $code Wordcode
     * @param string $shortcode Language shortcode
     * @return array Array: 0=>type of message (notice/error), 1=>Text of message
     */
    private function getResultMsg($res,$code,$shortcode){
    // prepare the result message
        switch($res){
        case 1 :
            $type = 'setFlashNotice';
            $msg = 'Wordcode "'.$code.'" has been added succesfully. Language: '.$this->words->get('lang_'.$shortcode);
            MOD_log::get()->write('inserting '.$code.' in '.$shortcode, 'AdminWord');
            break;
        case 0 :
        case 2 :
            $type = 'setFlashNotice';
            $msg = 'Wordcode "'.$code.'" has been updated succesfully. Language: '.$this->words->get('lang_'.$shortcode);
            MOD_log::get()->write('updating '.$code.' in '.$shortcode, 'AdminWord');
            break;
        }
        return array($type,$msg);
    }
    /**
     * calculates translation statistics for all languages or single language
     *
     * @access private
     * @return array Basic data for translation statistics
     */
    private function getStatistics($lang = null){
        $englishLength = $this->model->getEnglishTotalLength();
        $allLength = $this->model->getTranslationLength($lang);
        // prepare the data to be shown on the page
        foreach ($allLength as $key => $langData) {
            $data[$key]['perc'] = ($langData->translated / $englishLength->cnt) * 100;
            $data[$key]['name'] = $langData->englishName;
            $data[$key]['scope'] = $this->checkScope($this->wordrights['Words']['Scope'],$langData->shortCode);
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
    /**
     * collects data used for navigation-texts and links
     *
     * @access private
     * @return array Containing all data
     */
    private function getNavigationData(){

        // collect volunteerrights for this member;
        list($this->member, $this->wordrights) = $this->checkRights('Words');
        $rights = MOD_right::get();
        $nav = array();
        // get the base language from the session
        $nav['idLanguage'] = (int)$this->_session->get('IdLanguage');
        $nav['shortcode'] = $this->_session->get('lang');
        // translated full text of user language
        $nav['currentLanguage'] = $this->words->get('lang_'.$nav['shortcode']);
        // array of objects with scope languages
        $nav['scope'] = $this->wordrights['Words']['Scope'];
        if ($nav['scope']=='"All"'){
            $this->langarr = array('All');
            //$nav['scopetext'] = 'All';
        } else {
            $sc_arr = preg_split('#[,; ]+#',str_replace('"','',$nav['scope']));
            $this->langarr = $sc_arr;
            //$nav['scopetext'] = $this->glueScope($sc_arr);
        }
        $nav['level'] = $this->wordrights['Words']['Level'];
        $nav['grep'] = $rights->hasRight('Grep');
        return $nav;
    }

    /**
     * Perform base functions for the callbacks
     *
     * - return false if no post values present
     * - fill error array if any not well formatted data submitted and return false
     * - return navigation data if everything alright
     *
     * @access private
     * @param array $args Array of request arguments
     * @param object $mem_redirect Object used by Rox to handle callback functions
     * @return array Array of some base variables / boolean false if something not alright
     */
    private function baseCallback($args,$mem_redirect,$type){
        if (empty($args->post)) {return false;}
        // set posted variables in the session
        foreach ($args->post as $key => $postvar){
            $this->_session->set( 'form[' . $key . ']', $postvar );
        }
        $errors = $this->model->{$type.'FormCheck'}($args->post);
        if (!empty($errors)) {
            $mem_redirect->vars = $args->post;
            $mem_redirect->errors = $errors;
            // $mem_redirect->action = $action;
            return false;
        }
        return $this->getNavigationData();
    }

    public function noUpdateNeeded() {
        $id = intval($this->route_vars['id']);
        $callback = $this->args_vars->get['callback'];
        if (!$id) {
            $result = "false";
        } else {
            $result = $this->model->setNoUpdateNeeded($id);
        }
        header('Content-type: application/javascript, charset=utf-8');
        $javascript = $callback . '( ' . json_encode($result) . ')';
        echo $javascript . "\n";
        exit;
    }
}