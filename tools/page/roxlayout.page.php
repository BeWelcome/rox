<?php


class PageWithRoxLayout extends PageWithHTML
{
    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/YAML/main.css';
        $stylesheets[] = 'styles/YAML/bw_yaml.css';
        return $stylesheets;
    }
    
    protected function getStylesheetPatches()
    {
        $stylesheet_patches = parent::getStylesheetPatches();
        $stylesheet_patches[] = 'styles/YAML/patches/iehacks_3col_vlines.css';
        return $stylesheet_patches;
    }
    
    protected function getTopmenuItems()
    {
        $items = array();
        
        if (APP_User::isBWLoggedIn()) {
            $items[] = array('main', 'main', 'Menu');
            $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
            $items[] = array('profile', 'bw/member.php?cid='.$username, 'MyProfile');
        }
        $items[] = array('searchmembers', 'searchmembers/index', 'FindMembers');
        $items[] = array('forums', 'forums', 'Community');
        $items[] = array('groups', 'bw/groups.php', 'Groups');
        $items[] = array('gallery', 'gallery', 'Gallery');
        $items[] = array('getanswers', 'about', 'GetAnswers');
        
        return $items;
    }
    
    protected function getTopmenuActiveItem() {
        return 0;
    }
    
    protected function getSubmenuItems() {
        return 0;
    }
    
    protected function getSubmenuActiveItem() {
        return 0;
    }
    
    protected function body()
    {
        require TEMPLATE_DIR . 'shared/roxpage/body.php';
    }
    
    protected function topnav()
    {
        $words = $this->getWords();
        $logged_in = APP_User::isBWLoggedIn();
        if (!$logged_in) {
            $request = PRequest::get()->request;
            if (!isset($request[0])) {
                $login_url = 'login';
            } else switch ($request[0]) {
                case 'login':
                case 'main':
                case 'start':
                    $login_url = 'login';
                    break;
                default:
                    $login_url = 'login/'.implode('/', $request);
            }
        }
        
        if (class_exists('MOD_online')) {
            $who_is_online_count = MOD_online::get()->howManyMembersOnline();
        } else {
            // echo 'MOD_online not active';
            if (isset($_SESSION['WhoIsOnlineCount'])) {
                $who_is_online_count = $_SESSION['WhoIsOnlineCount']; // MOD_whoisonline::get()->whoIsOnlineCount();
            } else {
                $who_is_online_count = 0;
            }
        }  
        
        require TEMPLATE_DIR . 'shared/roxpage/topnav.php';
    }
    
    
    protected function topmenu()
    {
        $words = $this->getWords();
        $menu_items = $this->getTopmenuItems();
        $active_menu_item = $this->getTopmenuActiveItem();
        
        require TEMPLATE_DIR . 'shared/roxpage/topmenu.php';
    }
    
    protected function quicksearch()
    {
        PPostHandler::setCallback('quicksearch_callbackId', 'SearchmembersController', 'index');
        
        require TEMPLATE_DIR . 'shared/roxpage/quicksearch.php';
    }
    
    protected function columnsArea()
    {
        $side_column_names = $this->getColumnNames();
        $mid_column_name = array_pop($side_column_names);
        
        require TEMPLATE_DIR . 'shared/roxpage/columnsarea.php';
    }
    
    protected function getPageTitle() {
        return 'BeWelcome';
    }
    
    protected function teaser()
    {
        require TEMPLATE_DIR . 'shared/roxpage/teaser.php';
    }
    
    
    protected function teaserContent()
    {
        require TEMPLATE_DIR . 'shared/roxpage/teasercontent.php';
    }
    
    protected function submenu()
    {
        $words = $this->getWords();
        require TEMPLATE_DIR . 'shared/roxpage/submenu.php';
    }
    
    protected function footer()
    {
        $this->showTemplate('apps/rox/footer.php', array(
            'flagList' => $this->_buildFlagList(),
            'versionInfo' => $this->_getVersionInfo(),
            'bugreportLink' => $this->_getBugreportLink(),
        ));
    }
    
    protected function leftoverTranslationLinks()
    {
        $tr_buffer_body = $this->getWords()->flushBuffer();
        if($this->_tr_buffer_header != '') {
            echo '<br>Remaining words in header: ' . $this->_tr_buffer_header . '<br><br>';
        }
        if($tr_buffer_body != '') {
            echo '<br>Remaining words in body: ' . $tr_buffer_body . '<br><br>';
        }
    }
    
    protected function debugInfo()
    {
        if (PVars::get()->debug) {
            require TEMPLATE_DIR . 'shared/roxpage/debuginfo.php';
        }
    }
    
    protected function getColumnNames() {
        return array('col1', 'col2', 'col3');
    }
    
    private function _column($column_name)
    {
        $method_name = 'column_'.$column_name;
        $this->$method_name();
    }
    
    protected function column_col1()
    {
        $this->leftSidebar();
        $this->volunteerBar();
    }
    
    
    protected function volunteerBar()
    {
        $model = new VolunteerbarModel();

		 		$numberPersonsToBeAccepted=$model->getNumberPersonsToBeAccepted() ;
		 		$numberPersonsToBeChecked=$model->getNumberPersonsToBeChecked() ;
		 		$numberMessagesToBeChecked=$model->getNumberPersonsToAcceptInGroup() ;
		 		$numberSpamToBeChecked=$model->getNumberSpamToBeChecked() ;
		 		$numberPersonsToAcceptInGroup=$model->getNumberPersonsToAcceptInGroup() ;
				
        $widget = $this->createWidget('VolunteerbarWidget');
        $widget->render();
    }
    
    
    // TODO: move to a better place  -- and rename to languageSelector
    protected function _buildFlagList()
    {
        $model = new FlaglistModel();
        $languages = $model->getLanguages();
        $flaglist = '';
        $request_string = implode('/',PVars::__get('request'));
        
        foreach($languages as $language) {
            $abbr = $language->ShortCode;
            $title = $language->Name;
            $png = $abbr.'.png';
            if (!isset($_SESSION['lang'])) {
                // hmm
            } else { // if ($_SESSION['lang'] == $abbr) {               
                $flaglist .=
                    "<a href=\"rox/in/".$abbr.'/'.$request_string.
                    "\">"
                    . $title . "</a>\n"
                ;
            }
        }
        
        return $flaglist;
    }
    
    
    /**
     * used in footer
     */
    protected function _getVersionInfo()
    {
        // TODO: add alpha/test/live
        if (file_exists("revision.txt")) {   // htdocs is default dir
            $version = 'r' . file_get_contents("revision.txt");
        } else {
            $version = "local";
        }
        return $version;
    }
    
    
    /**
     * used in footer
     */
    protected function _getBugreportLink()
    {
        $url = "http://www.bevolunteer.org/trac/newticket?";
        $url .= "description=";
        $info =
            'BW Rox version: ' . $this->_getVersionInfo() . "\n" .
            'user agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\n" .
            'request uri: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']  . "\n";
        $url .= urlencode($info);
        $url .= "&summary=bug%20report";
        return $url;
    }
}


?>