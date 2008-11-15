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
    
    public function __construct(Forums &$model) {
        $this->_model =& $model;
    }
    
    /**
    * Create a new topic in the current forum
    */
    public function createTopic(&$callbackId,$IdGroup=0) {
        $boards = $this->_model->getBoard();
        $allow_title = true;
        $tags = $this->_model->getTagsNamed();
        $locationDropdowns = $this->getLocationDropdowns();
        $groupsDropdowns = $this->getGroupsDropdowns($IdGroup);
        $edit = false;
        $notifymecheck="checked" ; // This is to tell that the notifyme cell is preticked
		    $AppropriatedLanguage=0 ; // By default english will be proposed as défault language
		    $LanguageChoices=$this->_model->LanguageChoices() ;
        require 'templates/editcreateform.php';    
    }
    
    
    /**
     * returns a good-looking url for a forum thread
     * // TODO: maybe there's a better place for this.
     *
     * @param $thread as read from the threads database with mysql_fetch_object
     * @return string to be used as url
     */
    public static function threadURL($thread)
    {
        return 'forums/s'.$thread->threadid.'-'.str_replace(
           array('/', ' '),
           array('-', '-'),
           $thread->title
        );
    }
    
    public static function postURL($post)
    {
        return 'forums/s'.$post->threadid.'-'.str_replace(
            array('/', ' '),
            array('-', '-'),
            $post->title
        );
    }
    
    
    public function replyTopic(&$callbackId) {
        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $allow_title = false;
        $edit = false;
         $notifymecheck="" ;
        if ($this->_model->IsThreadSubscribed($topic->IdThread,$_SESSION["IdMember"])) {
            $notifymecheck="checked" ; // This is to tell that the notifyme cell is preticked
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
        require 'templates/editcreateform.php';
        
        require 'templates/replyLastPosts.php';
    }
    
// THis si the normal edit/translate post by a member
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
        $notifymecheck="" ;
        if ($this->_model->IsThreadSubscribed($this->_model->getThreadId(),$_SESSION["IdMember"])) {
            $notifymecheck="checked" ; // This is to tell that the notifyme cell is preticked
        }
				
// By default no appropriated language is propose, the member can choose to translate
		 		$LanguageChoices=$this->_model->LanguageChoices() ;
				if (!$translate) { // In case this is a edit, by default force the original post language
					$IdContent=$this->_model->getIdContent();
					global $fTradIdLastUsedLanguage ; $fTradIdLastUsedLanguage=1 ; // willbe change by ftrad
        	$word = new MOD_words();
					// This function is just called for findinf the language in which one the post will be displayed
					$void_string=$word->ftrad($IdContent) ;
					$AppropriatedLanguage=$fTradIdLastUsedLanguage ;
//		 		$AppropriatedLanguage=$this->_model->FindAppropriatedLanguage($vars['first_postid']) ;
				}
//		 $AppropriatedLanguage=$this->_model->FindAppropriatedLanguage($vars['first_postid']) ;
        require 'templates/editcreateform.php';    
    } // end of editPost
    

    
// THis si the Moderator edit/translate
    public function ModeditPost(&$callbackId) {
        $boards = $this->_model->getBoard();
        $topic = $this->_model->getTopic();
        $vars =& PPostHandler::getVars($callbackId);
        $all_tags = $this->_model->getAllTags();
        $locationDropdowns = $this->getLocationDropdowns();
//				echo "<pre>";print_r($this->_model->IdGroup) ;;echo "</pre>" ;
//				die ("\$topic->topicinfo->IdGroup=".$topic->topicinfo->IdGroup) ;
        $groupsDropdowns = $this->getModeratorGroupsDropdowns($this->_model->IdGroup);
        $allow_title = $vars['first_postid'] == $vars['postid'];
        $edit = true;
        $messageid = $this->_model->getMessageId();
        $notifymecheck="" ;
        if ($this->_model->IsThreadSubscribed($this->_model->getThreadId(),$_SESSION["IdMember"])) {
            $notifymecheck="checked" ; // This is to tell that the notifyme cell is preticked
        }
				$AppropriatedLanguage=$this->_model->FindAppropriatedLanguage($vars['first_postid']) ;
				$LanguageChoices=$this->_model->LanguageChoices() ;
        require 'templates/editcreateform.php';    
    } // end of editPost
    
    /**
    * Display a topic 
    */
    public function showTopic()
    {
        $topic = $this->_model->getTopic();
        $request = PRequest::get()->request;
        
        // maybe in a later commit..
        $words = new MOD_words();
        PVars::getObj('page')->title = $topic->topicinfo->title. ' - BeWelcome '.$words->getBuffered('Forum');
        
        $uri = implode('/', $request);
        $uri = rtrim($uri, '/').'/';

        require 'templates/topic.php';
        $currentPage = $this->_model->getPage();
        $itemsPerPage = Forums::POSTS_PER_PAGE;
        $max = $topic->topicinfo->replies + 1;
        $maxPage = ceil($max / Forums::POSTS_PER_PAGE);
        $pages = $this->getPageLinks($currentPage, $itemsPerPage, $max);
        

        require 'templates/pages.php';
    } // end of ShowTopic



    /**
    * Display the form for a Moderator edit
	 * This is the form with the list of all available translations for a given post
    */    
    public function showModeratorEditPost(&$callbackId,$DataPost)     {
        PVars::getObj('page')->title = "Moderator Edit Post";
        $vars =& PPostHandler::getVars($callbackId);
        $groupsDropdowns = $this->getModeratorGroupsDropdowns($this->_model->IdGroup);
        require 'templates/modpostform.php';
    } // end of showModeratorEditPost

    /**
    * Display the form for a Moderator edit
    */    
    public function showModeratorEditTag(&$callbackId,$DataTag)     {
        PVars::getObj('page')->title = "Moderator Edit Tag";
        $vars =& PPostHandler::getVars($callbackId);
        require 'templates/modtagform.php';
    } // end of showModeratorEditTag

    /**
    * Display a number of threads externally
    */    
    
    public function showExternal() {
        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;        
        $pages = $this->getBoardPageLinks();
        require 'templates/external.php';
    }    
    /**
    * Display a forum
    */
    
    /* This displays the custom teaser */
    public function teaser() {
        $boards = $this->_model->getBoard();
        $topboards = $this->_model->getTopLevelTags();
        $request = PRequest::get()->request;
        require 'templates/teaser.php';
    }
    public function userBar() {
        require 'templates/userbar.php';
    }
    /* This displays the forum rules and charter */
    public function rules() {
        require 'templates/rules.php';
    }  
    /* This adds custom styles to the page*/
    public function customStyles() {
        $out = '';
        /* 2column layout */
        // $out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/bw_basemod_2col.css" type="text/css"/>';
        $out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/forums.css" type="text/css"/>';
        return $out;
    }
  
    public function topMenu($currentTab) {
        require TEMPLATE_DIR . 'shared/roxpage/topmenu.php';
    }  
        
    public function showForum() {
        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;
        $uri = implode('/', $request);
        $uri = rtrim($uri, '/').'/';
        
        $words = new MOD_words();
        PVars::getObj('page')->title=$boards->getBoardName().' - BeWelcome '.$words->getBuffered('Forum');

        $pages = $this->getBoardPageLinks();
        $currentPage = $this->_model->getPage();
        $max = $this->_model->getBoard()->getNumberOfThreads();
        $maxPage = ceil($max / Forums::THREADS_PER_PAGE);
        
        require 'templates/board.php';
    }
    
    public function showTopLevel()
    {
        $words = new MOD_words();
        PVars::getObj('page')->title = $words->getBuffered('Forum').' - BeWelcome';
        
        $boards = $this->_model->getBoard();
        $request = PRequest::get()->request;
        
        $pages = $this->getBoardPageLinks();
        $currentPage = $this->_model->getPage();
        $max = $this->_model->getBoard()->getNumberOfThreads();
        $maxPage = ceil($max / Forums::THREADS_PER_PAGE);
        
        $top_tags = $this->_model->getTopLevelTags();
        $all_tags_maximum = $this->_model->getTagsMaximum();
        $all_tags = $this->_model->getAllTags();
        require 'templates/toplevel.php';
    }
    
    public function displaySearchResultSubscriptions($TResults) {
        require 'templates/searchresultsubscriptions.php';
    }
    public function displaySearchResultPosts($posts) {
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
        $itemsPerPage = Forums::THREADS_PER_PAGE;
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
                    $out .= $word.', ';
                }
                $out = rtrim($out, ', ');
                $out .= '\'); return false;">'.$word.'</a>, ';
            }
            $out = rtrim($out, ', ');
            return $out;
        }
        return '';
    }
    
    private function getContinentDropdown($preselect = false) {
        $continents = $this->_model->getAllContinents();
        
        $out = '<select name="d_continent" id="d_continent" onchange="javascript: updateContinent();">
            <option value="">None</option>';
        foreach ($continents as $code => $continent) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$continent.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    
    private function getCountryDropdown($continent, $preselect = false) {
        $countries = $this->_model->getAllCountries($continent);
        $out = '<select name="d_country" id="d_country" onchange="javascript: updateCountry();">
            <option value="">None</option>';
        foreach ($countries as $code => $country) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$country.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    private function getAreaDropdown($country, $preselect = false) {
        $areas = $this->_model->getAllAdmincodes($country);
        $out = '<select name="d_admin" id="d_admin" onchange="javascript: updateAdmincode();">
            <option value="">None</option>';
        foreach ($areas as $code => $area) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$area.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    private function getLocationDropdown($country, $areacode, $preselect = false) {
        $locations = $this->_model->getAllLocations($country, $areacode);
        $out = '<select name="d_geoname" id="d_geoname" onchange="javascript: updateGeonames();">
            <option value="">None</option>';
        foreach ($locations as $code => $location) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$location.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    private function getCategoriesDropdown($category, $preselect = false) {
        $tags = $this->_model->getTopLevelTags();
        $out = '<select name="d_geoname" id="d_geoname" onchange="javascript: updateGeonames();">
            <option value="">None</option>';
        foreach ($locations as $code => $location) {
            $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$location.'</option>';
        }
        $out .= '</select>';
        return $out;
    }
    
    private function getGroupsDropdowns($IdGroup=0) {
        $tt = $this->_model->GroupChoice();
        $out = '<select name="IdGroup" id="IdGroup"><option value="0">None</option>';
//				die ("IdGroup=".$IdGroup) ;
        foreach ($tt as $row => $tt) {
            $out .= '<option value="'.$tt->IdGroup.'"'.($IdGroup == $tt->IdGroup ? ' selected="selected"' : '').'>'.$tt->GroupName.'</option>';
//						echo $tt->IdGroup," ",$IdGroup," ",$tt->GroupName,"<br>\n" ;
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
*		This is the function which is called if the feature is disabled
*/
    public function showFeatureIsClosed()		{
        PVars::getObj('page')->title = 'Feature Closed - Bewelcome';
        require 'templates/featureclosed.php';
		} // end of showFeatureIsClosed()
}
?>
