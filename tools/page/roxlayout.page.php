<?php

  /**
   *
   */
class PageWithRoxLayout extends PageWithHTML
{

    protected $meta_description ;
    protected $meta_keyword ;
    protected $meta_robots ;
    
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

    protected function getPage_meta_keyword()
    {
        $words = $this->getWords();
        if (empty($this->meta_keyword)) {
            $this->meta_keyword = $words->getBuffered("default_meta_keyword");
        }
        return($this->meta_keyword) ;
    }
    
    public function SetMetaKey($ss)
    {
        $words = $this->getWords();
        $this->meta_keyword = $ss;
    }

    protected function getPage_meta_robots()
    {
        if (empty($this->meta_robots)) {
            $this->meta_robots = 'All' ;
        }
        return($this->meta_robots) ;
    }
    
    public function SetMetaRobots($ss) 
    {
            $this->meta_robots = $ss ;
    }

    protected function getPage_meta_description() 
    {
        $words = $this->getWords();
        if (empty($this->meta_description)) {
            $this->meta_description = $words->getBuffered("default_meta_description");
        }
        return($this->meta_description);
    }
    public function SetMetaDescription($ss)
    {
        $this->meta_description = $ss;
    }
    
    protected function init()
    {
        $this->page_title = 'BeWelcome';
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
            $items[] = array('profile', 'bw/member.php?cid='.$username, 'MyProfile');
        }
        $items[] = array('searchmembers', 'searchmembers', 'FindMembers');
        $items[] = array('trips', 'trip', 'Trips');
        $items[] = array('blogs', 'blog', 'Blogs');
        $items[] = array('forums', 'forums', 'Community');
        $items[] = array('groups', 'groups', 'Groups');
        $items[] = array('gallery', 'gallery', 'Gallery');
        $items[] = array('getanswers', 'about', 'GetAnswers');

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

        require TEMPLATE_DIR . 'shared/roxpage/topnav.php';
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
        return $this->page_title;
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
        $this->volunteerBar();
    }


    protected function volunteerBar()
    {
        $model = new VolunteerbarModel();

        $numberPersonsToBeAccepted = $model->getNumberPersonsToBeAccepted() ;
        $numberPersonsToBeChecked = $model->getNumberPersonsToBeChecked() ;
        $numberMessagesToBeChecked = $model->getNumberPersonsToAcceptInGroup() ;
        $numberSpamToBeChecked = $model->getNumberSpamToBeChecked() ;
        $numberPersonsToAcceptInGroup = $model->getNumberPersonsToAcceptInGroup() ;
        $numberPendingLocalMess = $model->getNumberPendingLocalMess() ;

        $widget = $this->createWidget('VolunteerbarWidget');
        $widget->render();
    }

}


?>
