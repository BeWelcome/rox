<?php
/**
* Forums model
* 
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id: forums.model.php 32 2007-04-03 10:22:22Z marco_p $
*/

class Forums extends PAppModel {

	const THREADS_PER_PAGE = 30;
	const POSTS_PER_PAGE = 30;
	const NUMBER_LAST_POSTS_PREVIEW = 5; // Number of Posts shown as a help on the "reply" page
	
	public function __construct() {
		parent::__construct();
	}
	
	public static $continents = array(
		'AF' => 'Africa',
		'AN' => 'Antarctica',
		'AS' => 'Asia',
		'EU' => 'Europe',
		'NA' => 'North America',
		'SA' => 'South Amercia',
		'OC' => 'Oceania'
		);
	
	private function boardTopLevel() {
		if ($this->tags) {
			$subboards = array();
			$taginfo = $this->getTagsNamed();
			
			$url = 'forums';
			
			$subboards[$url] = 'Forums';
			
			for ($i = 0; $i < count($this->tags) - 1; $i++) {
				if (isset($taginfo[$this->tags[$i]])) {
					$url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
					$subboards[$url] = $taginfo[$this->tags[$i]];
				}
			}
			
			$title = $taginfo[$this->tags[count($this->tags) -1]];
			$href = $url.'/t'.$this->tags[count($this->tags) -1].'-'.$title;
			
			$this->board = new Board($this->dao, $title, $href, $subboards, $this->tags, $this->continent);
			$this->board->initThreads($this->getPage());
		} else {
			$this->board = new Board($this->dao, 'Forums', '.');
			foreach (Forums::$continents as $code => $name) {
				$this->board->add(new Board($this->dao, $name, 'k'.$code.'-'.$name));
			}
			$this->board->initThreads($this->getPage());
		}
	}
	
	private function boardContinent() {
		if (!isset(Forums::$continents[$this->continent]) || !Forums::$continents[$this->continent]) {
			throw new PException('Invalid Continent');
		}
		
		$subboards = array('forums/' => 'Forums');
		
		$url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent];
		$href = $url;
		if ($this->tags) {
			$taginfo = $this->getTagsNamed();
			
			$subboards[$url] = Forums::$continents[$this->continent];
			
			for ($i = 0; $i < count($this->tags) - 1; $i++) {
				if (isset($taginfo[$this->tags[$i]])) {
					$url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
					$subboards[$url] = $taginfo[$this->tags[$i]];
				}
			}
			
			$title = $taginfo[$this->tags[count($this->tags) -1]];
			
		} else {
			$title = Forums::$continents[$this->continent];
		}
		
		$this->board = new Board($this->dao, $title, $href, $subboards, $this->tags, $this->continent);
		
		$countries = $this->getAllCountries($this->continent);
		foreach ($countries as $code => $country) {
			$this->board->add(new Board($this->dao, $country, 'c'.$code.'-'.$country));
		}

		$this->board->initThreads($this->getPage());
	}
	
	public function getAllCountries($continent) {
		$query = sprintf("SELECT `iso_alpha2`, `name` 
			FROM `geonames_countries` 
			WHERE `continent` = '%s'
			ORDER BY `name` ASC",
			$continent);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve countries!');
		}
		$countries = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->iso_alpha2] = $row->name;
		}
		return $countries;	
	}
	
	private function boardAdminCode() {
		$query = sprintf("SELECT `name`, `continent` 
			FROM `geonames_countries` 
			WHERE `iso_alpha2` = '%s'
			",
			$this->countrycode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('No such Country');
		}
		$countrycode = $s->fetch(PDB::FETCH_OBJ);
		
		$navichain = array('forums/' => 'Forums', 
			'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/' => Forums::$continents[$this->continent],
			'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/' => $countrycode->name);
	
		$query = sprintf("SELECT `name` 
			FROM `geonames_admincodes` 
			WHERE `country_code` = '%s' AND `admin_code` = '%s'
			",
			$this->countrycode, $this->admincode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('No such Admincode');
		}
		$admincode = $s->fetch(PDB::FETCH_OBJ);

		$url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/a'.$this->admincode.'-'.$admincode->name;
		$href = $url;
		if ($this->tags) {
			$taginfo = $this->getTagsNamed();
			
			
			$navichain[$url] = $admincode->name;
			
			for ($i = 0; $i < count($this->tags) - 1; $i++) {
				if (isset($taginfo[$this->tags[$i]])) {
					$url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
					$navichain[$url] = $taginfo[$this->tags[$i]];
				}
			}
			
			$title = $taginfo[$this->tags[count($this->tags) -1]];
		} else {
			$title = $admincode->name;
		}

		$this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode, $this->admincode);
		
		$locations = $this->getAllLocations($this->countrycode, $this->admincode);
		foreach ($locations as $geonameid => $name) {
			$this->board->add(new Board($this->dao, $name, 'g'.$geonameid.'-'.$name));
		}
		$this->board->initThreads($this->getPage());
	}
	
	public function getAllLocations($countrycode, $admincode) {
		$query = sprintf("SELECT `geonameid`, `name` 
			FROM `geonames_cache` 
			WHERE `fk_countrycode` = '%s' AND `fk_admincode` = '%s'
			ORDER BY `population` DESC
			LIMIT 100",
			$countrycode, $admincode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Districts!');
		}
		$locations = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$locations[$row->geonameid] = $row->name;
		}
		natcasesort($locations);
		return $locations;		
	}
	
	private function boardCountry() {
		$query = sprintf("SELECT `name`, `continent` 
			FROM `geonames_countries` 
			WHERE `iso_alpha2` = '%s'
			",
			$this->countrycode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('No such Country');
		}
		$countrycode = $s->fetch(PDB::FETCH_OBJ);
		
		$navichain = array('forums/' => 'Forums', 
			'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/' => Forums::$continents[$this->continent]);
		
		$url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name;
		$href = $url;
		if ($this->tags) {
			$taginfo = $this->getTagsNamed();
			
			
			$navichain[$url] = $countrycode->name;
			
			for ($i = 0; $i < count($this->tags) - 1; $i++) {
				if (isset($taginfo[$this->tags[$i]])) {
					$url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
					$navichain[$url] = $taginfo[$this->tags[$i]];
				}
			}
			
			$title = $taginfo[$this->tags[count($this->tags) -1]];
		} else {
			$title = $countrycode->name;
		}
		
		
		$this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode);
		
		$admincodes = $this->getAllAdmincodes($this->countrycode);
		foreach ($admincodes as $code => $name) {
			$this->board->add(new Board($this->dao, $name, 'a'.$code.'-'.$name));
		}
		
		$this->board->initThreads($this->getPage());
	}
	
	public function getAllAdmincodes($country_code) {
		$query = sprintf("SELECT `admin_code`, `name` 
			FROM `geonames_admincodes` 
			WHERE `country_code` = '%s'
			ORDER BY `name` ASC",
			$country_code);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Districts!');
		}
		$admincodes = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$admincodes[$row->admin_code] = $row->name;
		}
		return $admincodes;
	}
	
	private function boardLocation() {
		$query = sprintf("SELECT `name`, `continent` 
			FROM `geonames_countries` 
			WHERE `iso_alpha2` = '%s'
			",
			$this->countrycode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('No such Country');
		}
		$countrycode = $s->fetch(PDB::FETCH_OBJ);

	
		$query = sprintf("SELECT `name` 
			FROM `geonames_admincodes` 
			WHERE `country_code` = '%s' AND `admin_code` = '%s'
			",
			$this->countrycode, $this->admincode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('No such Admincode');
		}
		$admincode = $s->fetch(PDB::FETCH_OBJ);
		
		$navichain = array('forums/' => 'Forums', 
			'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/' => Forums::$continents[$this->continent],
			'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/' => $countrycode->name,
			'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/a'.$this->admincode.'-'.$admincode->name.'/' => $admincode->name);
				
		$query = sprintf("SELECT `name` 
			FROM `geonames_cache` 
			WHERE `geonameid` = '%d'
			",
			$this->geonameid);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('No such Country');
		}
		$geonameid = $s->fetch(PDB::FETCH_OBJ);
		
		$url = 'forums/k'.$this->continent.'-'.Forums::$continents[$this->continent].'/c'.$this->countrycode.'-'.$countrycode->name.'/a'.$this->admincode.'-'.$admincode->name.'/g'.$this->geonameid.'-'.$geonameid->name;
		$href = $url;
		if ($this->tags) {
			$taginfo = $this->getTagsNamed();
			
			$navichain[$url] = $geonameid->name;
			for ($i = 0; $i < count($this->tags) - 1; $i++) {
				if (isset($taginfo[$this->tags[$i]])) {
					$url = $url.'/t'.$this->tags[$i].'-'.$taginfo[$this->tags[$i]];
					$navichain[$url] = $taginfo[$this->tags[$i]];
				}
			}
			
			$title = $taginfo[$this->tags[count($this->tags) -1]];
		} else {
			$title = $geonameid->name;
		}
		
		$this->board = new Board($this->dao, $title, $href, $navichain, $this->tags, $this->continent, $this->countrycode, $this->admincode, $this->geonameid);
		$this->board->initThreads($this->getPage());
	}
	
	/**
	* Fetch all required data for the view to display a forum
	*/
	public function prepareForum() {
		if (!$this->geonameid && !$this->countrycode && !$this->continent) { 
			$this->boardTopLevel();
		} else if ($this->continent && !$this->geonameid && !$this->countrycode) { 
			$this->boardContinent();
		} else if (isset($this->admincode) && $this->admincode && $this->continent && $this->countrycode && !$this->geonameid) { 
			$this->boardadminCode();
		} else if ($this->continent && $this->countrycode && !$this->geonameid) {
			$this->boardCountry();
		} else if ($this->continent && $this->countrycode && $this->geonameid && isset($this->admincode) && $this->admincode) { 
			$this->boardLocation();
		} else {
			if (PVars::get()->debug) {
				throw new PException('Invalid Request');
			} else {
				PRequest::home();
			}
		}
	}
	
	private $board;
	public function getBoard() {
		return $this->board;
	}
	
	public function createProcess() {
		if (!($User = APP_User::login())) {
			return false;
		}
		
		$vars =& PPostHandler::getVars();

		$vars_ok = $this->checkVarsTopic($vars);
		if ($vars_ok) {
			$topicid = $this->newTopic($vars);
			PPostHandler::clearVars();
			return PVars::getObj('env')->baseuri.'forums/s'.$topicid;
		} else {
			return false;
		}
	
	}
	
	/*
	* Fill the Vars in order to edit a post
	*/
	public function getEditData($callbackId) {
		$query = sprintf("SELECT `postid`, `authorid`, `message` AS `topic_text`, 
				`title` AS `topic_title`, `first_postid`, `last_postid`,
				`forums_threads`.`continent`,
				`forums_threads`.`geonameid`,
				`forums_threads`.`admincode`,
				`forums_threads`.`countrycode`,
				`forums_threads`.`tag1` AS `tag1id`, `tags1`.`tag` AS `tag1`,
				`forums_threads`.`tag2` AS `tag2id`, `tags2`.`tag` AS `tag2`,
				`forums_threads`.`tag3` AS `tag3id`, `tags3`.`tag` AS `tag3`,
				`forums_threads`.`tag4` AS `tag4id`, `tags4`.`tag` AS `tag4`,
				`forums_threads`.`tag5` AS `tag5id`, `tags5`.`tag` AS `tag5`
			FROM `forums_posts`
			LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
			LEFT JOIN `forums_tags` AS `tags1` ON (`forums_threads`.`tag1` = `tags1`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags2` ON (`forums_threads`.`tag2` = `tags2`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags3` ON (`forums_threads`.`tag3` = `tags3`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags4` ON (`forums_threads`.`tag4` = `tags4`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags5` ON (`forums_threads`.`tag5` = `tags5`.`tagid`)
			WHERE `postid` = '%d'", $this->messageId);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Postinfo!');
		}
		$vars =& PPostHandler::getVars($callbackId);
		$vars = $s->fetch(PDB::FETCH_ASSOC);
		$tags = array();
		for ($i = 1; $i <= 5; $i++) {
			$key = 'tag'.$i;
			if (isset($vars[$key]) && $vars[$key]) {
				$tags[] = $vars[$key];
			} 
		}
		$vars['tags'] = implode(', ', $tags);
		$this->admincode = $vars['admincode'];
		$this->continent = $vars['continent'];
		$this->countrycode = $vars['countrycode'];
		$this->geonameid = $vars['geonameid'];
	}
	
	public function editProcess() {
		if (!($User = APP_User::login())) {
			return false;
		}
		
		$vars =& PPostHandler::getVars();
		
		$query = sprintf("SELECT `postid`, `authorid`, `forums_posts`.`threadid`, 
				`first_postid`, `last_postid`
			FROM `forums_posts`
			LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
			WHERE `postid` = '%d'", $this->messageId);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Postinfo!');
		}
		$postinfo = $s->fetch(PDB::FETCH_OBJ);
		
		if ($User->hasRight('edit_foreign@forums') || ($User->hasRight('edit_own@forums') && $postinfo->authorid == $User->getId())) {
			$is_topic = ($postinfo->postid == $postinfo->first_postid);
			
			if ($is_topic) {
				$vars_ok = $this->checkVarsTopic($vars);
			} else {
				$vars_ok = $this->checkVarsReply($vars);
			}
			if ($vars_ok) {
				$this->dao->query("START TRANSACTION");
		
				$this->editPost($vars, $User->getId());
				if ($is_topic) {
					$this->editTopic($vars, $postinfo->threadid);
				}
		
				$this->dao->query("COMMIT");
				
				PPostHandler::clearVars();
				return PVars::getObj('env')->baseuri.'forums/s'.$postinfo->threadid;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function editPost($vars, $editorid) {
		$query = sprintf("UPDATE `forums_posts` SET `message` = '%s', `last_edittime` = NOW(), `last_editorid` = '%d', `edit_count` = `edit_count` + 1 WHERE `postid` = '%d'",
			$this->dao->escape($this->cleanupText($vars['topic_text'])), $editorid, $this->messageId);
		$this->dao->query($query);
	}

	private function subtractTagCounter($threadid) {
		$query = sprintf("SELECT `tag1`, `tag2`, `tag3`, `tag4`, `tag5`
			FROM `forums_threads`
			WHERE `threadid` = '%d'", $threadid);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Taginfo!');
		}
		$old_tags = $s->fetch(PDB::FETCH_OBJ);
		if ($old_tags->tag1) {
			$query = "UPDATE `forums_tags` SET `counter` = IF (`counter` > 0, `counter` - 1, 0) WHERE `tagid` = '".$old_tags->tag1."'";
			$this->dao->query($query);
		}
		if ($old_tags->tag2) {
			$query = "UPDATE `forums_tags` SET `counter` = IF (`counter` > 0, `counter` - 1, 0) WHERE `tagid` = '".$old_tags->tag2."'";
			$this->dao->query($query);
		}
		if ($old_tags->tag3) {
			$query = "UPDATE `forums_tags` SET `counter` = IF (`counter` > 0, `counter` - 1, 0) WHERE `tagid` = '".$old_tags->tag3."'";
			$this->dao->query($query);
		}
		if ($old_tags->tag4) {
			$query = "UPDATE `forums_tags` SET `counter` = IF (`counter` > 0, `counter` - 1, 0) WHERE `tagid` = '".$old_tags->tag4."'";
			$this->dao->query($query);
		}
		if ($old_tags->tag5) {
			$query = "UPDATE `forums_tags` SET `counter` = IF (`counter` > 0, `counter` - 1, 0) WHERE `tagid` = '".$old_tags->tag5."'";
			$this->dao->query($query);
		}
	}
	
	private function editTopic($vars, $threadid) {
		$this->subtractTagCounter($threadid);
		
		$query = sprintf("UPDATE `forums_threads` 
			SET `title` = '%s', `tag1` = NULL, `tag2` = NULL, `tag3` = NULL, `tag4` = NULL, `tag5` = NULL,
				`geonameid` = %s, `admincode` = %s, `countrycode` = %s, `continent` = %s
			WHERE `threadid` = '%d'", 
			$this->dao->escape(strip_tags($vars['topic_title'])), 
			($this->geonameid ? "'".(int)$this->geonameid."'" : 'NULL'),
			(isset($this->admincode) && $this->admincode ? "'".$this->dao->escape($this->admincode)."'" : 'NULL'),
			($this->countrycode ? "'".$this->dao->escape($this->countrycode)."'" : 'NULL'),
			($this->continent ? "'".$this->dao->escape($this->continent)."'" : 'NULL'),
			$threadid);
		$this->dao->query($query);
		
		$this->updateTags($vars, $threadid);
	}
	
	public function replyProcess() {
		if (!($User = APP_User::login())) {
			return false;
		}
		
		$vars =& PPostHandler::getVars();

		$this->checkVarsReply($vars);
		$this->replyTopic($vars);
	
		PPostHandler::clearVars();
		return PVars::getObj('env')->baseuri.'forums/s'.$this->threadid;
	}
	
	
	public function delProcess() {
		if (!($User = APP_User::login())) {
			return false;
		}
		
		if ($User->hasRight('delete@forums')) {
			$this->dao->query("START TRANSACTION");
			
			$query = sprintf("SELECT `forums_posts`.`threadid`, `forums_threads`.`first_postid`, `forums_threads`.`last_postid`
				FROM `forums_posts`
				LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
				WHERE `forums_posts`.`postid` = '%d'
				",
				$this->messageId);
			$s = $this->dao->query($query);
			if (!$s) {
				throw new PException('Could not retrieve Threadinfo!');
			}
			$topicinfo = $s->fetch(PDB::FETCH_OBJ);
			
			if ($topicinfo->first_postid == $this->messageId) { // Delete the complete topic
				$this->subtractTagCounter($topicinfo->threadid);
				
				$query = sprintf("UPDATE `forums_threads` SET `first_postid` = NULL, `last_postid` = NULL WHERE `threadid` = '%d'", $topicinfo->threadid);
				$this->dao->query($query);
				
				$query = sprintf("DELETE FROM `forums_posts` WHERE `threadid` = '%d'", $topicinfo->threadid);
				$this->dao->query($query);
				
				$query = sprintf("DELETE FROM `forums_threads` WHERE `threadid` = '%d'", $topicinfo->threadid);
				$this->dao->query($query);
			
				$redir = 'forums';
			} else { // Delete a single post
				/*
				* Check if we are deleting the very last post of a topic
				* if so, we have to update the `last_postid` field of the `forums_threads` table
				*/ 
				if ($topicinfo->last_postid == $this->messageId) {
					$query = sprintf("UPDATE `forums_threads` SET `last_postid` = NULL WHERE `threadid` = '%d'", $topicinfo->threadid);
					$this->dao->query($query);
				}
				
				$query = sprintf("DELETE FROM `forums_posts` WHERE `postid` = '%d'", $this->messageId);
				$this->dao->query($query);
			
				if ($topicinfo->last_postid == $this->messageId) {
					$query = sprintf("SELECT `postid` 
						FROM `forums_posts` 
						WHERE `threadid` = '%d'
						ORDER BY `create_time` DESC LIMIT 1",
						$topicinfo->threadid);
					$s = $this->dao->query($query);
					if (!$s) {
						throw new PException('Could not retrieve Postinfo!');
					}
					$lastpost = $s->fetch(PDB::FETCH_OBJ);
					
					$lastpostupdate = sprintf(", `last_postid` = '%d'", $lastpost->postid);
				} else {
					$lastpostupdate = '';
				}
				
				$query = sprintf("UPDATE `forums_threads` SET `replies` = (`replies` - 1) %s WHERE `threadid` = '%d'", $lastpostupdate, $topicinfo->threadid);
				$this->dao->query($query);
				
				$redir = 'forums/s'.$topicinfo->threadid;
			}
			
			$this->dao->query("COMMIT");
		}
	
		
		header('Location: '.PVars::getObj('env')->baseuri.$redir);
		exit;
	}

	
	private function checkVarsReply(&$vars) {
		$errors = array();
        
        if (!isset($vars['topic_text']) || empty($vars['topic_text'])) {
            $errors[] = 'text';
        }
        
        if ($errors) {
        	$vars['errors'] = $errors;
        	return false;
        }
        
        return true;
	}
	
	private function checkVarsTopic(&$vars) {
		$errors = array();
        
        if (!isset($vars['topic_title']) || empty($vars['topic_title'])) {
            $errors[] = 'title';
        }
        if (!isset($vars['topic_text']) || empty($vars['topic_text'])) {
            $errors[] = 'text';
        }
        
        if ($errors) {
        	$vars['errors'] = $errors;
        	return false;
        }
        
        return true;
	}
	
	private function replyTopic(&$vars) {
		if (!($User = APP_User::login())) {
			throw new PException('User gone missing...');
		}
		
		$this->dao->query("START TRANSACTION");
		
		$query = sprintf("INSERT INTO `forums_posts` (`authorid`, `threadid`, `create_time`, `message`)
			VALUES ('%d', '%d', NOW(), '%s')",
			$User->getId(), $this->threadid, $this->dao->escape($this->cleanupText($vars['topic_text'])));
		$result = $this->dao->query($query);
		
		$postid = $result->insertId();
		
		$query = sprintf("UPDATE `forums_threads` SET `last_postid` = '%d', `replies` = `replies` + 1 WHERE `threadid` = '%d'",
			$postid, $this->threadid);
		$this->dao->query($query);
		
		$this->dao->query("COMMIT");
		
		return $postid;
	}
	
	/**
	* Create a new Topic (with initial first post)
	* @return int topicid Id of the newly created topic
	*/
	private function newTopic(&$vars) {
		if (!($User = APP_User::login())) {
			throw new PException('User gone missing...');
		}
		
		$this->dao->query("START TRANSACTION");
		
		$query = sprintf("INSERT INTO `forums_posts` (`authorid`, `create_time`, `message`)
			VALUES ('%d', NOW(), '%s')",
			$User->getId(), $this->dao->escape($this->cleanupText($vars['topic_text'])));
		$result = $this->dao->query($query);
		
		$postid = $result->insertId();
		
		$query = sprintf("INSERT INTO `forums_threads` (`title`, `first_postid`, `last_postid`, `geonameid`, `admincode`, `countrycode`, `continent`)
			VALUES ('%s', '%d', '%d', %s, %s, %s, %s)",
			$this->dao->escape(strip_tags($vars['topic_title'])), $postid, $postid, 
			($this->geonameid ? "'".(int)$this->geonameid."'" : 'NULL'),
			(isset($this->admincode) && $this->admincode ? "'".$this->dao->escape($this->admincode)."'" : 'NULL'),
			($this->countrycode ? "'".$this->dao->escape($this->countrycode)."'" : 'NULL'),
			($this->continent ? "'".$this->dao->escape($this->continent)."'" : 'NULL'));
		$result = $this->dao->query($query);
		
		$threadid = $result->insertId();
		
		$query = sprintf("UPDATE `forums_posts` SET `threadid` = '%d' WHERE `postid` = '%d'", $threadid, $postid);
		$result = $this->dao->query($query);
		
		 // Create the tags
		$this->updateTags($vars, $threadid);
		
		$this->dao->query("COMMIT");
		
		return $threadid;
	}
	
	private function updateTags($vars, $threadid) {
		if (isset($vars['tags']) && $vars['tags']) {
			$tags = explode(',', $vars['tags']);
			$i = 1;
			foreach ($tags as $tag) {
				if ($i > 5) {
					break;
				}
				
				$tag = trim(strip_tags($tag));
				$tag = $this->dao->escape($tag);
				
				// Check if it already exists in our Database
				$query = "SELECT `tagid` FROM `forums_tags` WHERE `tag` = '$tag'";
				$s = $this->dao->query($query);
				$taginfo = $s->fetch(PDB::FETCH_OBJ);
				if ($taginfo) {
					$tagid = $taginfo->tagid;
				} else {
					// Insert it
					$query = "INSERT INTO `forums_tags` (`tag`) VALUES ('$tag')";
					$result = $this->dao->query($query);
					$tagid = $result->insertId();
				}
				if ($tagid) {
					$query = "UPDATE `forums_tags` SET `counter` = `counter` + 1 WHERE `tagid` = '$tagid'";
					$this->dao->query($query);
					$query = "UPDATE `forums_threads` SET `tag$i` = '$tagid' WHERE `threadid` = '$threadid'";
					$this->dao->query($query);
					$i++;
				}
			}
		}
	}
	
	private $topic;
	public function prepareTopic() {
		$this->topic = new Topic();
		
		// Topic Data
		$query = sprintf("SELECT `forums_threads`.`title`, `forums_threads`.`replies`, `forums_threads`.`views`, `forums_threads`.`first_postid`,
				`forums_threads`.`continent`,
				`forums_threads`.`geonameid`, `geonames_cache`.`name` AS `geonames_name`,
				`forums_threads`.`admincode`, `geonames_admincodes`.`name` AS `adminname`,
				`forums_threads`.`countrycode`, `geonames_countries`.`name` AS `countryname`,
				`forums_threads`.`tag1` AS `tag1id`, `tags1`.`tag` AS `tag1`,
				`forums_threads`.`tag2` AS `tag2id`, `tags2`.`tag` AS `tag2`,
				`forums_threads`.`tag3` AS `tag3id`, `tags3`.`tag` AS `tag3`,
				`forums_threads`.`tag4` AS `tag4id`, `tags4`.`tag` AS `tag4`,
				`forums_threads`.`tag5` AS `tag5id`, `tags5`.`tag` AS `tag5`
			FROM `forums_threads`
			LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)
			LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)
			LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)
			LEFT JOIN `forums_tags` AS `tags1` ON (`forums_threads`.`tag1` = `tags1`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags2` ON (`forums_threads`.`tag2` = `tags2`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags3` ON (`forums_threads`.`tag3` = `tags3`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags4` ON (`forums_threads`.`tag4` = `tags4`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags5` ON (`forums_threads`.`tag5` = `tags5`.`tagid`)
			WHERE `threadid` = '%d'
			",
			$this->threadid);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Posts!');
		}
		$topicinfo = $s->fetch(PDB::FETCH_OBJ);
		$this->topic->topicinfo = $topicinfo;

		
		$from = Forums::POSTS_PER_PAGE * ($this->getPage() - 1);
		
		// Posts
		$query = sprintf("SELECT `postid`, UNIX_TIMESTAMP(`create_time`) AS `posttime`, `message`,
				`user`.`id` AS `user_id`, `user`.`handle` AS `user_handle`,
				`geonames_cache`.`fk_countrycode`
			FROM `forums_posts`
			LEFT JOIN `user` ON (`forums_posts`.`authorid` = `user`.`id`)
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `threadid` = '%d'
			ORDER BY `posttime` ASC
			LIMIT %d, %d",
			$this->threadid, $from, Forums::POSTS_PER_PAGE);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Posts!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$this->topic->posts[] = $row;
		}
		
		// Increase the number of views
		$query = sprintf("UPDATE `forums_threads` SET `views` = (`views` + 1) WHERE `threadid` = '%d' LIMIT 1", $this->threadid);
		$this->dao->query($query);
		
	}
	
	public function initLastPosts() {
		$query = sprintf("SELECT `postid`, UNIX_TIMESTAMP(`create_time`) AS `posttime`, `message`,
				`user`.`id` AS `user_id`, `user`.`handle` AS `user_handle`,
				`geonames_cache`.`fk_countrycode`
			FROM `forums_posts`
			LEFT JOIN `user` ON (`forums_posts`.`authorid` = `user`.`id`)
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `threadid` = '%d'
			ORDER BY `posttime` DESC
			LIMIT %d",
			$this->threadid, Forums::NUMBER_LAST_POSTS_PREVIEW);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Posts!');
		}
		$this->topic->posts = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$this->topic->posts[] = $row;
		}
	}
	
	public function searchUserposts($user) {
		$query = sprintf("SELECT `postid`, UNIX_TIMESTAMP(`create_time`) AS `posttime`, `message`,
				`forums_threads`.`threadid`, `forums_threads`.`title`,
				`user`.`id` AS `user_id`, `user`.`handle` AS `user_handle`,
				`geonames_cache`.`fk_countrycode`
			FROM `forums_posts`
			LEFT JOIN `forums_threads` ON (`forums_posts`.`threadid` = `forums_threads`.`threadid`)
			LEFT JOIN `user` ON (`forums_posts`.`authorid` = `user`.`id`)
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `user`.`handle` = '%s' 
			ORDER BY `posttime` DESC",
			$this->dao->escape($user));
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Posts!');
		}
		$posts = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$posts[] = $row;
		}
		return $posts;
	}
	
	public function getTopic() {
		return $this->topic;
	}
	
	/**
	* Check if it's a topic or a forum
	* @return bool true on topic
	* @return bool false on forum
	*/
	public function isTopic() {
		return (bool) $this->threadid;
	}
	
	private $geonameid = 0;
	private $countrycode = 0;
	private $admincode;
	private $threadid = 0;
	private $tags = array();
	private $continent = false;
	private $page = 1;
	private $messageId = 0;
	public function setGeonameid($geonameid) {
		$this->geonameid = (int) $geonameid;
	}
	public function getGeonameid() {
		return $this->geonameid;
	}
	public function setCountryCode($countrycode) {
		$this->countrycode = $countrycode;
	}
	public function getCountryCode() {
		return $this->countrycode;
	}
	public function setAdminCode($admincode) {
		$this->admincode = $admincode;
	}
	public function getAdminCode() {
		return $this->admincode;
	}
	public function addTag($tagid) {
		$this->tags[] = (int) $tagid;
	}
	public function getTags() {
		return $this->tags;
	}
	public function setThreadId($threadid) {
		$this->threadid = (int) $threadid;
	}
	public function getThreadId() {
		return $this->threadid;
	}
	public function setContinent($continent) {
		$this->continent = $continent;
	}
	public function getContinent() {
		return $this->continent;
	}
	public function getPage() {
		return $this->page;
	}
	public function setPage($page) {
		$this->page = (int) $page;
	}
	public function setMessageId($messageid) {
		$this->messageId = (int) $messageid;
	}
	public function getMessageId() {
		return $this->messageId;
	}
	
	public function getTagsNamed() {
		$tags = array();
		if ($this->tags) {
			$query = sprintf("SELECT `tagid`, `tag` FROM `forums_tags` WHERE `tagid` IN (%s)", implode(',', $this->tags));
			$s = $this->dao->query($query);
			if (!$s) {
				throw new PException('Could not retrieve countries!');
			}
			while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$tags[$row->tagid] = $row->tag;
			}
			
		}
		return $tags;
	}
	
	public function getAllTags() {
		$tags = array();
		
		$query = "SELECT `tagid`, `tag` FROM `forums_tags` ORDER BY `tag` ASC";
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve countries!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$tags[$row->tagid] = $row->tag;
		}
		return $tags;
	}
	
	public function getTopLevelTags() {
		$tags = array();
		
		$query = "SELECT `tagid`, `tag`, `tag_description` FROM `forums_tags` WHERE `tag_position` < 250 ORDER BY `tag_position` ASC, `tag` ASC";
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve countries!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$tags[$row->tagid] = $row;
		}
		return $tags;	
	}
	
	private function cleanupText($txt) {
		$str = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$txt.'</body></html>'; 
		$doc = DOMDocument::loadHTML($str);
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
			$sanitize->allow('font');
			$sanitize->allow('strike');
			$sanitize->allow('br');
			$sanitize->allow('blockquote');
		
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
	
	public function suggestTags($search) {
		// Split words
		$words = explode(',', $search);
		$cleaned = array();
		// Clean up
		foreach ($words as $word) {
			$word = trim($word);
			if ($word) {
				$cleaned[] = $word;
			}
		}
		$words = $cleaned;

		// Which word is the person changing?
		$number_words = count($words);
		if ($number_words && isset($_SESSION['prev_tag_content']) && $_SESSION['prev_tag_content']) {
			$search_for = false;
			$pos = false;
			for ($i = 0; $i < $number_words; $i++) {
				if (isset($words[$i]) && (!isset($_SESSION['prev_tag_content'][$i]) || $words[$i] != $_SESSION['prev_tag_content'][$i])) {
					$search_for = $words[$i];
					$pos = $i;
				}
			}
			if (!$search_for) {
				return array();
			}
		} else if ($number_words) {
			$search_for = $words[count($words) - 1]; // last word
			$pos = false;
		} else {
			return array();
		}

		if ($search_for) {
	
			$_SESSION['prev_tag_content'] = $words;
		
			// look for possible matches (from ALL tags)
			$query = "SELECT `tag`
				FROM `forums_tags`
				WHERE `tag` LIKE '".$this->dao->escape($search_for)."%'
				ORDER BY `counter` DESC";
			$s = $this->dao->query($query);
			if (!$s) {
				throw new PException('Could not retrieve tag entries');
			}
			$tags = array();
			while ($row = $s->fetch(PDB::FETCH_OBJ)) {
				$tags[] = $row->tag;
			}
			
			if ($tags) {
				$out = array();
				$suggestion_number = 0;
				foreach ($tags as $w) {
					$out[$suggestion_number] = array();
					for ($i = 0; $i < count($words); $i++) {
						if ($i == $pos) {
							$out[$suggestion_number][] = $w;
						} else {
							$out[$suggestion_number][] .= $words[$i];
						}
					}
					$suggestion_number++;
				}
				return $out;
			}
		}
		return array();
	}


	public function getAllContinents() {
		return self::$continents;
	}
}


class Topic {
	public $topicinfo;
	public $posts = array();
}

class Board implements Iterator {
	public function __construct(&$dao, $boardname, $link, $navichain=false, $tags=false, $continent=false, $countrycode=false, $admincode=false, $geonameid=false) {
		$this->dao =& $dao;
	
		$this->boardname = $boardname;
		$this->link = $link;
		$this->continent = $continent;
		$this->countrycode = $countrycode;
		$this->admincode = $admincode;
		$this->geonameid = $geonameid;
		$this->navichain = $navichain;
		$this->tags = $tags;
	}
	
	private $dao;
	private $navichain;
	private $numberOfThreads;
	private $totalThreads;
	
	public function initThreads($page = 1) {
		
		$where = '';
		
		if ($this->continent) {
			$where .= sprintf("AND `forums_threads`.`continent` = '%s' ", $this->continent);
		}
		if ($this->countrycode) {
			$where .= sprintf("AND `countrycode` = '%s' ", $this->countrycode);
		}
		if ($this->admincode) {
			$where .= sprintf("AND `admincode` = '%s' ", $this->admincode);
		}
		if ($this->geonameid) {
			$where .= sprintf("AND `forums_threads`.`geonameid` = '%s' ", $this->geonameid);
		}
		if ($this->tags) {
			foreach ($this->tags as $tag) {
				$where .= sprintf("AND (`forums_threads`.`tag1` = '%1\$d' OR `forums_threads`.`tag2` = '%1\$d' OR `forums_threads`.`tag3` = '%1\$d' OR `forums_threads`.`tag4` = '%1\$d' OR `forums_threads`.`tag5` = '%1\$d') ", $tag);
			}
		}
		
		
		$query = sprintf("SELECT COUNT(*) AS `number` FROM `forums_threads` WHERE 1 %s", $where);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}
		$row = $s->fetch(PDB::FETCH_OBJ);
		$this->numberOfThreads = $row->number;
		
		$from = (Forums::THREADS_PER_PAGE * ($page - 1));
		
		$query = sprintf("SELECT SQL_CALC_FOUND_ROWS `forums_threads`.`threadid`, `forums_threads`.`title`, `forums_threads`.`replies`, `forums_threads`.`views`, `forums_threads`.`continent`,
				`first`.`postid` AS `first_postid`, `first`.`authorid` AS `first_authorid`, UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				`last`.`postid` AS `last_postid`, `last`.`authorid` AS `last_authorid`, UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				`first_user`.`handle` AS `first_author`,
				`last_user`.`handle` AS `last_author`,
				`geonames_cache`.`name` AS `geonames_name`, `geonames_cache`.`geonameid`,
				`geonames_admincodes`.`name` AS `adminname`, `geonames_admincodes`.`admin_code` AS `admincode`,
				`geonames_countries`.`name` AS `countryname`, `geonames_countries`.`iso_alpha2` AS `countrycode`,
				`tags1`.`tag` AS `tag1`, `tags1`.`tagid` AS `tag1id`,
				`tags2`.`tag` AS `tag2`, `tags2`.`tagid` AS `tag2id`,
				`tags3`.`tag` AS `tag3`, `tags3`.`tagid` AS `tag3id`,
				`tags4`.`tag` AS `tag4`, `tags4`.`tagid` AS `tag4id`,
				`tags5`.`tag` AS `tag5`, `tags5`.`tagid` AS `tag5id`
			FROM `forums_threads`
			LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)
			LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)
			LEFT JOIN `user` AS `first_user` ON (`first`.`authorid` = `first_user`.`id`)
			LEFT JOIN `user` AS `last_user` ON (`last`.`authorid` = `last_user`.`id`)
			LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)
			LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)
			LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)
			LEFT JOIN `forums_tags` AS `tags1` ON (`forums_threads`.`tag1` = `tags1`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags2` ON (`forums_threads`.`tag2` = `tags2`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags3` ON (`forums_threads`.`tag3` = `tags3`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags4` ON (`forums_threads`.`tag4` = `tags4`.`tagid`)
			LEFT JOIN `forums_tags` AS `tags5` ON (`forums_threads`.`tag5` = `tags5`.`tagid`)
			WHERE 1 %s
			ORDER BY `last_create_time` DESC
			LIMIT %d, %d
			", $where, $from, Forums::THREADS_PER_PAGE);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			if (isset($row->continent) && $row->continent) {
				$row->continentid = $row->continent;
				$row->continent = Forums::$continents[$row->continent];
			}
			$this->threads[] = $row;
		}
		
		$query = "SELECT FOUND_ROWS() AS `found_rows`";
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve number of rows!');
		}
		$row = $s->fetch(PDB::FETCH_OBJ);
		$this->totalThreads = $row->found_rows;
	}
	
	private $threads = array();
	public function getThreads() {
		return $this->threads;
	}
	

	private $continent;
	private $countrycode;
	private $admincode;
	private $geonameid;
	private $tags;

	private $boardname;
	public function getBoardName() {
		return $this->boardname;
	}
	
	private $link;
	public function getBoardLink() {
		return $this->link;
	}
	
	public function getNaviChain() {
		return $this->navichain;
	}
	
	public function getNumberOfThreads() {
		return $this->numberOfThreads;
	}
	
	public function getTotalThreads() {
		return $this->totalThreads;
	}
	
	private $subboards = array();
	
	// Add a subboard
	public function add(Board $board) {
		$this->subboards[] = $board;
	}
	
	public function hasSubBoards() {
		return (bool)(count($this->subboards) > 0);
	}
	
	public function rewind() {
		reset($this->subboards);
	}
	
	public function current() {
		$var = current($this->subboards);
		return $var;
	}
	
	public function key() {
		$var = key($this->subboards);
		return $var;
	}
	
	public function next() {
		$var = next($this->subboards);
		return $var;
	}
	
	public function valid() {
		$var = $this->current() !== false;
		return $var;
	}

}


?>