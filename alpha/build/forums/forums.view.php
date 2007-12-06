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

class ForumsView extends PAppView {
	private $_model;
	
	public function __construct(Forums &$model) {
		$this->_model =& $model;
	}
	
	/**
	* Create a new topic in the current forum
	*/
	public function createTopic(&$callbackId) {
		$boards = $this->_model->getBoard();
		$allow_title = true;
		$tags = $this->_model->getTagsNamed();
		$locationDropdowns = $this->getLocationDropdowns();
		$edit = false;
		require TEMPLATE_DIR.'apps/forums/editcreateform.php';	
	}
	
	public function replyTopic(&$callbackId) {
		$boards = $this->_model->getBoard();
		$topic = $this->_model->getTopic();
		$allow_title = false;
		$edit = false;
		require TEMPLATE_DIR.'apps/forums/editcreateform.php';
		
		require TEMPLATE_DIR.'apps/forums/replyLastPosts.php';
	}
	
	public function editPost(&$callbackId) {
		$boards = $this->_model->getBoard();
		$topic = $this->_model->getTopic();
		$vars =& PPostHandler::getVars($callbackId);
		$all_tags = $this->_model->getAllTags();
		$locationDropdowns = $this->getLocationDropdowns();
		$allow_title = $vars['first_postid'] == $vars['postid'];
		$edit = true;
		$messageid = $this->_model->getMessageId();
		require TEMPLATE_DIR.'apps/forums/editcreateform.php';	
	}
	
	/**
	* Display a topic
	*/
	public function showTopic() {
		$topic = $this->_model->getTopic();
		$request = PRequest::get()->request;
		
		$uri = implode('/', $request);
		$uri = rtrim($uri, '/').'/';
		
		require TEMPLATE_DIR.'apps/forums/topic.php';
		
		$currentPage = $this->_model->getPage();
		$itemsPerPage = Forums::POSTS_PER_PAGE;
		$max = $topic->topicinfo->replies + 1;
		$maxPage = ceil($max / Forums::POSTS_PER_PAGE);
		$pages = $this->getPageLinks($currentPage, $itemsPerPage, $max);

		require TEMPLATE_DIR.'apps/forums/pages.php';
	}

	/**
	* Display a number of threads externally
	*/    
    
	public function showExternal() {
		$boards = $this->_model->getBoard();
		$request = PRequest::get()->request;		
		$pages = $this->getBoardPageLinks();
		require TEMPLATE_DIR.'apps/forums/external.php';
	}	
	/**
	* Display a forum
	*/
	
	/* This displays the custom teaser */
	public function teaser() {
		$boards = $this->_model->getBoard();
		$request = PRequest::get()->request;
        require TEMPLATE_DIR.'apps/forums/teaser.php';
    }
	public function userBar() {
        require TEMPLATE_DIR.'apps/forums/userbar.php';
    }
  /* This displays the forum rules and charter */
	public function rules() {
        require TEMPLATE_DIR.'apps/forums/rules.php';
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
        require TEMPLATE_DIR.'apps/rox/topmenu.php';
    }  
		
	public function showForum() {
		$boards = $this->_model->getBoard();
		$request = PRequest::get()->request;
		$uri = implode('/', $request);
		$uri = rtrim($uri, '/').'/';

		$pages = $this->getBoardPageLinks();
		$currentPage = $this->_model->getPage();
		$max = $this->_model->getBoard()->getNumberOfThreads();
		$maxPage = ceil($max / Forums::THREADS_PER_PAGE);
		
		require TEMPLATE_DIR.'apps/forums/board.php';
	}
	
	public function showTopLevel() {
		$boards = $this->_model->getBoard();
		$request = PRequest::get()->request;
		
		$pages = $this->getBoardPageLinks();
		$currentPage = $this->_model->getPage();
		$max = $this->_model->getBoard()->getNumberOfThreads();
		$maxPage = ceil($max / Forums::THREADS_PER_PAGE);
		
		$top_tags = $this->_model->getTopLevelTags();
		$all_tags_maximum = $this->_model->getTagsMaximum();
		$all_tags = $this->_model->getAllTags();
		require TEMPLATE_DIR.'apps/forums/toplevel.php';
	}
	
	public function displaySearchResultPosts($posts) {
		require TEMPLATE_DIR.'apps/forums/searchresultposts.php';
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
	}
}
?>