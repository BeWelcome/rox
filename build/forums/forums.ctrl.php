<?php
/**
* forums controller
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id: forums.ctrl.php 32 2007-04-03 10:22:22Z marco_p $
*/

class ForumsController extends PAppController
{
    private $_model;
    private $_view;

    protected $BW_Right;
    protected $BW_Flag;
    protected $request;

    public function __construct() {
        parent::__construct();
        $this->_model = new Forums();
        $this->_view = new ForumsView($this->_model);
//                $this->_view->page=new RoxGenericPage();
        $this->BW_Right = MOD_right::get();
        $this->BW_Flag = MOD_flag::get();
        $this->request = PRequest::get()->request;
        $this->forums_uri = $this->get_forums_uri();
        $this->_model->forums_uri = $this->forums_uri;
    }

    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    public function topMenu($currentTab) {
        $this->_view->topMenu($currentTab);
    }

    public function get_forums_uri() {
        $request = PRequest::get()->request;
        $uri = array();
        foreach ($request as $r) {
            array_push($uri,$r);
            if ($r == 'forums' or $r == 'forum') break;
        }
        $uri = implode('/', $uri);
        $uri = rtrim($uri, '/').'/';
        return $uri;
    }

    /**
    * index is called when http request = ./forums
    * or during a new topic/edit of a group
    */
    public function index($subforum = false)     {
        if (PPostHandler::isHandling()) {
            return;
        }

        // Determine the search callback and tell the view about it
        $searchCallbackId = $this->searchProcess();

        $view = $this->_view;
        $view->searchCallbackId = $searchCallbackId;

        $page = $view->page = new RoxGenericPage();

        $request = $this->request;
        if (isset($request[0]) && $request[0] != 'forums') {
            // if this is a ./groups url get the group number if any
            if (($request[0] == "groups") && (isset($request[1]))) {
                $IdGroup = intval($request[1]);
            }
            $new_request = array();
            $push = false;
            foreach ($request as $r) {
                if ($r == 'forums' or $r == 'forum') $push = true;
                if ($push == true) array_push($new_request,$r);
            }
            $request = $new_request;
            $page = $view->page=new PageWithHTMLpart();
        }

        // First check if the feature is closed
        if (($_SESSION["Param"]->FeatureForumClosed!='No')and(!$this->BW_Right->HasRight("Admin"))) {
            $this->_view->showFeatureIsClosed();
            PPHP::PExit();
        } // end of test "if feature is closed"


        if ( APP_User::isBWLoggedIn()) {
            $User = APP_User::login();
        }
        else {
            $User = false;
        }

        $showSticky = true;

        $this->parseRequest();

        // set uri for correct links in group pages etc.
        $view->uri = $this->uri;
        $page->uri = $this->uri;

        $view->BW_Right = $this->BW_Right;
        $page->BW_Right = $this->BW_Right;

        $this->_model->prepareForum($showSticky);

        // first include the col2-stylesheet
        $page->addStyles .= $view->customStyles();
        $page->currentTab = 'forums';
        // then the userBar
        $page->newBar .= $view->getAsString('userBar');

        // we can't replace this ob_start()
        if ($this->action == self::ACTION_NOT_LOGGED_IN) {
            $this->_redirectNotLoggedIn();
        }

        ob_start();
        if ($this->action == self::ACTION_VOTE_POST) {
            if (!isset($request[2])) {
                die("Need to have a IdPost") ;
            }
            $IdPost=$request[2] ;
            if (!isset($request[3])) {
                die("Need to have a vote value") ;
            }
            $Value=$request[3] ;
            $this->_model->VoteForPost($IdPost,$Value);
            $this->_model->setThreadId($this->_model->GetIdThread($IdPost));
            $this->isTopLevel = false;
            $this->_model->prepareTopic(true);
            $this->_view->showTopic();
        }
        elseif ($this->action == self::ACTION_DELETEVOTE_POST) {
            if (!isset($request[2])) {
                 die("Need to have a IdPost") ;
            }
            $IdPost=$request[2] ;
			$this->_model->DeleteVoteForPost($IdPost);

            $this->_model->setThreadId($this->_model->GetIdThread($IdPost));
            $this->isTopLevel = false;
            $this->_model->prepareTopic(true);
            $this->_view->showTopic();
         }
        elseif ($this->action == self::ACTION_MODERATOR_FULLEDITPOST) {
            if (!isset($request[2])) {
                 die("Need to have a IdPost") ;
            }
            $IdPost=$request[2] ;
            if (!$this->BW_Right->HasRight("ForumModerator","Edit")) {
                 MOD_log::get()->write("Trying to edit post #".$IdPost." without proper right", "ForumModerator");
                 die("You miss right ForumModerator") ;
            }
            $callbackId = $this->ModeratorEditPostProcess();

            $DataPost=$this->_model->prepareModeratorEditPost($IdPost, $this->BW_Right->HasRight('ForumModerator'));
            $this->_view->showModeratorEditPost($callbackId,$DataPost);
            PPostHandler::clearVars($callbackId);
         }

        elseif ($this->action == self::ACTION_MODERATOR_EDITTAG) {
            if (!isset($request[2])) {
                 die("Need to have a IdTag") ;
             }
             $IdTag=$request[2] ;
             if (!$this->BW_Right->HasRight("ForumModerator","Edit")) {
                 MOD_log::get()->write("Trying to edit Tag #".$IdTag." without proper right", "ForumModerator");
                 die("You miss right ForumModerator") ;
             }
            $callbackId = $this->ModeratorEditTagProcess();

            $DataTag=$this->_model->prepareModeratorEditTag($IdTag);
            $this->_view->showModeratorEditTag($callbackId,$DataTag);
            PPostHandler::clearVars($callbackId);
         }
        else if ($this->action == self::ACTION_VIEW) {
            if ($this->_model->isTopic()) {
                $this->_model->prepareTopic(true);
                $this->_view->showTopic();
            }
            else {
                if ($this->isTopLevel) {
                    $this->_model->setTopMode(Forums::CV_TOPMODE_LANDING);
                    $this->_model->prepareForum();

                    $onlymygroupscallbackId = $this->mygroupsonlyProcess();
                    $morelessthreadscallbackid = $this->morelessthreadsProcess();
                    $this->_view->showTopLevelLandingPage($onlymygroupscallbackId, $morelessthreadscallbackid);
                    PPostHandler::clearVars($onlymygroupscallbackId);
                    PPostHandler::clearVars($morelessthreadscallbackid);
                } else {
                    $this->_model->prepareForum();
                    $this->_view->showForum();
                }
            }
        }
        else if ($this->action == self::ACTION_VIEW_CATEGORY) {
            $this->_view->showTopLevelCategories();
        }
        else if ($this->action == self::ACTION_VIEW_LASTPOSTS) {
            $callbackId = $this->mygroupsonlyProcess();
            $this->_view->showTopLevelRecentPosts($callbackId);
            PPostHandler::clearVars($callbackId);
        }
        else if ($this->action == self::ACTION_VIEW_LANDING) {
            $callbackId = $this->mygroupsonlyProcess();
            $this->_view->showTopLevelLandingPage($callbackId);
            PPostHandler::clearVars($callbackId);
        }
        else if ($this->action == self::ACTION_VIEW_FORUM) {
            $groupsCallback = false;
            $member = $this->_model->getLoggedInMember();
            if ($member && $member->Status != 'ChoiceInactive') {
                $noForumNewTopicButton = false;
            } else {
                // Don't offer the new topic button to 'silent' members
                $noForumNewTopicButton = true;
            }
            $this->_view->showTopLevelRecentPosts($groupsCallback, $noForumNewTopicButton);
        }
        else if ($this->action == self::ACTION_VIEW_GROUPS) {
            $callbackId = $this->mygroupsonlyProcess();
            $this->_view->showTopLevelRecentPosts($callbackId, true);
            PPostHandler::clearVars($callbackId);
        }
        else if ($this->action == self::ACTION_RULES) {
            $this->_view->rules();
        }
        else if ($this->action == self::ACTION_NEW) {
            if ($this->BW_Flag->hasFlag("NotAllowedToPostInForum")) { // Test if the user has right for this, if not rough exit
                MOD_log::get()->write("Forums.ctrl : Forbid to do action [".$this->action."] because of Flag "."NotAllowedToPostInForum","FlagEvent") ;
                $words = new MOD_Words();
                die($words->get('NotAllowedToPostInForum'));
            }
            if (!$User) {
                PRequest::home();
            }
            if ((isset($request[2])) and ($request[2]{0}=='u')) {
                 $IdGroup=substr($request[2],1) ;
            }
            else {
                if (!isset($IdGroup)) {
                 $IdGroup=0 ;
            }
            }
            $this->_model->prepareForum();
            $callbackId = $this->createProcess();
            $this->_view->createTopic($callbackId,$IdGroup);
            PPostHandler::clearVars($callbackId);
        }
        else if ($this->action == self::ACTION_REPORT_TO_MOD) {
            if ($this->BW_Flag->hasFlag("NotAllowedToPostInForum")) { // Test if the user has right for this, if not rough exit
                MOD_log::get()->write("Forums.ctrl : Forbid to do action [".$this->action."] because of Flag "."NotAllowedToPostInForum","FlagEvent") ;
                $words = new MOD_Words();
                die($words->get('NotAllowedToPostInForum'));
            }
            if (!$User) {
                PRequest::home();
            }
            $callbackId = $this->reportpostProcess();

			if (isset($request[2])) {
				if ($request[2]=='AllMyReport') {
					$DataPost=$this->_model->prepareReportList($_SESSION["IdMember"],""); // This retrieve all the reports for the current member
					$this->_view->showReportList($callbackId,$DataPost);
				}
				elseif ($request[2]=='MyReportActive') {
					$DataPost=$this->_model->prepareReportList($_SESSION["IdMember"],"('Open','OnDiscussion')"); // This retrieve the Active current pending report for the current member
					$this->_view->showReportList($callbackId,$DataPost);
				}
				elseif ($request[2]=='AllActiveReports') {
                    if (!$this->BW_Right->HasRight("ForumModerator")) {
                        // if a non forum moderator tries to access this just pull the brakes
                        PPHP::PExit();
                    }
					$DataPost=$this->_model->prepareReportList(0,"('Open','OnDiscussion')"); // This retrieve all the current Active pending report
					$this->_view->showReportList($callbackId,$DataPost);
				}
				else {
					$IdPost=$request[2] ;
					$IdWriter=$_SESSION["IdMember"] ;
					if ((!empty($request[3])) and ($this->BW_Right->HasRight("ForumModerator"))) {
						$IdWriter=$request[3] ;
					}

					$DataPost=$this->_model->prepareModeratorEditPost($IdPost, $this->BW_Right->HasRight('ForumModerator')); // We will use the same data as the one used for Moderator edit

                    if ($DataPost->Error == 'NoGroupMember') {
                        // if someone who isn't a member of the associated group
                        // tries to access this just pull the brakes
                        PPHP::PExit();
                    }
					$DataPost->Report=$this->_model->prepareReportPost($IdPost,$IdWriter) ;
					$this->_view->showReportPost($callbackId,$DataPost);
				}
				PPostHandler::clearVars($callbackId);
			}
		}
        else if ($this->action == self::ACTION_REPLY) {
            if ($this->BW_Flag->hasFlag("NotAllowedToPostInForum")) { // Test if teh user has right for this, if not rough exit
                MOD_log::get()->write("Forums.ctrl : Forbid to do action [".$this->action."] because of Flag "."NotAllowedToPostInForum","FlagEvent") ;
                $words = new MOD_Words();
                die($words->get('NotAllowedToPostInForum'));
            }
            if (!$User) {
                PRequest::home();
            }
            $this->_model->prepareForum();
            $this->_model->prepareTopic();
            $this->_model->initLastPosts();
            $callbackId = $this->replyProcess();
            $this->_view->replyTopic($callbackId);
            PPostHandler::clearVars($callbackId);
        }
        else if ($this->action == self::ACTION_SUGGEST) {
            // ignore current request, so we can use the last request
            PRequest::ignoreCurrentRequest();
            if (!isset($request[2])) {
                PPHP::PExit();
            }
            $new_tags = $this->_model->suggestTags($request[2]);
            echo $this->_view->generateClickableTagSuggestions($new_tags);
            PPHP::PExit();
        }
        else if ($this->action == self::ACTION_LOCATIONDROPDOWNS) {
            // ignore current request, so we can use the last request
            PRequest::ignoreCurrentRequest();
            if (!isset($request[2])) {
                PPHP::PExit();
            }
            echo $this->_view->getLocationDropdowns();
            PPHP::PExit();
        } else if ($this->action == self::ACTION_DELETE) {
            if ($this->BW_Flag->hasFlag("NotAllowedToPostInForum")) { // Test if the user has right for this, if not rough exit
                MOD_log::get()->write("Forums.ctrl : Forbid to do action [".$this->action."] because of Flag "."NotAllowedToPostInForum","FlagEvent") ;
                $words = new MOD_Words();
                die($words->get('NotAllowedToPostInForum'));
            }
            if (!$User || !$this->BW_Right->HasRight("ForumModerator","Delete")) {
                PRequest::home();
            }
            $this->delProcess();
        } else if ($this->action == self::ACTION_EDIT) {
            if ($this->BW_Flag->hasFlag("NotAllowedToPostInForum")) { // Test if the user has right for this, if not rough exit
                MOD_log::get()->write("Forums.ctrl : Forbid to do action [".$this->action."] because of Flag "."NotAllowedToPostInForum","FlagEvent") ;
                $words = new MOD_Words();
                die($words->get('NotAllowedToPostInForum'));
            }
            if (!$User) {
                PRequest::home();
            }
            $callbackId = $this->editProcess();
            $this->_model->prepareForum();
            $this->_model->getEditData($callbackId);
            $this->_view->editPost($callbackId,false);
            PPostHandler::clearVars($callbackId);
        } else if ($this->action == self::ACTION_TRANSLATE) {
            if ($this->BW_Flag->hasFlag("NotAllowedToPostInForum")) { // Test if the user has right for this, if not rough exit
                MOD_log::get()->write("Forums.ctrl : Forbid to do action [".$this->action."] because of Flag "."NotAllowedToPostInForum","FlagEvent") ;
                $words = new MOD_Words();
                die($words->get('NotAllowedToPostInForum'));
            }
            if (!$User) {
                PRequest::home();
            }
            $callbackId = $this->editProcess();
            $this->_model->prepareForum();
            $this->_model->getEditData($callbackId);
            $this->_view->editPost($callbackId,true);
            PPostHandler::clearVars($callbackId);
        } else if ($this->action == self::ACTION_MODEDIT) {
            if (!$User) {
                PRequest::home();
            }
            $callbackId = $this->editProcess();
            $this->_model->prepareForum();
            $this->_model->getEditData($callbackId);
            $this->_view->ModeditPost($callbackId);
            PPostHandler::clearVars($callbackId);
        } else if ($this->action == self::ACTION_SEARCH_FORUMS) {
            $this->_view->keyword = $request[2];
            $this->_view->showSearchResultPage($request[2]);
            PPostHandler::clearVars($searchCallbackId);
        } else if ($this->action == self::ACTION_SEARCH_USERPOSTS) {
            if (!isset($request[2])) {
                PPHP::PExit();
            }
            $this->searchUserposts($request[2]);
        } else if ($this->action == self::ACTION_SUBSCRIBE) {
            if (!isset($request[2])) {
                PPHP::PExit();
            }
            if ($request[2]=="thread") {
                $this->SubscribeThread($request[3]);
            }
            if ($request[2]=="tag") {
                $this->SubscribeTag($request[3]);
            }
        } else if ($this->action == self::ACTION_SEARCH_SUBSCRIPTION) {

            /*
             * Here the following syntax can be used :
             * forums/subscriptions : allow current user to see his subscribtions
             * forums/subscriptions/unsubscribe/thread/xxx/yyy : allow current user to unsubscribe from members_threads_subscribed.id xxx with key yyy
             * forums/subscriptions/member/xxx : allow a forum moderator to see all subscribtions of member xxx
             * forums/subscriptions/thread/xxx : allow a forum moderator to see all subscribers and subscribtions for thread xxx
             * forums/subscribe/thread/xxx : subscribe to thread xxx
             */

            $operation="" ;
            if (isset($request[2])) {
                $operation=$request[2] ;
            }
            switch($operation) {
                case "enable":
                    if (isset($request[3])) {
                        switch($request[3]) {
                            case 'thread':
                                $this->EnableThread($request[4]);
                                break;
                            case 'tag':
                                $this->EnableTag($request[4]);
                                break;
                            case 'group':
                                $this->EnableGroup($request[4]);
                                break;
                        }
                    } else {
                        $this->enableSubscriptions();
                    }
                    break;
                case "disable":
                    if (isset($request[3])) {
                        switch($request[3]) {
                            case 'tag':
                                $this->DisableTag($request[4]);
                                break;
                            case 'thread':
                                $this->DisableThread($request[4]);
                                break;
                            case 'group':
                                $this->DisableGroup($request[4]);
                                break;
                        }
                    } else {
                        $this->disableSubscriptions();
                    }
                    break;
                case "subscribe":
                    if (isset($request[3]) and ($request[3]=='group')) {
                        $this->SubscribeGroup($request[4]);
                    }
                    break;
                case "unsubscribe":
                    switch ($request[3]) {
                        case 'thread' :
                            $this->UnsubscribeThread($request[4],$request[5]);
                            break;
                        case 'tag':
                            $this->UnsubscribeTag($request[4],$request[5]);
                            break;
                        case 'group':
                            $this->UnsubscribeGroup($request[4]);
                            break;
                    }
                    break;
                default :
                    $this->searchSubscriptions();
            }
        } else {
            if (PVars::get()->debug) {
                throw new PException('unexpected forum action!');
            } else {
                PRequest::home();
            }
        }

        $page->content .= ob_get_contents();
         ob_end_clean();
        $page->newBar .= $view->getAsString('showCategoriesContinentsTagcloud');
        $page->teaserBar .= $view->getAsString('teaser');
        $page->render();
    } // end of index

    private function _redirectNotLoggedIn() {
        $request = PVars::getObj('env')->baseuri . 'login/' . implode('/', $this->request) . '#login-widget';
        header('Location: ' . $request);
        PPHP::PExit();
    }

    private function redirectSubscriptions() {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            $redirect = PVars::getObj('env')->baseuri . 'forums/subscriptions';
        } else {
            $referrer = $_SERVER['HTTP_REFERER'];
            $referrer = str_replace('/login/', '/', $referrer);
            $pos = strpos($referrer, 'forums/subscriptions/');
            if ($pos !== false) {
                // make sure no infinite redirect happens
                $redirect = substr($referrer, 0, $pos) . 'forums/subscriptions';
            } else {
                $redirect = $referrer;
            }
        }
        header('Location: ' . $redirect);
        exit;
    }

    private function EnableGroup($IdGroup) {
        $this->_model->enableGroup($IdGroup);
        $this->redirectSubscriptions();
    }
    private function DisableGroup($IdGroup) {
        $this->_model->disableGroup($IdGroup);
        $this->redirectSubscriptions();
    }
    private function SubscribeGroup($IdGroup) {
        $this->_model->subscribeGroup($IdGroup);
        $this->redirectSubscriptions();
    }
    private function UnsubscribeGroup($IdGroup) {
        $this->_model->unsubscribeGroup($IdGroup);
        $this->redirectSubscriptions();
    }
    private function enableSubscriptions() {
        $this->_model->enableSubscriptions();
        $this->redirectSubscriptions();
    }
    private function disableSubscriptions() {
        $this->_model->disableSubscriptions();
        $this->redirectSubscriptions();
    }
    private function searchSubscriptions() {
        $TResults = $this->_model->searchSubscriptions();
        $this->_view->displaySearchResultSubscriptions($TResults);
    }
    private function SubscribeThread($IdThread) {
        $res = $this->_model->SubscribeThread($IdThread);
        $this->redirectSubscriptions();
    }
    private function UnsubscribeThread($IdSubscribe=0,$Key="") {
        $this->_model->UnsubscribeThread($IdSubscribe,$Key);
        $this->redirectSubscriptions();
    }

    private function SubscribeTag($IdTag) {
        $res = $this->_model->SubscribeTag($IdTag);
        $this->redirectSubscriptions();
    }
    private function UnsubscribeTag($IdSubscribe=0,$Key="") {
        $res = $this->_model->UnsubscribeTag($IdSubscribe,$Key);
        $this->redirectSubscriptions();
    }
    private function EnableThread($IdThread) {
        $this->_model->EnableThread($IdThread);
        $this->redirectSubscriptions();
    }
    private function EnableTag($IdTag) {
        $this->_model->EnableTag($IdTag);
        $this->redirectSubscriptions();
    }
    private function DisableThread($IdThread) {
        $this->_model->DisableThread($IdThread);
        $this->redirectSubscriptions();
    }
    private function DisableTag($IdThread) {
        $this->_model->DisableTag($IdThread);
        $this->redirectSubscriptions();
    }

    private function searchUserposts($user) {
        // Data will be displayed only if the current user is Logged
        $profileVisitor = $this->_model->getLoggedInMember();
        if ($profileVisitor) {
            $userId = APP_User::memberId($user);
            $membersForumPostsPagePublic = $this->_model->isMembersForumPostsPagePublic($userId);
            if ($membersForumPostsPagePublic || ($profileVisitor->getPKValue() == $userId) || $this->BW_Right->HasRight("Admin") || $this->BW_Right->HasRight("ForumModerator") || $this->BW_Right->HasRight("SafetyTeam") ) {
                $posts = $this->_model->searchUserposts($user);
            } else {
                $posts = array(); //TODO: post something that says that the user has not enabled that page
            }
        }
        else {
            $posts = array() ;
        }
        $this->_view->displaySearchResultPosts($posts); // TODO: post something suggesting to LogIn or to register to maybe see posts by this user
    }

    /**
    * show latest threads belonging to a group
    *
    **/
    public function showExternalGroupThreads($groupId, $isGroupMember = true, $showsticky = true, $showNewTopicButton = true) {

        $request = $this->request;
        $this->parseRequest();
        $this->_model->setGroupId($groupId);
        $this->isTopLevel = false;
        $this->_model->prepareForum($showsticky);
        $this->_view->uri = 'groups/'.$request[1].'/forum/';
        $this->_view->showExternal(true, $showsticky, $showNewTopicButton, $isGroupMember);
    }

    /**
    * shows one thread of the suggestions group
    *
    **/
    public function showExternalSuggestionsThread($suggestionId, $groupId, $threadId) {
        $request = $this->request;
        $this->parseRequest();
        $this->_model->setGroupId($groupId);
        $this->_model->setThreadId($threadId);
        $this->isTopLevel = false;
        $this->_model->prepareForum();
        $this->_model->prepareTopic();
        $this->_view->uri = 'suggestions/' . $suggestionId . '/discuss/forum/';
        $this->_view->showTopic();
    }

    /**
    * allows to reply to a thread in the suggestions group
    *
    **/
    public function showExternalSuggestionsThreadReply($suggestionId, $groupId, $threadId, $urlpart) {
        $request = $this->request;
        $this->parseRequest();
        $this->_model->setGroupId($groupId);
        $this->_model->setThreadId($threadId);
        $this->isTopLevel = false;
        $this->_model->prepareForum();
        $this->_model->prepareTopic();
        $this->_model->initLastPosts();
        $this->_view->suggestionId = $suggestionId;
        $this->_view->suggestionsGroupId = $groupId;
        $this->_view->suggestionsThreadId = $threadId;
        $this->_view->suggestionsUri = 'suggestions/' . $suggestionId . '/' . $urlpart . '/';
        $callbackId = $this->replySuggestionsProcess();
        $this->_view->replyTopic($callbackId);
        PPostHandler::clearVars($callbackId);
    }

    /**
     * Displays a teaser list with latest threads
     *
     * @param bool $showGroups Set true if group name and link should be shown
     *                         in teasers
     */
    public function showExternalLatest($showGroups = false) {
        $request = $this->request;
        $member = $this->_model->getLoggedInMember();
        $showForumNewTopicButton = true;
        if ($member->Status == 'ChoiceInactive') {
            $showForumNewTopicButton = false;
        }
        $this->parseRequest();
        $this->_model->setTopMode(Forums::CV_TOPMODE_FORUM);
        $this->_model->prepareForum(false);
        $this->_view->uri = 'forums/';
        $this->_view->showExternal($showGroups, false, $showForumNewTopicButton);
    }

    public function editProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
            return $this->_model->editProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function createProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
            return $this->_model->createProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function replyProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
            return $this->_model->replyProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function replySuggestionsProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $groupId = SuggestionsModel::getGroupId();
            $this->_model->setGroupId($groupId);
            return $this->_model->replyProcess(true);
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function reportpostProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
            return $this->_model->reportpostProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function ModeratorEditPostProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
//             echo ("here") ;
            return $this->_model->ModeratorEditPostProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function ModeratorEditTagProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
//             echo ("here") ;
            return $this->_model->ModeratorEditTagProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function delProcess() {
        $this->parseRequest();
        $this->_model->delProcess();
    }

    public function mygroupsonlyProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            return $this->_model->switchShowMyGroupsTopicsOnly();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function morelessthreadsProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            return $this->_model->adjustThreadsCountToShow($step = 3);
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    private $action = 0;
    private $isTopLevel = true;
    private $uri = 'forums/';
    const ACTION_VIEW = 0;
    const ACTION_NEW = 1;
    const ACTION_EDIT = 2;
    const ACTION_REPLY = 3;
    const ACTION_SUGGEST = 4;
    const ACTION_DELETE = 5;
    const ACTION_LOCATIONDROPDOWNS = 6;
    const ACTION_SEARCH_FORUMS = 23;
    const ACTION_SEARCH_USERPOSTS = 7;
    const ACTION_RULES = 8;
    const ACTION_SEARCH_SUBSCRIPTION=9 ;
    const ACTION_SUBSCRIBE=10 ;
    const ACTION_MODERATOR_FULLEDITPOST=11 ;
    const ACTION_MODERATOR_EDITTAG=12 ;
    const ACTION_MODEDIT = 13;
    const ACTION_TRANSLATE = 14;
    const ACTION_VIEW_CATEGORY = 15;
    const ACTION_VIEW_LASTPOSTS = 16;
    const ACTION_VOTE_POST = 17;
	const ACTION_DELETEVOTE_POST = 18 ;
	const ACTION_REPORT_TO_MOD = 19 ;
    const ACTION_VIEW_LANDING = 20;
    const ACTION_VIEW_FORUM = 21;
    const ACTION_VIEW_GROUPS = 22;
    const ACTION_NOT_LOGGED_IN = 24;


    /**
    * Parses a request
    * Extracts the current action, geoname-id, country-code, admin-code, all tags and the threadid from the request uri
    */
    private function parseRequest() {
        $request = $this->request;

    //    die ("\$request[1]=".$request[1]) ;
        // If this is a subforum within a group
      if (isset($request[0]) && !isset($request[1]) && $request[0] == 'forums') {
          $this->_model->setTopMode(Forums::CV_TOPMODE_LANDING);
          $this->action = self::ACTION_VIEW;
      }

      if (isset($request[0]) && $request[0] == 'groups') {
            if (isset($request[1])) {
                if ($request[1] == 'forums') {
                    $this->_model->setTopMode(Forums::CV_TOPMODE_GROUPS);
                    $this->action = self::ACTION_VIEW_GROUPS;
                    $this->uri = 'forums/';
                }
                else if (isset($request[2]) && $request[2]=='forum') {
                    $this->_model->setGroupId((int) $request[1]);
                    $this->isTopLevel = false;
                    $this->isTopCategories = false;
                    $this->uri = 'groups/'.$request[1].'/forum/';
                }
            }
        }
        if (!APP_User::isBWLoggedIn()) {
            $this->action = self::ACTION_NOT_LOGGED_IN;
        } else if (!isset($request[1])) {
            $this->_model->setTopMode(Forums::CV_TOPMODE_LANDING);
            $this->action = self::ACTION_VIEW;
        } else if (isset($request[1]) && $request[1] == 'suggestTags') {
            $this->action = self::ACTION_SUGGEST;
        } else if (isset($request[1]) && $request[1] == 'search') {
            $this->action = self::ACTION_SEARCH_FORUMS;
            if (isset($request[3]) && preg_match_all('/page([0-9]+)/i', $request[3], $regs)) {
                $this->_model->setPage($regs[1][0]);
                $this->_model->pushToPageArray($regs[1][0]);
            }
        } else if (isset($request[1]) && $request[1] == 'member') {
            $this->action = self::ACTION_SEARCH_USERPOSTS;
        } else if (isset($request[1]) && $request[1] == 'modfulleditpost') {
            $this->action = self::ACTION_MODERATOR_FULLEDITPOST;
        } else if (isset($request[1]) && $request[1] == 'votepost') {
            $this->action = self::ACTION_VOTE_POST;
        } else if (isset($request[1]) && $request[1] == 'deltevotepost') {
            $this->action = self::ACTION_DELETEVOTE_POST;
        } else if (isset($request[1]) && $request[1] == 'modedittag') {
            $this->action = self::ACTION_MODERATOR_EDITTAG;
        } else if (isset($request[1]) && $request[1] == 'subscriptions') {
            $this->action = self::ACTION_SEARCH_SUBSCRIPTION;
        } else if (isset($request[1]) && $request[1] == 'subscribe') {
            $this->action = self::ACTION_SUBSCRIBE;
        } else if (isset($request[1]) && $request[1] == 'rules') {
            $this->action = self::ACTION_RULES;
        } else {
            foreach ($request as $r) {
                if ($r == 'new') {
                    $this->action = self::ACTION_NEW;
                } else if ($r == 'edit') {
                    $this->action = self::ACTION_EDIT;
                } else if ($r == 'landing') {
                    $this->_model->setTopMode(Forums::CV_TOPMODE_LANDING);
                    $this->action = self::ACTION_VIEW_LANDING;
                    $showSticky = false;
                } else if ($r == 'bwforum') {
                    $this->_model->setTopMode(Forums::CV_TOPMODE_FORUM);
                    $this->action = self::ACTION_VIEW_FORUM;
                } else if ($r == 'lastposts') {
                    $this->_model->setTopMode(Forums::CV_TOPMODE_LASTPOSTS);
                    $this->action = self::ACTION_VIEW_LASTPOSTS;
                } else if ($r == 'category') {
                    $this->_model->setTopMode(Forums::CV_TOPMODE_CATEGORY);
                    $this->action = self::ACTION_VIEW_CATEGORY;
                } else if ($r == 'translate') {
                    $this->action = self::ACTION_TRANSLATE;
                } else if ($r == 'modedit') {
                    $this->action = self::ACTION_MODEDIT;
                } else if ($r == 'reply') {
                    $this->action = self::ACTION_REPLY;
                } else if ($r == 'reporttomod') {
                    $this->action = self::ACTION_REPORT_TO_MOD;
                } else if ($r == 'modefullditpost') {
                    $this->action = self::ACTION_MODERATOR_FULLEDITPOST;
                } else if ($r == 'votepost') {
                    $this->action = self::ACTION_VOTE_POST;
                } else if ($r == 'deletevotepost') {
                    $this->action = self::ACTION_DELETEVOTE_POST;
                } else if ($r == 'modedittag') {
                    $this->action = self::ACTION_MODERATOR_EDITTAG;
                } else if ($r == 'reverse') {  // This mean user has click on the reverse order box
                    $this->_model->switchForumOrderList() ;
                } else if ($r == 'delete') {
                    $this->action = self::ACTION_DELETE;
                } else if (preg_match_all('/page([0-9]+)/i', $r, $regs)) {
                    $this->_model->setPage($regs[1][0]);
                    $this->_model->pushToPageArray($regs[1][0]);
                } else if ($r ==  'locationDropdowns') {
                    $this->action = self::ACTION_LOCATIONDROPDOWNS;
                } else {
                    $char = $r{0};
                    $dashpos = strpos($r, '-');
                    if ($dashpos === false) {
                        $dashpos = strlen($r) - 1;
                    } else {
                        $dashpos--;
                    }
                    if ($char == 'g') { // Geoname-ID
                        $this->_model->setGeonameid((int) substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 'c') { // Countrycode
                        $this->_model->setCountryCode(substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 'a') { // Admincode
                        $this->_model->setAdminCode(substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 't') { // Tagid
                        $this->_model->addTag((int) substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 's') { // Subject-ID (Thread-ID)
                        $this->_model->setThreadId((int) substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 'u') { // Group ID (This is a dedicated group)
                        $this->_model->setGroupId((int) substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 'k' && $r != "kickmember") { // Continent-ID
                        $this->_model->setContinent(substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    } else if ($char == 'm' && $r != "mygroupsonly") { // Message-ID (Single Post)
                        $this->_model->setMessageId(substr($r, 1, $dashpos));
                        $this->isTopLevel = false;
                    }
                }
            }
        }
    } // end of parserequest

    /**
     * Handles the post request of the forums search box
     */
    public function searchProcess() {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));

        if (PPostHandler::isHandling()) {
            $this->parseRequest();
            return $this->_model->searchProcess();
        } else {
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }
}
?>
