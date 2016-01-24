<?php
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
    private $_model;
    public $page;
    private $words ;
    public $uri;
    public $forum_uri;
    public $BW_Right;

    public function __construct(Forums &$model) {
        $this->_model =& $model;
        $this->words=$this->_model->words ;
        $this->BW_Right=$this->_model->BW_Right ;
        $this->uri=$this->getURI() ;
        $this->forum_uri='forums' ;
        $this->page = new stdClass();
    }


        public function SetPageTitle($Title) {
            $this->page->title=$Title ;
        }

    /**
    * Create a new topic in the current forum
    */
    public function createTopic(&$callbackId,$IdGroup=0) {
        $boards = $this->_model->getBoard();
        $allow_title = true;
        $tags = $this->_model->getTagsNamed();
        $locationDropdowns = $this->getLocationDropdowns();
//                           die ("1 IdGroup=".$IdGroup) ;
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
        return $baseurl.'s'.$thread->threadid.'-'.preg_replace('/[^A-Za-z0-9]/', '_',$this->words->fTrad($thread->IdTitle) ) ;
    }

    public  function postURL($post, $baseurl = false)
    {
        if ($baseurl === false) {
            $baseurl = $this->uri;
        }
        return $baseurl.'s'.$post->threadid.'-'.preg_replace('/[^A-Za-z0-9]/', '_',$this->words->fTrad($post->IdTitle) ) ;
    }


    public function replyTopic(&$callbackId) {
        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $allow_title = false;
        $edit = false;
        $notifymecheck = "";
        if ($this->_model->IsThreadSubscribed($topic->IdThread,$_SESSION["IdMember"])) {
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
        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $vars =& PPostHandler::getVars($callbackId);
        $all_tags = $this->_model->getAllTags();
        $locationDropdowns = $this->getLocationDropdowns();
        $groupsDropdowns = $this->getGroupsDropdowns($this->_model->IdGroup);
        $allow_title = $vars['first_postid'] == $vars['postid'];
        $edit = true;
        $messageid = $this->_model->getMessageId();
        $notifymecheck = "" ;
        if ($this->_model->IsThreadSubscribed($this->_model->getThreadId(),$_SESSION["IdMember"])) {
            $notifymecheck = 'checked="checked"' ; // This is to tell that the notifyme cell is preticked
        }
        $IdGroup = $this->_model->IdGroup;
        $visibilityThread = $this->_model->GetThreadVisibility($vars['threadid']);
        $visibilityPost = $this->_model->GetPostVisibility($vars['postid']);
        $visibilityCheckbox = $this->getVisibilityCheckbox($visibilityPost, $visibilityThread, $IdGroup, $allow_title);

        // By default no appropriated language is propose, the member can choose to translate
        $LanguageChoices=$this->_model->LanguageChoices() ;
        if (!$translate) { // In case this is a edit, by default force the original post language
            $IdContent=$this->_model->getIdContent();
            global $fTradIdLastUsedLanguage ; $fTradIdLastUsedLanguage=1 ; // willbe change by ftrad
            $word = new MOD_words();
            // This function is just called for finding the language in which one the post will be displayed
            $void_string=$word->ftrad($IdContent) ;
            $AppropriatedLanguage=$fTradIdLastUsedLanguage ;
        }
        $disableTinyMCE = $this->_model->getTinyMCEPreference();
        require 'templates/editcreateform.php';
    } // end of editPost



// This si the Moderator edit/translate
    public function ModeditPost(&$callbackId) {
        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $vars =& PPostHandler::getVars($callbackId);
        $all_tags = $this->_model->getAllTags();
        $locationDropdowns = $this->getLocationDropdowns();
//              echo "<pre>";print_r($this->_model->IdGroup) ;;echo "</pre>" ;
//              die ("\$topic->topicinfo->IdGroup=".$topic->topicinfo->IdGroup) ;
        $groupsDropdowns = $this->getModeratorGroupsDropdowns($this->_model->IdGroup);
        $allow_title = $vars['first_postid'] == $vars['postid'];
        $edit = true;
        $messageid = $this->_model->getMessageId();
        $notifymecheck="";
        if ($this->_model->IsThreadSubscribed($this->_model->getThreadId(),$_SESSION["IdMember"])) {
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

        if (isset($topic->topicinfo->IdGroup) && ($topic->topicinfo->IdGroup > 0) && isset($_SESSION["IdMember"])) {
             $group_id = $topic->topicinfo->IdGroup;
             $memberIsGroupMember = $this->_model->checkGroupMembership($group_id);
        }
        // maybe in a later commit..
        if (isset($topic->topicinfo->IdTitle)) {
            $this->SetPageTitle($this->words->fTrad($topic->topicinfo->IdTitle));
        }
        else {
            $this->SetPageTitle($topic->topicinfo->title. ' - BeWelcome '.$this->words->getBuffered('Forum'));
        }
        if (empty($_SESSION['IdMember']))  {
            if (isset($topic->posts[0])) {
                $this->page->SetMetaDescription(strip_tags($this->_model->words->fTrad(($topic->posts[0]->IdContent)))) ; ;
            }
            $wordtag="" ;

            for ($ii=0;$ii<$topic->topicinfo->NbTags;$ii++) {
                if ($ii>0) {
                    $wordtag.=',' ;
                }
                $wordtag.=$this->_model->words->fTrad($topic->topicinfo->IdName[$ii]) ;
            }
            if ($wordtag!="") {
                $wordtag.="," ;
            }
            $wordtag.=$this->_model->words->getFormatted("default_meta_keyword") ;
            $this->page->SetMetaKey($wordtag)  ;
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
        $topboards = $this->_model->getTopCategoryLevelTags();
        $request = PRequest::get()->request;
        require 'templates/teaser.php';
    }
    public function userBar() {
        if (isset($_SESSION["IdMember"])) {
            $topboards = $this->_model->getTopCategoryLevelTags();
            require 'templates/userbar.php';
        }
    }
    public function showCategoriesContinentsTagcloud() {
        $top_tags = $this->_model->getTopCategoryLevelTags();
        $all_tags_maximum = $this->_model->getTagsMaximum();
        $all_tags = $this->_model->getAllTags();
        require 'templates/categories_continents_tagcloud.php';
    }
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
        if ($boards->IdGroup == SuggestionsModel::getGroupId()) {
            $noForumNewTopicButton = true;
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


        $top_tags = $this->_model->getTopCategoryLevelTags();
        $all_tags_maximum = $this->_model->getTagsMaximum();
        $all_tags = $this->_model->getAllTags();
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

        $top_tags = $this->_model->getTopCategoryLevelTags();
        $all_tags_maximum = $this->_model->getTagsMaximum();
        $all_tags = $this->_model->getAllTags();
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

        $top_tags = $this->_model->getTopCategoryLevelTags();
        $all_tags_maximum = $this->_model->getTagsMaximum();
        $all_tags = $this->_model->getAllTags();
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

    public function generateClickableTagSuggestions($tags) {
        if ($tags) {
            $out = '';
            foreach ($tags as $suggestion) {
                $out .= '<a href="#" onclick="javascript:ForumsSuggest.updateForm(\'';
                foreach ($suggestion as $word) {
                    $out .= htmlspecialchars($word, ENT_QUOTES).', ';
                }
                $out = rtrim($out, ', ');
                $out .= '\'); return false;">'.htmlspecialchars($word, ENT_QUOTES).'</a>, ';
            }
            $out = rtrim($out, ', ');
            return $out;
        }
        return '';
    }

    private function getVisibilityCheckbox($visibility, $highestVisibility, $IdGroup, $newTopic) {
        if ($IdGroup == 0) {
            // Indicate to the form that only MembersOnly is allowed; this is a hack to avoid too much code changes
            return '';
        }

        if ($IdGroup == SuggestionsModel::getGroupId()) {
            // Indicate to the form that only MembersOnly is allowed;
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

        $out = '<input type="checkbox" name="' . $name . '" id="' . $name . '" value="GroupOnly"';
        if ($visibility == 'GroupOnly') {
            $out .= ' checked="checked" ';
        }
        $out .= '/> <label for="' . $name . '">' . $words->get($word) . '</label>';
        return $out;
    }

    private function getContinentDropdown($preselect = false) {
        $continents = $this->_model->getAllContinents();

        $out = '<select name="d_continent" id="d_continent" onchange="javascript: updateContinent();">
        <option value="">' . $this->words->getFormatted("SelectNone") . '</option>';
        foreach ($continents as $code => $continent) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$continent.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    private function getCountryDropdown($continent, $preselect = false) {
        $countries = $this->_model->getAllCountries($continent);
        $out = '<select name="d_country" id="d_country" onchange="javascript: updateCountry();">
            <option value="">' . $this->words->getFormatted("SelectNone") . '</option>';
        foreach ($countries as $code => $country) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$country.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    private function getAreaDropdown($country, $preselect = false) {
        $areas = $this->_model->getAllAdmincodes($country);
        $out = '<select name="d_admin" id="d_admin" onchange="javascript: updateAdmincode();">
            <option value="">' . $this->words->getFormatted("SelectNone") . '</option>';
        foreach ($areas as $code => $area) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$area.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    private function getLocationDropdown($country, $areacode, $preselect = false) {
        $locations = $this->_model->getAllLocations($country, $areacode);
        $out = '<select name="d_geoname" id="d_geoname" onchange="javascript: updateGeonames();">
            <option value="">' . $this->words->getFormatted("SelectNone") . '</option>';
        foreach ($locations as $code => $location) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$location.'</option>';
        }
        $out .= '</select>';
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



    public function getLocationDropdowns() {
        $out = '';

        $out .= $this->getContinentDropdown($this->_model->getContinent());

        if ($this->_model->getContinent()) {
            $out .= $this->getCountryDropdown($this->_model->getContinent(), $this->_model->getCountryCode());

            if ($this->_model->getCountryCode()) {
                $out .= $this->getAreaDropdown($this->_model->getCountryCode(), $this->_model->getAdminCode());

                if ($this->_model->getAdminCode()) {
                    $out .= $this->getLocationDropdown($this->_model->getCountryCode(), $this->_model->getAdminCode(), $this->_model->getGeonameid());
                }
            }
        }

        return $out;
    } // end of getLocationDropdowns

/*
*       This is the function which is called if the feature is disabled
*/
    public function showFeatureIsClosed()       {
//        PVars::getObj('page')->title = 'Feature Closed - Bewelcome';
                $this->SetPageTitle('Feature Closed - Bewelcome') ;
        require 'templates/featureclosed.php';
        } // end of showFeatureIsClosed()
}
?>
