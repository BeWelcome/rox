<?php

  /** 
   * 
   */
class PageWithRoxLayout extends PageWithHTML
{
    /*
     * Return a list of stylesheets to be included.
     */
    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        // TODO: merge main.css and bw_yaml.css (for fewer HTTP reqs)5C
        $stylesheets[] = 'styles/YAML/main.css';
        $stylesheets[] = 'styles/YAML/bw_yaml.css';
        return $stylesheets;
    }
    
    protected function init()
    {
        $this->page_title = 'BeWelcome';
        $words = $this->getWords();
				
				// Todo : I am unsure with what I did here, I did it to avoid a warning
				// with a not initialized object, but I have not fully understood 
				// how/when wwsilent->default_meta_description is supposed to be initialized
				// JeanYves
				if (empty($this->wwsilent->default_meta_description)) {
					$this->meta_description=$words->getBuffered("default_meta_description");
				}
				else {
        	$this->meta_description = $this->wwsilent->default_meta_description;
				}
				if (empty($this->wwsilent->default_meta_keyword)) {
					$this->meta_keyword=$words->getBuffered("default_meta_keyword") ;
				}
				else {
        	$this->meta_keyword = $this->wwsilent->default_meta_keyword;
				}
    }
    
    /*
     * The idea was that stylesheetpatches was for MSIE
     */
    protected function getStylesheetPatches()
    {
        $stylesheet_patches = parent::getStylesheetPatches();
        $stylesheet_patches[] = 'styles/YAML/patches/iehacks_3col_vlines.css';
        return $stylesheet_patches;
    }

    /** 
     * Return a list of items to show in the sub menu.  Each item is
     * an array of keyword, url and translatable Word
     */
    protected function getTopmenuItems()
    {
        $items = array();
        
        if (APP_User::isBWLoggedIn('NeedMore,Pending')) {
            $items[] = array('main', 'main', 'Menu');
            $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
            $items[] = array('profile', 'people/'.$username, $username, true);
        }
        // $items[] = array('searchmembers', 'searchmembers/index', 'FindMembers');
        // $items[] = array('forums', 'forums', 'Community');
        // $items[] = array('groups', 'bw/groups.php', 'Groups');
        // $items[] = array('gallery', 'gallery', 'Gallery');
        // $items[] = array('getanswers', 'about', 'GetAnswers');
        $items[] = array('findhosts', 'findmembers', 'FindHosts');
        $items[] = array('explore', 'explore', 'Explore');
        if (APP_User::isBWLoggedIn('NeedMore,Pending')) {
            $items[] = array('messages', 'messages', 'Messages');
        }
        
        return $items;
    }
    
    /*
     * Override this method to define which of the top menu items is active, e.g.
     * return 'forums';
     */
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

    /*
     * Andreas thinks it's the top right stuff, with 0 members online, login and signup
     */
    protected function topnav()
    {

    }
    
    
    protected function topmenu()
    {
        $words = $this->getWords();
        $menu_items = $this->getTopmenuItems();
        $active_menu_item = $this->getTopmenuActiveItem();
        
        require TEMPLATE_DIR . 'shared/roxpage/topmenu.php';
    }

    /** 
     * A tiny wee quicksearch box
     */ 
    protected function quicksearch()
    {
        $words = $this->getWords();
        $logged_in = APP_User::isBWLoggedIn('NeedMore,Pending');
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
        } else {
            $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
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

    /* also check htdocs/bw/layout/footer.php
     */   
    protected function footer()
    {
        require SCRIPT_BASE . "build/rox/templates/footer.php";
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
        echo '<br/><br/>'; // TODO: Replace HTML breaks by layout directive
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
    
}


?>
