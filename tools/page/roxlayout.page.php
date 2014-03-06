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
        $stylesheets[] = 'styles/css/minimal/minimal.css?1';
        $stylesheets[] = 'styles/css/bewelcome.css?1';
        if (PVars::getObj('development')->uncompress_css != 1) {
            $stylesheets = str_replace(".css", ".min.css", $stylesheets);
            return $stylesheets;
        } else {
            return $stylesheets;
        }
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
        //$stylesheet_patches[] = 'styles/css/minimal/patches/iehacks_3col_vlines.css';
        return $stylesheet_patches;
    }

    /**
     * Return a list of items to show in the sub menu.  Each item is
     * an array of keyword, url and translatable Word
     */
/*    protected function getTopmenuItems()
    {
        $items = array();

        if (APP_User::isBWLoggedIn()) {
            $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
            $items[] = array('profile', 'members/'.$username, $username, true);
        }
        $items[] = array('getanswers', 'about', 'GetAnswers');
        $items[] = array('findhosts', 'findmembers', 'FindHosts');
        $items[] = array('explore', 'explore', 'Explore');
        if (APP_User::isBWLoggedIn()) {
            $items[] = array('messages', 'messages', 'Messages');
        }
        
        return $items;
    }

    /*
     * Override this method to define which of the top menu items is active, e.g.
     * return 'forums';
     */
/*    protected function getTopmenuActiveItem() {
        return 0;
    }
*/
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
        $model = new VolunteerbarModel();
        $numberPersonsToBeAccepted = $model->getNumberPersonsToBeAccepted() ;
        $numberReportedComments = $model->getNumberReportedComments();
        $numberPersonsToBeChecked = $model->getNumberPersonsToBeChecked() ;
        $numberSpamToBeChecked = $model->getNumberSpamToBeChecked() ;
        $numberPersonsToAcceptInGroup = $model->getNumberPersonsToAcceptInGroup() ;
        $R = MOD_right::get();
        
        $logged_in = APP_User::IsBWLoggedIn("NeedMore,Pending");
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
                    $login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);
            }
        } else {
            $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
            if (isset($_SESSION["IdMember"])) {
                $IdMember = intval($_SESSION["IdMember"]);
                $roxmodel = new Rox();
                $numberOfNewMessagees = $roxmodel->getNewMessagesNumber($IdMember);
            }
        }

        /*if (class_exists('MOD_online')) {
            $who_is_online_count = MOD_online::get()->howManyMembersOnline();
        } else {
            // echo 'MOD_online not active';
            if (isset($_SESSION['WhoIsOnlineCount'])) {
                $who_is_online_count = $_SESSION['WhoIsOnlineCount']; // MOD_whoisonline::get()->whoIsOnlineCount();
            } else {
                $who_is_online_count = 0;
            }
        }*/
        if ($numberOfNewMessagees > 0) {
            $envelopestyle = "fa fa-envelope"; 
            $nbOfNewMessagees = "(" . intval($numberOfNewMessagees) . ")";
            echo $words->flushBuffer();
    } else {
        $envelopestyle = "fa fa-envelope-o";
    }
        require TEMPLATE_DIR . 'shared/roxpage/topnav.php';
    }


    protected function topmenu()
    {
        $words = $this->getWords();
        $menu_items = $this->getTopmenuItems();
        $active_menu_item = $this->getTopmenuActiveItem();
        $logged_in = APP_User::isBWLoggedIn('NeedMore,Pending');
        $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
        $rights = new MOD_right();
        $volunteer = $rights->hasRightAny();
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
                    $login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);
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
    }

    protected function column_col2(){

    }

    protected function volunteerMenu()
    {
        $widget = $this->createWidget('VolunteermenuWidget');
        $widget->render();
    }

    /**
     * shortcut to get callback variables for form
     *
     * @param string $controller controller to callback to
     * @param string $method method in controller
     *
     * @access protected
     * @return string
     */
    protected function getCallbackOutput($controller, $method)
    {
        return $this->layoutkit->formkit->setPostCallback($controller, $method);
    }

    /**
     * returns the redirected post vars, if any
     *
     * @param string $array which saved array to return from the redirected mem
     *
     * @access protected
     * @return array
     */
    protected function getRedirectedMem($array)
    {
        if (($redirected = $this->layoutkit->formkit->mem_from_redirect) && $redirected->$array)
        {
            return $redirected->$array;
        }
        else
        {
            return array();
        }
    }

    /**
     * Get flash message and remove from session if needed
     *
     * @param string $type Type of flash, i.e. "error" or "notice"
     * @param bool $remove True if message should be removed from session,
     *                     false by default
     *
     * @return string Flash message
     */
    private function getFlash($type, $remove = false) {
        $flashName = 'flash_' . $type;
        if (isset($_SESSION[$flashName])) {
            $flashMessage = $_SESSION[$flashName];
        } else {
            $flashMessage = false;
        }
        if ($remove) {
            unset($_SESSION[$flashName]);
        }
        return $flashMessage;
    }

    /**
     * Get flash notice message and remove from session if needed
     * @see RoxControllerBase::setFlashNotice() for counterpart
     * @see templates/shared/roxpage/body.php
     *
     * @param bool $remove True if message should be removed from session,
     *                     false by default
     *
     * @return string Flash notice message
     */
    public function getFlashNotice($remove = false) {
        return $this->getFlash('notice', $remove);
    }

    /**
     * Get flash error message and remove from session if needed
     * @see RoxControllerBase::setFlashError() for counterpart
     * @see templates/shared/roxpage/body.php
     *
     * @param bool $remove True if message should be removed from session,
     *                     false by default
     *
     * @return string Flash error message
     */
    public function getFlashError($remove = false) {
        return $this->getFlash('error', $remove);
    }

}
