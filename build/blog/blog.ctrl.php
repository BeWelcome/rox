<?php
/**
 * blog controller
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: blog.ctrl.php 56 2006-06-21 13:53:57Z roland $
 */
class BlogController extends RoxControllerBase {
    private $_model;
    private $_view;

    public function __construct() {
        parent::__construct();
        $this->_model = new Blog();
        $this->_view =  new BlogView($this->_model);
    }

    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    public function index()
    {
        $P = PVars::getObj('page');
        $vw = new ViewWrap($this->_view);
        $cw = new ViewWrap($this);

        // index is called when http request = ./blog
        if (PPostHandler::isHandling()) {
            return;
        }
        $request = PRequest::get()->request;
        $member = $this->_model->getLoggedInMember();
        //$User = A PP_User::login();
        if (!isset($request[1]))
            $request[1] = '';
        // user bar
        // show the userbar always for now:
        /*if ($User && $request[1] != 'tags') { */
//            $P->newBar .= $vw->userbar();
        /*} */
        $bloguser = 0;
        $RSS = false;
        switch ($request[1])
        {
            case 'ajax':
                if (!isset($request[2]))
                    PPHP::PExit();
                switch ($request[2])
                {
                    case 'post':
                        $this->ajaxPost();
                        break;
                }
                break;
            case 'suggestTags':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($request[2]))
                {
                    PPHP::PExit();
                }
                $new_tags = $this->_model->suggestTags($request[2]);
                echo $this->_view->generateClickableTagSuggestions($new_tags);
                PPHP::PExit();
                break;

            case 'suggestLocation':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($request[2])) {
                    PPHP::PExit();
                }
                $locations = $this->_model->suggestLocation($request[2]);
                echo $this->_view->generateLocationOverview($locations);
                PPHP::PExit();
                break;
            case 'create':
                if (!$member)
                {
                    PRequest::home();
                }
                if (isset($request[2]) && $request[2] == 'finish' && isset($request[3]) && $this->_model->isPostId($request[3]))
                {
                    $page = new BlogSinglePostPage($this->_model);
                    $page->member = $member;
                    $page->post = $this->_model->getPost($request[3]);
                }
                else
                {
                    $page = new BlogCreatePage($this->_model);
                }
                return $page;
            case 'del':
                if (!$member || !isset($request[2]) || !$this->_model->isUserPost($member->id, $request[2]))
                {
                    PRequest::home();
                }
                $post = $this->_model->getPost($request[2]);
                $p = new BlogDeletePage($this->_model);
                $p->member = $member;
                $p->post = $post;
                return $p;
            case 'edit':
                if (!$member || !isset($request[2]) || !$this->_model->isUserPost($member->id, $request[2]))
                {
                    PRequest::home();
                }
            	if (isset($request[3]) && $request[3] == 'finish')
                {
                    $p = new BlogSinglePostPage($this->_model);
                    $p->member = $member;
                    $p->post = $this->_model->getPost($request[2]);
                    return $p;
            	}
                else
                {
					//$callbackId = $this->editProcess((int)$request[2]);
                    //$vars =& PPostHandler::getVars($callbackId);
                    if (!isset($vars['errors']) || !is_array($vars['errors'])) {
                        $vars['errors'] = array();
                    }
                    $this->_editFill($request[2], $vars);
                    $p = new BlogEditPage($this->_model);
                    $p->post = $this->_model->getPost($request[2]);
                    $p->member = $member;
                    $p->vars = $vars;
                    return $p;
					$P->content .= $vw->editForm((int)$request[2], $callbackId);
				}
                break;

            case 'search':
                if (!empty($this->args_vars->get['s']) && (strlen($this->args_vars->get['s']) >= 3))
                {
                    $search = $this->args_vars->get['s'];
                    $tagsposts = $this->_model->getTaggedPostsIt($search);
                    $posts = $this->_model->searchPosts($search);
                }
                else
                {
                    $error = 'To few arguments';
                    $posts = false;
                    $tagsposts = false;
                    $search = '';
                }
                $p = new BlogSearchPage($this->_model);
                $p->posts = $posts;
                $p->tagged_posts = $tagsposts;
                $p->search = $search;
                return $p;
                $P->content .= $vw->searchPage($posts,$tagsposts);
                break;
/* removed - references user app
            case 'settings':
                $p = new BlogSettingsPage($this->_model);
                return $p;
                $P->content .= $vw->settingsForm();
                break;
*/

            case 'tags':
                $p = new BlogTagsPage($this->_model);
                $p->tag = (isset($request[2]) ? $request[2] : false);
                return $p;
                $P->content .= $vw->tags((isset($request[2])?$request[2]:false));
                break;

            case 'cat':
                $p = new BlogCategoriesPage($this->_model);
                return $p;
                break;

            default:
                $page = ((isset($this->args_vars->get['page']) && intval($this->args_vars->get['page'])) ? intval($this->args_vars->get['page']) : 1);

                // display blogs of user $request[1]
                $memberBlog = $this->_model->getMemberByUsername($request[1]);
                if ($memberBlog)
                {
                    if (!isset($request[2]))
                        $request[2] = '';
                    switch ($request[2])
                    {
                        case 'cat':
                            if (isset($request[3]))
                            {
                                $p = new BlogPage($this->_model);
                                $p->page = $page;
                                $p->category = $request[3];
                                $p->member = $memberBlog;
                                $p->initPager($this->_model->countRecentPosts($memberBlog->id, $request[3]), $page);
                                $p->posts = $this->_model->getRecentPostsArray($memberBlog->id, $request[3], $page);
                                break;
                            }
                            // if we're not dealing with a category, fall through and hit the default
                        case '':
                        default:
                            // show different blog layout for public visitors
                            if ($post = $this->_model->getPost($request[2]))
                            {
                                $p = new BlogSinglePostPage($this->_model);
                                $p->member = $memberBlog;
                                $p->post = $post;
                            }
                            else
                            {
                                $p = new BlogPage($this->_model);
                                $p->page = $page;
                                $p->member = $memberBlog;
                                $p->initPager($this->_model->countRecentPosts($memberBlog->id, false), $page);
                                $p->posts = $this->_model->getRecentPostsArray($memberBlog->id, false, $page);
                            }
                            break;
                    }
                }
                else
                {
                    $p = new BlogPage($this->_model);
                    $p->page = $page;
                    $p->initPager($this->_model->countRecentPosts(false, false), $page);
                    $p->posts = $this->_model->getRecentPostsArray(false, false, $page);
                }
                return $p;
        }
    }

    private function ajaxPost() {
        PRequest::ignoreCurrentRequest();
        if (!$member = $this->_model->getLoggedInMember())
            return false;
    	// Modifying a blog post using an ajax-request
        if( isset($_GET['item']) ) {
            $id = $_GET['item'];
            if ($this->_model->isUserPost($member->id, $id)) {
                if( isset($_GET['title']) ) {
                    $str = htmlentities($_GET['title'], ENT_QUOTES, "UTF-8");
                    if (!empty($str)) {
                    $this->_model->ajaxEditPost($id,$str,'');
                    $str2 = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                    echo $str2;
                    } else echo 'Can`t be empty! Click to edit!';
                }
                if( isset($_GET['text']) ) {
                    $str = htmlentities($_GET['text'], ENT_QUOTES, "UTF-8");
                    $this->_model->ajaxEditPost($id,'',$str);
                    $str = utf8_decode(addslashes(preg_replace("/\r|\n/s", "",nl2br($str))));
                    echo $str;
                }
                if( isset($_GET['geoid']) ) {
                    $str = (int)$_GET['geoid'];
                    $result = $this->_model->ajaxEditPost($id,'','',$str);
                    echo $result ? 'OK' : 'NO';
                }
            PPHP::PExit();
            }
        }
        echo 'Error!';
        PPHP::PExit();
    }

    // 2006-11-23 19:13:59 rs Copied to Message class :o
    private function _cleanupText($txt)
    {
        $str = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$txt.'</body></html>';
        $doc = @DOMDocument::loadHTML($str);
        if ($doc) {
            $sanitize = new PSafeHTML($doc);
            $sanitize->allow('html');
            $sanitize->allow('body');
            $sanitize->allow('p');
            $sanitize->allow('div');
            $sanitize->allow('b');
            $sanitize->allow('i');
            $sanitize->allow('u');
            $sanitize->allow('a');
            $sanitize->allow('em');
            $sanitize->allow('strong');
            $sanitize->allow('hr');
            $sanitize->allow('span');
            $sanitize->allow('ul');
            $sanitize->allow('il');
            $sanitize->allow('img');
            $sanitize->allow('font');
            $sanitize->allow('strike');
            $sanitize->allow('br');
            $sanitize->allow('blockquote');
            $sanitize->allow('div');
            $sanitize->allow('h1');
            $sanitize->allow('h2');
            $sanitize->allow('h3');
            $sanitize->allow('h4');
            $sanitize->allow('h5');
            $sanitize->allow('ul');
            $sanitize->allow('ol');
            $sanitize->allow('li');

            $sanitize->allowAttribute('color');
            $sanitize->allowAttribute('bgcolor');
            $sanitize->allowAttribute('href');
            $sanitize->allowAttribute('style');
            $sanitize->allowAttribute('class');
            $sanitize->allowAttribute('width');
            $sanitize->allowAttribute('height');
            $sanitize->allowAttribute('src');
            $sanitize->allowAttribute('alt');
            $sanitize->allowAttribute('title');
            $sanitize->clean();
            $doc = $sanitize->getDoc();
            $nodes = $doc->x->query('/html/body/node()');
            $ret = '';
            foreach ($nodes as $node) {
                $ret .= $doc->saveXML($node);
            }
            return $ret;
        } else {
            // invalid HTML
            return '';
        }

    }

    /**
     * Fills the posthandler vars with the blog from $blogId.
     *
     * @return false if no blog could be found with id $blogId, otherwise true.
     */
    private function _editFill($blogId, &$vars)
    {
        if (!$b = $this->_model->getEditData($blogId))
            return false;
        $vars['id']          = $blogId;
        $vars['t']           = $b->blog_title;
        $vars['txt']         = $b->blog_text;
        $vars['tr']          = $b->trip_id_foreign;
        $vars['flag-sticky'] = $b->is_sticky;
        $vars['trip_id_foreign'] = $b->trip_id_foreign;
        $vars['cat']         = $b->category;
        $vars['vis'] = 'pub';
        if ($b->is_private)
            $vars['vis'] = 'pri';
        if ($b->is_protected)
            $vars['vis'] = 'prt';
        if ($b->blog_start === null) {
            $vars['sty'] = '';
            $vars['stm'] = '';
            $vars['std'] = '';
            $vars['date'] = '';
        } else {
            $vars['sty'] = date('Y', strtotime($b->blog_start));
            $vars['stm'] = idate('m', strtotime($b->blog_start));
            $vars['std'] = date('d', strtotime($b->blog_start));
            $vars['date'] = date('d.m.Y', strtotime($b->blog_start));
        }
        if ($b->latitude) {
            $vars['latitude'] = $b->latitude;
        } else {
            $vars['latitude'] = '';
        }
        if ($b->longitude) {
            $vars['longitude'] = $b->longitude;
        } else {
            $vars['longitude'] = '';
        }
        if ($b->blog_geonameid) {
            $vars['geonameid'] = $b->blog_geonameid;
        } else {
            $vars['geonameid'] = '';
        }
        if ($b->geonamesname) {
            $vars['geonamename'] = $b->geonamesname;
        } else {
            $vars['geonamename'] = '';
        }
        if ($b->fk_countrycode) {
            $vars['geonamecountrycode'] = $b->fk_countrycode;
        } else {
        	$vars['geonamecountrycode'] = '';
        }
        if ($b->geonamecountry) {
            $vars['geonamecountry'] = $b->geonamecountry;
        } else {
            $vars['geonamecountry'] = '';
        }
        if ($b->fk_admincode) {
            $vars['admincode'] = $b->fk_admincode;
        } else {
        	$vars['admincode'] = '';
        }

        $tagIt = $this->_model->getTags($blogId);
        $tags = array();
        if ($tagIt) {
            foreach ($tagIt as $row) {
                $tags[] = $row->name;
            }
            $vars['tags'] = implode(', ', $tags);
        }
        return true;
    }

    private function _validateVars(&$vars)
    {
        $member = $this->_model->getLoggedInMember();
        $errors = array();
        // check title
        if (!isset($vars['t']) || empty($vars['t'])) {
            $errors[] = 'title';
        }
        // check text
        if ((!isset($vars['txt']) || empty($vars['txt'])) && (!isset($vars['tr']) || !strcmp($vars['tr'],'')!=0 || !$this->_model->isUserTrip($member->id, $vars['tr']))) {
            $errors[] = 'text';
        }
        // check category
        if (!isset($vars['cat']) || strcmp($vars['cat'],'')==0) {
            $vars['cat'] = false; // no category selected.
        } elseif (!$this->_model->isUserBlogCategory($member->id, $vars['cat'])) {
            $errors[] = 'category';
        }
        if (isset($vars['tr']) && strcmp($vars['tr'],'')!=0 && !$this->_model->isUserTrip($member->id, $vars['tr'])) {
            $errors[] = 'trip';
        }
        // geonames
        if (!isset($vars['latitude']) || $vars['latitude'] == '') {
            $vars['latitude'] = false;
        }
        if (!isset($vars['longitude']) || $vars['longitude'] == '') {
            $vars['longitude'] = false;
        }
        if (!isset($vars['geonameid']) || $vars['geonameid'] == '') {
            $vars['geonameid'] = false;
        }
        if (!isset($vars['geonamename']) || $vars['geonamename'] == '') {
            $vars['geonamename'] = false;
        }
        if (!isset($vars['geonamecountrycode']) || $vars['geonamecountrycode'] == '') {
            $vars['geonamecountrycode'] = false;
        }

        if (count($errors) > 0) {
            $vars['errors'] = $errors;
            return false;
        }
        return true;
    }

    /**
     * Processing creation of a blog.
     *
     * This is a POST callback function.
     *
     * Sets following errors in POST vars:
     * title        - invalid(empty) title.
     * text         - invalid(empty) text.
     * startdate    - wrongly formatted start date.
     * enddate      - wrongly formatted end date.
     * duration     - empty enddate and invalid duration.
     * category     - category is not belonging to user.
     * trip         - trip is not belonging to user.
     * inserror     - error performing db insertion.
     * tagerror     - error while updating tags.
     */
    public function createProcess($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$member = $this->_model->getLoggedInMember())
            return false;
        $vars = $args->post;
        $mem_redirect->post = $args->post;

        if (isset($vars['txt'])) {
            $vars['txt'] = $this->_cleanupText($vars['txt']);
        }

        if (!$this->_validateVars($vars)) {
            return false;
        }

        if (!$userId = $member->id) {
            $vars['errors'] = array('inserror');
            return false;
        }

        $flags = 0;

        /* removed from use, referencing user app
        if (isset($vars['flag-sticky']) && $User->hasRight('write_sticky@blog')) {
            $flags = ($flags | Blog::FLAG_STICKY);
        }
        */

        if (!isset($vars['vis']))
            $vars['vis'] = 'pub'; // Default (if none set: public)
        switch($vars['vis']) {
            case 'pub':
                break;

            case 'prt':
                $flags = ($flags | Blog::FLAG_VIEW_PROTECTED);
                break;

            default:
                $flags = ($flags | Blog::FLAG_VIEW_PRIVATE);
                break;
        }
        $trip = (isset($vars['tr']) && strcmp($vars['tr'],'')!=0) ? (int)$vars['tr'] : false;
        $blogId = $this->_model->createEntry($flags, $userId, $trip);

        if (isset($vars['date']) && (strlen($vars['date']) <= 10 && strlen($vars['date']) > 8)) {
            list($day, $month, $year) = preg_split('/[\/.-]/', $vars['date']);
            if (substr($month,0,1) == '0') $month = substr($month,1,2);
            if (substr($day,0,1) == '0') $day = substr($day,1,2);
            $start = mktime(0, 0, 0, (int)$month, (int)$day, (int)$year);
            $start = date('YmdHis', $start);
        } else {
            $start = false;
        }

        // Check if the location already exists in our DB and add it if necessary
        if ($vars['geonameid'] && $vars['latitude'] && $vars['longitude'] && $vars['geonamename'] && $vars['geonamecountrycode'] && $vars['admincode']) {
            $geoname_ok = $this->_model->checkGeonamesCache($vars['geonameid']);
        } else {
            $geoname_ok = false;
        }

        $start = is_null($start) ? false : $start;
        $geonameId = $geoname_ok ? $vars['geonameid'] : false;
        try {
            $this->_model->createData($blogId, $vars['t'], $vars['txt'], $start, $geonameId);
        } catch (PException $e) {
            if (PVars::get()->debug) {
                throw $e;
            } else {
                error_log($e->__toString());
            }
            // rollback!
            $this->_model->deleteEntry($blogId);
            $vars['errors'] = array('inserror');
            return false;
        }

        if ($trip) {
            $this->_model->setTripPosition($trip, $blogId);
        }

        if (!$this->_model->updateTags($blogId, explode(',', $vars['tags']))) {
            $vars['errors'] = array('tagerror');
            return false;
        }

        // 'Touch' the corresponding trip!
        if ($trip) {
            $TripModel = new Trip;
            $TripModel->touchTrip($trip);
        }

        $request = PRequest::get()->request;
        if ($request[0] == 'trip')
            return implode('/', $request).'/finish';
        return 'blog/create/finish/'.$blogId;
    }

    public function deleteProcess($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$member = $this->_model->getLoggedInMember())
            return false;
        $vars = $args->post;
        $vars['errors'] = array();
        $vars['messages'] = array();
        if (!isset($vars['id']))
            return "blog/{$member->Username}";
        if (!$this->_model->isPostId($vars['id']) || !$this->_model->isUserPost($member->id, $vars['id']))
            return "blog/{$member->Username}";
        if (isset($vars['n']) && $vars['n']) {
            $vars['messages'][] = 'not_deleted';
            return $ret;
        }
        if (isset($vars['y']) && $vars['y']) {
            $this->_model->deleteEntry($vars['id']);
            $this->_model->deleteData($vars['id']);
            $vars['messages'][] = 'deleted';
        }
        return "blog/{$member->Username}";
    }

    /**
     * Processing edit of a blog.
     *
     * This is a POST callback function.
     *
     * Sets following errors in POST vars:
     * title        - invalid(empty) title.
     * startdate    - wrongly formatted start date.
     * enddate      - wrongly formatted end date.
     * duration     - empty enddate and invalid duration.
     * category     - category is not belonging to user.
     * trip         - trip is not belonging to user.
     * upderror     - error performing db update.
     * tagerror     - error while updating tags.
     */
    public function editProcess($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$member = $this->_model->getLoggedInMember())
            return false;
        $userId = $member->id;
        $vars = $args->post;
        if (!isset($vars['id']) || !$this->_model->isUserPost($userId, $vars['id']))
            return false;
        if (isset($vars['txt'])) {
            $vars['txt'] = $this->_cleanupText($vars['txt']);
        }
        if (!$this->_validateVars($vars)) {
            return false;
        }

        $post = $this->_model->getPost($vars['id']);
        if (!$post)
            return false;
        $flags = $post->flags;

        // cannot write sticky blogs currently
        $flags = ($flags & ~(int)Blog::FLAG_STICKY);

        if (!isset($vars['vis']))
            $vars['vis'] = 'pri';
        switch($vars['vis']) {
            case 'pub':
                $flags = ($flags & ~(int)Blog::FLAG_VIEW_PROTECTED & ~(int)Blog::FLAG_VIEW_PRIVATE);
                break;

            case 'prt':
                $flags = ($flags & ~(int)Blog::FLAG_VIEW_PRIVATE | (int)Blog::FLAG_VIEW_PROTECTED);
                break;

            default:
                $flags = ($flags & ~(int)Blog::FLAG_VIEW_PROTECTED | (int)Blog::FLAG_VIEW_PRIVATE);
                break;
        }
        $tripId = (isset($vars['tr']) && strcmp($vars['tr'],'')!=0) ? (int)$vars['tr'] : false;

        $this->_model->updatePost($post->blog_id, $flags, $tripId);

        // 'Touch' the corresponding trip!
        if ($tripId) {
            $TripModel = new Trip;
            $TripModel->touchTrip($tripId);
        }

        /*// to sql datetime format.
        if ((isset($vars['sty']) && (int)$vars['sty'] != 0) || (isset($vars['stm']) && (int)$vars['stm'] != 0) || (isset($vars['std']) && (int)$vars['std'] != 0)) {
            $start = mktime(0, 0, 0, (int)$vars['stm'], (int)$vars['std'], (int)$vars['sty']);
            $start = date('YmdHis', $start);
        } else {
            $start = false;
        } */
        // to sql datetime format.
        if (isset($vars['date']) && (strlen($vars['date']) <= 10 && strlen($vars['date']) > 8)) {
            list($day, $month, $year) = preg_split('/[\/.-]/', $vars['date']);
            if (substr($month,0,1) == '0') $month = substr($month,1,2);
            if (substr($day,0,1) == '0') $day = substr($day,1,2);
            $start = mktime(0, 0, 0, (int)$month, (int)$day, (int)$year);
            $start = date('YmdHis', $start);
        } else {
            $start = false;
        }

        // Check if the location already exists in our DB and add it if necessary
        if ($vars['geonameid'] && $vars['latitude'] && $vars['longitude'] && $vars['geonamename'] && $vars['geonamecountrycode'] && $vars['admincode']) {
            $geoname_ok = $this->_model->checkGeonamesCache($vars['geonameid']);
        } else {
            $geoname_ok = false;
        }

        $geonameId = $geoname_ok ? $vars['geonameid'] : false;

        $this->_model->updatePostData($post->blog_id, $vars['t'], $vars['txt'], $start, $geonameId);

        if (!$this->_model->updateTags($post->blog_id, explode(',', $vars['tags']))) {
            $vars['errors'] = array('tagerror');
            return false;
        }
        $this->_model->updateBlogToCategory($post->blog_id, $vars['cat']);
        PPostHandler::clearVars();
        return 'blog/edit/'.$post->blog_id.'/finish';
    }

    public function singlePost($postId, $showComments = true)
    {
        $blog = $this->_model->getPost($postId);
        $this->_view->singlePost($blog, $showComments);
    }

    public function userPosts($userHandle) {
    	$this->_view->userPosts($userHandle);
    }

    /* removed - referencing app_user which is being deleted
    public function userSettingsForm()
    {
    	if (!$this->_model->getLoggedInMember())
            return false;
        $this->_view->userSettingsForm();
    }
    */

    public function stickyPosts() {
    	$this->_view->stickyPosts();
    }

    /* removed - referencing app_user which is being deleted
    public function settingsProcess()
    {
    	$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
        if (PPostHandler::isHandling())
        {
            if (!$this->_model->getLoggedInMember())
                return false;
            $vars =& PPostHandler::getVars();
            return $this->_model->settingsProcess($vars);
        }
        else
        {
        	PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
        }
    }
    */

    /**
     * handles comment submissions
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return false|string
     */
    public function commentProcess($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$this->_model->getLoggedInMember())
            return false;
        $vars = $args->post;
        $request = $args->request;
        if ($comment_id = $this->_model->commentProcess($vars, $request, $request[2]))
        {
            return implode('/', $request) . '#c' . $comment_id;
        }
        else
        {
            $redirected_mem->vars = $vars;
            return false;
        }
    }

    /**
     * handles forms from blog/cat/
     *
     * @param object $args
     * @param object $action
     * @param object $mem_redirect
     * @param object $mem_resend
     * @access public
     * @return false|string
     */
    public function categoryProcess($args, $action, $mem_redirect, $mem_resend)
    {
        if (!$this->_model->getLoggedInMember())
            return false;
        $vars = $args->post;
        if ($this->_model->categoryProcess($vars, $args->request))
        {
            return 'blog/cat';
        }
        else
        {
            $mem_redirect->vars = $vars;
            $mem_redirect->post = $vars;
            return false;
        }
    }
}
