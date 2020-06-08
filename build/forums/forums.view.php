<?php

use App\Utilities\SessionTrait;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookup;

/**
* Forums view
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id:forums.view.php 32 2007-04-03 10:22:22Z marco_p $
*/

class ForumsView extends RoxAppView {
    use SessionTrait;

    private $_model;
    /** @var PageWithHTML */
    public $page;
    private $words ;
    public $uri;
    public $forum_uri;
    public $BW_Right;
    private $entryPointLookup;

    public function __construct(Forums &$model) {
        parent::__construct();
        $this->setSession();
        $this->_model =& $model;
        $this->words=$this->_model->words ;
        $this->BW_Right=$this->_model->BW_Right ;
        $this->uri=$this->getURI() ;
        $this->forum_uri='forums' ;
        $this->page = new PageWithHTML();
        $this->entryPointLookup = new EntrypointLookup('build/entrypoints.json');
    }


        public function SetPageTitle($Title) {
            $this->page->title=$Title ;
        }


    /**
    * Create a new topic in the current forum
    */
    public function createTopic(&$callbackId,$IdGroup=0) {
        $this->page->addStyleSheet('build/roxeditor.css');
        $this->page->addLateLoadScriptFile('build/cktranslations/'.$this->session->get('lang', 'en').'.js');
        $this->page->addLateLoadScriptFile('build/roxeditor.js');

        $boards = $this->_model->getBoard();
        $allow_title = true;
        $groupsDropdowns = $this->getGroupsDropdowns($IdGroup);
        $edit = false;

        $notifymecheck = "";
        if ($boards->IdGroup == 0) {
            $notifymecheck = 'checked="checked"' ; // This is to tell that the notifyme cell is preticked
        }
        $AppropriatedLanguage = 0 ; // By default english will be proposed as défault language
        $LanguageChoices = $this->_model->LanguageChoices() ;
        if ($IdGroup == 0) {
            $visibilityCheckbox = ''; // MembersOnly
        } else {
            $group = $this->_model->getGroupEntity($IdGroup);
            $groupOnly = ($group->VisiblePosts == 'no');
            if ($groupOnly) {
                $threadVisibility = 'GroupOnly';
            } else {
                $threadVisibility = 'MembersOnly';
            }
            $visibilityCheckbox = $this->getVisibilityCheckbox('GroupOnly', $threadVisibility, $IdGroup, true);
        }
        $disableTinyMCE = $this->_model->getTinyMCEPreference();
        require 'templates/editcreateform.php';
    }

    public function getURI()
    {
        return $this->forum_uri;
    }

    /**
     * returns a good-looking url for a forum thread
     * // TODO: maybe there's a better place for this.
     *
     * @param $thread as read from the threads database with mysql_fetch_object
     * @return string to be used as url
     */
    public function threadURL($thread, $baseurl = false)
    {
        if ($baseurl === false) {
            $baseurl = $this->uri;
        }
        return $baseurl.'s'.$thread->id.'-'.preg_replace('/[^A-Za-z0-9]/', '_',$this->words->fTrad($thread->IdTitle) ) ;
    }

    public  function postURL($post, $baseurl = false)
    {
        if ($baseurl === false) {
            $baseurl = $this->uri;
        }
        return $baseurl.'s'.$post->threadid.'-'.preg_replace('/[^A-Za-z0-9]/', '_',$this->words->fTrad($post->IdTitle) ) ;
    }

    public  function groupURL($post, $baseurl = false)
    {
        if ($baseurl === false) {
            $baseurl = $this->uri;
        }
        return '/group/'.$post->IdGroup;
    }


    public function replyTopic(&$callbackId) {
        $this->page->addStyleSheet('build/roxeditor.css');
        $this->page->addLateLoadScriptFile('build/cktranslations/'.$this->session->get('lang', 'en').'.js');
        $this->page->addLateLoadScriptFile('build/roxeditor.js');

        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $allow_title = false;
        $edit = false;
        $notifymecheck = "";
        if ($this->_model->IsThreadSubscribed($topic->IdThread,$this->session->get("IdMember"))) {
            $notifymecheck = 'checked="checked"' ; // This is to tell that the notifyme cell is preticked
        }

         // We are trying to find the more appropriated language according to the current one available for
         // this post + according to the languages of the current user
/*
         print_r($topic->topicinfo) ;
         echo "<br />\$topic->title=",$topic->topicinfo->title,"<br />" ;
         echo "<br />\$topic->first_postid=",$topic->first_postid,"<br />" ;

         echo "<br />\$topic->topicinfo=" ; print_r($topic->topicinfo); echo"<br />" ;
         echo "<br />\$topic->topicinfo->IdContent=",$topic->topicinfo->IdContent,"<br />" ;
*/
         $AppropriatedLanguage=$this->_model->FindAppropriatedLanguage($topic->topicinfo->first_postid) ;
         $LanguageChoices=$this->_model->LanguageChoices($AppropriatedLanguage) ;

        // Get current visibility of thread and set $visibilitiesDropdown
        // for editcreateform
        $IdGroup = 0;
        if (isset($topic->topicinfo->IdGroup)) {
            $IdGroup = $topic->topicinfo->IdGroup;
        }

        $visibility = $this->_model->getThreadVisibility($topic->IdThread);
        $visibilityCheckbox = $this->getVisibilityCheckbox($visibility, $visibility, $IdGroup, false);
        $disableTinyMCE = $this->_model->getTinyMCEPreference();

        require 'templates/editcreateform.php';

        require 'templates/replyLastPosts.php';
    }

    // This is the normal edit/translate post by a member
    public function editPost(&$callbackId,$translate=false) {
        $this->page->addStyleSheet('build/roxeditor.css');
        $this->page->addLateLoadScriptFile('build/cktranslations/'.$this->session->get('lang', 'en').'.js');
        $this->page->addLateLoadScriptFile('build/roxeditor.js');

        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $vars =& PPostHandler::getVars($callbackId);
        $groupsDropdowns = $this->getGroupsDropdowns($this->_model->IdGroup);
        $allow_title = $vars['first_postid'] == $vars['postid'];
        $edit = true;
        $messageid = $this->_model->getMessageId();
        $notifymecheck = "" ;
        if ($this->_model->IsThreadSubscribed($this->_model->getThreadId(),$this->session->get("IdMember"))) {
            $notifymecheck = 'checked="checked"' ; // This is to tell that the notifyme cell is preticked
        }
        $IdGroup = $this->_model->IdGroup;
        $visibilityThread = $this->_model->GetThreadVisibility($vars['threadid']);
        $visibilityPost = $this->_model->GetPostVisibility($vars['postid']);
        $visibilityCheckbox = $this->getVisibilityCheckbox($visibilityPost, $visibilityThread, $IdGroup, $allow_title);

        // By default no appropriated language is propose, the member can choose to translate
        $LanguageChoices=$this->_model->LanguageChoices() ;
        if (!$translate) { // In case this is a edit, by default force the original post language
            global $fTradIdLastUsedLanguage ; $fTradIdLastUsedLanguage=1 ; // will be change by ftrad
            $word = new MOD_words();
            // This function is just called for finding the language in which one the post will be displayed
            $AppropriatedLanguage=$fTradIdLastUsedLanguage ;
        }
        $disableTinyMCE = $this->_model->getTinyMCEPreference();
        if ($IdGroup == 0) {
            $this->renderScriptAndStyleTags = true;
        }
        require 'templates/editcreateform.php';
    } // end of editPost



// This si the Moderator edit/translate
    public function ModeditPost(&$callbackId) {
        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $vars =& PPostHandler::getVars($callbackId);
        $groupsDropdowns = $this->getModeratorGroupsDropdowns($this->_model->IdGroup);
        $allow_title = $vars['first_postid'] == $vars['postid'];
        $edit = true;
        $messageid = $this->_model->getMessageId();
        $notifymecheck="";
        if ($this->_model->IsThreadSubscribed($this->_model->getThreadId(),$this->session->get("IdMember"))) {
            $notifymecheck = 'checked="checked"'; // This is to tell that the notifyme cell is preticked
        }
        $AppropriatedLanguage=$this->_model->FindAppropriatedLanguage($vars['first_postid']) ;
        $LanguageChoices=$this->_model->LanguageChoices() ;
        $disableTinyMCE = $this->_model->getTinyMCEPreference();
        require 'templates/editcreateform.php';
    } // end of editPost

    /**
    * Display a topic
    */
    public function showTopic()  {
        $topic = $this->_model->getTopic();
        $request = PRequest::get()->request;

        if (isset($topic->topicinfo->IdGroup) && ($topic->topicinfo->IdGroup > 0) && $this->session->has( "IdMember" )) {
             $group_id = $topic->topicinfo->IdGroup;
             $memberIsGroupMember = $this->_model->checkGroupMembership($group_id);
        }
        // maybe in a later commit..
        if (isset($topic->topicinfo->IdTitle)) {
            $this->SetPageTitle($this->words->fTrad($topic->topicinfo->IdTitle));
        }
        else {
            $this->SetPageTitle('Forums - BeWelcome '.$this->words->getBuffered('Forum'));
        }
        if (empty($this->session->get('IdMember')))  {
            if (isset($topic->posts[0])) {
                $this->page->SetMetaDescription(strip_tags($this->_model->words->fTrad(($topic->posts[0]->IdContent)))) ; ;
            }
        }

        $uri = implode('/', $request);
        $uri = rtrim($uri, '/').'/';

        require 'templates/topic.php';
        $currentPage = $this->_model->getPage();
        $itemsPerPage = $this->_model->POSTS_PER_PAGE;
        $max = $topic->topicinfo->replies + 1;
        $maxPage = ceil($max / $this->_model->POSTS_PER_PAGE);
        $pages = $this->getPageLinks($currentPage, $itemsPerPage, $max);


        require 'templates/pages.php';
//              die( "<br />after page template".PVars::getObj('page')->title) ;
    } // end of ShowTopic



    /**
    * Display the form for a Moderator edit
     * This is the form with the list of all available translations for a given post
    */
    public function showModeratorEditPost(&$callbackId,$DataPost)     {
        $this->SetPageTitle("Moderator Edit Post") ;
        $vars =& PPostHandler::getVars($callbackId);
        $groupsDropdowns = $this->getModeratorGroupsDropdowns($this->_model->IdGroup);
        require 'templates/modpostform.php';
    } // end of showModeratorEditPost

// This is the normal view to display moderator report
    public function showReportPost(&$callbackId,$DataPost) {
        $this->SetPageTitle("Report To Moderator") ;
        $vars =& PPostHandler::getVars($callbackId);
        require 'templates/moderatorreport.php';
    } // end of showReportPost

// This is the normal view to display list to accessto reports
    public function showReportList(&$callbackId,$DataPost) {
        $this->SetPageTitle("List of reports") ;
        $vars =& PPostHandler::getVars($callbackId);
        require 'templates/reportslist.php';
    } // end of showReportList


    /**
    * Display the form for a Moderator edit
    */
    public function showModeratorEditTag(&$callbackId,$DataTag)     {
//        PVars::getObj('page')->title = "Moderator Edit Tag";
                $this->SetPageTitle("Moderator Edit Page") ;
        $vars =& PPostHandler::getVars($callbackId);
        require 'templates/modtagform.php';
    } // end of showModeratorEditTag

    /**
     * Display thread teasers externally
     *
     * @param bool $showGroups Set true if group name and link should be shown
     *                         in teasers
     */
    public function showExternal($showGroups = false, $showsticky = true, $showNewTopicButton = true, $isGroupMember = false) {
        $boards = $this->_model->getBoard($showsticky);
        $request = PRequest::get()->request;
        require 'templates/external.php';
    }

    /**
    * Display a forum
    */

    /* This displays the custom teaser */
    public function teaser() {
        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;
        $User = $this->_model->getLoggedInMember();

        require 'templates/teaser.php';
    }
    public function leftSidebar() {
        if ($this->session->has( "IdMember" )) {
            require 'templates/userbar.php';
        }
    }
  /*  public function showCategoriesContinentsTagcloud() {
        $top_tags = $this->_model->getTopCategoryLevelTags();
        $all_tags_maximum = $this->_model->getTagsMaximum();
        $all_tags = $this->_model->getAllTags();
        require 'templates/categories_continents_tagcloud.php';
    } */

    /* This displays the forum rules and charter */
    public function rules() {
        require 'templates/rules.php';
    }
    /* This adds custom styles to the page*/
    public function customStyles() {
        $out = '';
        $out .= '<link rel="stylesheet" href="styles/css/minimal/screen/custom/forums.css?10" type="text/css"/>';
        return $out;
    }

    public function topMenu($currentTab) {
        require TEMPLATE_DIR . 'shared/roxpage/topmenu.php';
    }


    // This will display the content of one board
    public function showForum() {
        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;
        $uri = implode('/', $request);
        $uri = rtrim($uri, '/').'/';
        $this->SetPageTitle($boards->getBoardName().' - BeWelcome '.$this->words->getBuffered('Forum'));

        if ($boards->IdGroup != 0) {
            $memberIsGroupMember = $this->_model->checkGroupMembership($boards->IdGroup);
            if (!$memberIsGroupMember) {
                $noForumNewTopicButton = true;
            }
        }
        $pages = $this->getBoardPageLinks();
        $currentPage = $this->_model->getPage();
        $max = $this->_model->getBoard()->getNumberOfThreads();
        $maxPage = ceil($max / $this->_model->THREADS_PER_PAGE);

        require 'templates/board.php';
    }

/*
* showTopLevelLandingPage produce the view with recent forum posts (without any group posts)
* on top and groups on the bottom
*/
    public function showTopLevelLandingPage($ownGroupsButtonCallbackId = false, $moreLessThreadsCallbackId = false) {
        $this->SetPageTitle($this->words->getBuffered('Forum').' - BeWelcome') ;

        $boards = $this->_model->getBoard();
        $boards->rewind();
        $forum = $boards->current();
        $groups = $boards->next();

        $request = PRequest::get()->request;

        $page_array = $this->_model->getPageArray();

        if (isset($page_array[0]) && isset($page_array[1])) {
            $currentForumPage = (int) $page_array[0];
            $currentGroupsPage = (int) $page_array[1];
        } else {
            $currentForumPage = 1;
            $currentGroupsPage = 1;
        }

        $pages = null;
        $forumpages = $this->getPageLinks($currentForumPage, $forum->THREADS_PER_PAGE, $forum->getNumberOfThreads());
        $groupspages = $this->getPageLinks($currentGroupsPage, $groups->THREADS_PER_PAGE, $groups->getNumberOfThreads());
        $forumMaxPage = ceil($forum->getNumberOfThreads() / $forum->THREADS_PER_PAGE);
        $groupsMaxPage = ceil($groups->getNumberOfThreads() / $groups->THREADS_PER_PAGE);

        $User = $this->_model->getLoggedInMember();
        require 'templates/landing.php';
    } // end of ShowTopLevelLandingPage

/*
* showTopLevelRecentPosts produce the view with the TagCloud and categories list
* last posts are not grouped in categories
*/
    public function showTopLevelRecentPosts($ownGroupsButtonCallbackId = false, $noForumNewTopicButton = false) {
//        PVars::getObj('page')->title = $this->words->getBuffered('Forum').' - BeWelcome';
        $this->SetPageTitle($this->words->getBuffered('Forum').' - BeWelcome') ;

        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;

        $pages = $this->getBoardPageLinks();
        $currentPage = $this->_model->getPage();
        $max = $this->_model->getBoard()->getNumberOfThreads();
        $maxPage = ceil($max / $this->_model->THREADS_PER_PAGE);

        require 'templates/toplevel.php';
    } // end of ShowTopLevel

/*
* showTopLevelcategories produce the view without the TagCloud and categories list
* last posts are grouped in categories
*/
    public function showTopLevelCategories() {
//        PVars::getObj('page')->title = $this->words->getBuffered('Forum').' - BeWelcome - Last Post in Catgegories';
        $this->SetPageTitle($this->words->getBuffered('Forum').' - BeWelcome - Last Post in Categories') ;

        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;

        $pages = $this->getBoardPageLinks();
        $currentPage = $this->_model->getPage();
        $max = $this->_model->getBoard()->getNumberOfThreads();
        $maxPage = ceil($max / $this->_model->THREADS_PER_PAGE);

        require 'templates/topcategories.php';
    } // end of showTopLevelCategories

    /**
     * @param string $keyword The term to be searched for
     */
    public function showSearchResultPage($keyword) {
        $result = $this->_model->searchForums($keyword);
        if (isset($result['errors'])) {
            require 'templates/searcherror.php';
        } else {
            $boards = $this->_model->getBoard();
            $request = PRequest::get()->request;
            $uri = implode('/', $request);
            $uri = rtrim($uri, '/') . '/';
            $this->SetPageTitle($boards->getBoardName() . ' - BeWelcome ' . $this->words->getBuffered('Forum'));

            $noForumNewTopicButton = true;

            $pages = $this->getBoardPageLinks();
            $currentPage = $this->_model->getPage();
            $max = $this->_model->getBoard()->getNumberOfThreads();
            $maxPage = ceil($max / $this->_model->THREADS_PER_PAGE);

            require 'templates/board.php';
        }
    }

    public function displaySearchResultSubscriptions($TResults) {
        $member = $this->_model->getLoggedInMember();
        require 'templates/searchresultsubscriptions.php';
    }
    public function displaySearchResultPosts($posts) {
        $topic = new StdClass();
        $topic->WithDetail=true ; // to avoid a warning
        require 'templates/searchresultposts.php';
    }

    public function SubscribeTag($res) {
        require 'templates/subscribetag.php';
    }

    public function SubscribeThread($res) {
        require 'templates/subscribethread.php';
    }

    public function Unsubscribe($res) {
        require 'templates/unsubscriberesult.php';
    }

    private function getBoardPageLinks() {
        $currentPage = $this->_model->getPage();
        $itemsPerPage = $this->_model->THREADS_PER_PAGE;
        $max = $this->_model->getBoard()->getNumberOfThreads();
        return $this->getPageLinks($currentPage, $itemsPerPage, $max);
    }

    private function getPageLinks($currentPage, $itemsPerPage, $max) {
        $maxPage = ceil($max / $itemsPerPage);
        if ($currentPage > $maxPage) {
            $currentPage = $maxPage;
        }
        $offs = ($currentPage - 1) * $itemsPerPage;

        $pages = array();
        $j = 0;
        for ($i = 1; $i <= $maxPage; $i++) {
            if ($i <= ($currentPage - 3) && $i != 1 && $i != 2) {
                continue;
            }
            if ($i >= ($currentPage + 3) && $i != ($maxPage) && $i != ($maxPage - 1)) {
                continue;
            }
            if ($i - $j != 1) {
                $pages[] = 'separator';
            }
            $j = $i;
            $p = array('pageno' => $i);
            if ($i == $currentPage) {
                $p['current'] = true;
            }
            $pages[] = $p;
        }

        return $pages;
    }

    public function getVisibilityCheckbox($visibility, $highestVisibility, $IdGroup, $newTopic) {
        if ($IdGroup == 0) {
            // Indicate to the form that only MembersOnly is allowed; this is a hack to avoid too much code changes
            return '';
        }

        if ($highestVisibility == 'GroupOnly') {
            // This will tell the form that the post is GroupOnly (IdGroup set and no visibilityCheckbox content
            return '';
        }

        if (!$newTopic) {
            $name = "PostVisibility";
            $word = 'ForumVisibilityGroupOnlyPost';
        } else {
            $name = "ThreadVisibility";
            $word = 'ForumVisibilityGroupOnlyThread';
        }

        $words = new MOD_words();

        $out = '<div class="form-check"><input type="checkbox" class="form-check-input" name="' . $name . '" id="' . $name . '" value="GroupOnly"';
        if ($visibility == 'GroupOnly') {
            $out .= ' checked="checked" ';
        }
        $out .= '/> <label for="' . $name . '" class="form-check-label">' . $words->get($word) . '</label></div>';
        return $out;
    }

    private function getGroupsDropdowns($IdGroup=0) {
        $tt = $this->_model->GroupChoice();
        $out = '<select name="IdGroup" id="IdGroup"><option value="0">'. $this->words->getFormatted("SelectNone").'</option>';
//              die ("2 IdGroup=".$IdGroup) ;
        foreach ($tt as $row => $tt) {
            $out .= '<option value="'.$tt->IdGroup.'"'.($IdGroup == $tt->IdGroup ? ' selected="selected"' : '').'>'.$tt->GroupName.'</option>';
//                      echo $tt->IdGroup," ",$IdGroup," ",$tt->GroupName,"<br>\n" ;
        }
        $out .= '</select>';
        return $out;
    } // end of getGroupsDropdowns


    private function getModeratorGroupsDropdowns($IdGroup=0) {
        $tt = $this->_model->ModeratorGroupChoice();
        $out = '<select name="IdGroup" id="IdGroup">
            <option value="0">None</option>';
        foreach ($tt as $row => $tt) {
            $out .= '<option value="'.$tt->IdGroup.'"'.($IdGroup == $tt->IdGroup ? ' selected="selected"' : '').'>'.$tt->GroupName.' ['.$tt->Name.'('.$tt->cnt.') </option>';
        }
        $out .= '</select>';
        return $out;
    } // end of getGroupsDropdowns

/*
*       This is the function which is called if the feature is disabled
*/
    public function showFeatureIsClosed()       {
//        PVars::getObj('page')->title = 'Feature Closed - Bewelcome';
                $this->SetPageTitle('Feature Closed - Bewelcome') ;
        require 'templates/featureclosed.php';
        } // end of showFeatureIsClosed()

    protected function printScriptAndStyleTags($package)
    {
        $stylesheetFiles = $this->entryPointLookup->getCssFiles($package);
        foreach ($stylesheetFiles as $stylesheetFile) {
            echo '<link rel="stylesheet" href="' . $stylesheetFile . '">' . PHP_EOL;
        }
        $scriptFiles = $this->entryPointLookup->getJavaScriptFiles($package);
        foreach ($scriptFiles as $scriptFile) {
            echo '<script type="text/javascript" src="' . $scriptFile . '"></script>' . PHP_EOL;
        }
    }

}
