<?php
/**
* Forums model
* 
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class Forums extends PAppModel {
	
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
		$this->board = new Board($this->dao, 'Forums', '.');
		foreach (Forums::$continents as $code => $name) {
			$this->board->add(new Board($this->dao, $name, 'k'.$code.'-'.$name));
		}
	}
	
	private function boardContinent() {
		if (!isset(Forums::$continents[$this->continent]) || !Forums::$continents[$this->continent]) {
			throw new PException('Invalid Continent');
		}
		
		$subboards = array('a' => 'Forums');
		
		$this->board = new Board($this->dao, Forums::$continents[$this->continent], '.', $subboards, $this->continent);
		
		$query = sprintf("SELECT `iso_alpha2`, `name` 
			FROM `geonames_countries` 
			WHERE `continent` = '%s'
			ORDER BY `name` ASC",
			$this->continent);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve countries!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$this->board->add(new Board($this->dao, $row->name, 'c'.$row->iso_alpha2.'-'.$row->name));
		}
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
		
		$navichain = array('a' => 'Forums', 
			'b' => Forums::$continents[$this->continent],
			'c' => $countrycode->name);
	
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

		$this->board = new Board($this->dao, $admincode->name, '.', $navichain, $this->continent, $this->countrycode, $this->admincode);
		
		$query = sprintf("SELECT `geonameid`, `name` 
			FROM `geonames_cache` 
			WHERE `fk_countrycode` = '%s' AND `fk_admincode` = '%s'
			ORDER BY `population` DESC
			LIMIT 100",
			$this->countrycode, $this->admincode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Districts!');
		}
		$locations = array();
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$locations[$row->geonameid] = $row->name;
		}
		natcasesort($locations);
		foreach ($locations as $geonameid => $name) {
			$this->board->add(new Board($this->dao, $name, 'g'.$geonameid.'-'.$name));
		}
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
		
		$navichain = array('a' => 'Forums', 
			'b' => Forums::$continents[$this->continent]);
		
		$this->board = new Board($this->dao, $countrycode->name, '.', $navichain, $this->continent, $this->countrycode);
		
		$query = sprintf("SELECT `admin_code`, `name` 
			FROM `geonames_admincodes` 
			WHERE `country_code` = '%s'
			ORDER BY `name` ASC",
			$this->countrycode);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Districts!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			$this->board->add(new Board($this->dao, $row->name, 'a'.$row->admin_code.'-'.$row->name));
		}
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
		
				
		$navichain = array('a' => 'Forums', 
			'b' => Forums::$continents[$this->continent],
			'c' => $countrycode->name,
			'd' => $admincode->name);
		
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
		
		$this->board = new Board($this->dao, $geonameid->name, '.', $navichain, $this->continent, $this->countrycode, $this->admincode, $this->geonameid);
	}
	
	/**
	* Fetch all required data for the view to display a forum
	*/
	public function prepareForum() {
		if (!$this->geonameid && !$this->countrycode && !$this->tags && !$this->continent) { 
			$this->boardTopLevel();
		} else if ($this->continent && !$this->geonameid && !$this->countrycode && !$this->tags) { 
			$this->boardContinent();
		} else if (isset($this->admincode) && $this->admincode && $this->continent && $this->countrycode && !$this->geonameid && !$this->tags) { 
			$this->boardadminCode();
		} else if ($this->continent && $this->countrycode && !$this->geonameid && !$this->tags) {
			$this->boardCountry();
		} else if ($this->continent && $this->countrycode && $this->geonameid && isset($this->admincode) && $this->admincode && !$this->tags) { 
			$this->boardLocation();
		} else {
			throw new PException('Invalid Request');
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
		
		return $threadid;
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
		
		$this->dao->query("COMMIT");
		
		return $threadid;
	}
	
	private $topic;
	public function prepareTopic() {
		$this->topic = new Topic();
		
		// Topic Data
		$query = sprintf("SELECT `forums_threads`.`title`, `forums_threads`.`replies`, `forums_threads`.`views`, `forums_threads`.`continent`,
				`geonames_cache`.`name` AS `geonames_name`,
				`geonames_admincodes`.`name` AS `adminname`,
				`geonames_countries`.`name` AS `countryname`
			FROM `forums_threads`
			LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)
			LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)
			LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)
			WHERE `threadid` = '%d'
			",
			$this->threadid);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Posts!');
		}
		$topicinfo = $s->fetch(PDB::FETCH_OBJ);
		$this->topic->topicinfo = $topicinfo;
		
		// Posts
		$query = sprintf("SELECT `postid`, UNIX_TIMESTAMP(`create_time`) AS `posttime`, `message`,
				`user`.`id` AS `user_id`, `user`.`handle` AS `user_handle`,
				`geonames_cache`.`fk_countrycode`
			FROM `forums_posts`
			LEFT JOIN `user` ON (`forums_posts`.`authorid` = `user`.`id`)
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `threadid` = '%d'",
			$this->threadid);
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
	public function setGeonameid($geonameid) {
		$this->geonameid = (int) $geonameid;
	}
	public function setCountryCode($countrycode) {
		$this->countrycode = $countrycode;
	}
	public function setAdminCode($admincode) {
		$this->admincode = $admincode;
	}
	public function addTag($tagid) {
		$this->tags[] = (int) $tagid;
	}
	public function setThreadId($threadid) {
		$this->threadid = $threadid;
	}
	public function setContinent($continent) {
		$this->continent = $continent;
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
}


class Topic {
	public $topicinfo;
	public $posts = array();
}

class Board implements Iterator {
	public function __construct(&$dao, $boardname, $link, $navichain=false, $continent=false, $countrycode=false, $admincode=false, $geonameid=false) {
		$this->dao =& $dao;
	
		$this->boardname = $boardname;
		$this->link = $link;
		$this->continent = $continent;
		$this->countrycode = $countrycode;
		$this->admincode = $admincode;
		$this->geonameid = $geonameid;
		$this->navichain = $navichain;
		
		$this->initThreads();
	}
	
	private $dao;
	private $navichain;
	
	private function initThreads() {
		
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
		
		
		
		$query = sprintf("SELECT `forums_threads`.`threadid`, `forums_threads`.`title`, `forums_threads`.`replies`, `forums_threads`.`views`, `forums_threads`.`continent`,
				`first`.`postid` AS `first_postid`, `first`.`authorid` AS `first_authorid`, UNIX_TIMESTAMP(`first`.`create_time`) AS `first_create_time`,
				`last`.`postid` AS `last_postid`, `last`.`authorid` AS `last_authorid`, UNIX_TIMESTAMP(`last`.`create_time`) AS `last_create_time`,
				`first_user`.`handle` AS `first_author`,
				`last_user`.`handle` AS `last_author`,
				`geonames_cache`.`name` AS `geonames_name`,
				`geonames_admincodes`.`name` AS `adminname`,
				`geonames_countries`.`name` AS `countryname`
			FROM `forums_threads`
			LEFT JOIN `forums_posts` AS `first` ON (`forums_threads`.`first_postid` = `first`.`postid`)
			LEFT JOIN `forums_posts` AS `last` ON (`forums_threads`.`last_postid` = `last`.`postid`)
			LEFT JOIN `user` AS `first_user` ON (`first`.`authorid` = `first_user`.`id`)
			LEFT JOIN `user` AS `last_user` ON (`last`.`authorid` = `last_user`.`id`)
			LEFT JOIN `geonames_cache` ON (`forums_threads`.`geonameid` = `geonames_cache`.`geonameid`)
			LEFT JOIN `geonames_admincodes` ON (`forums_threads`.`admincode` = `geonames_admincodes`.`admin_code` AND `forums_threads`.`countrycode` = `geonames_admincodes`.`country_code`)
			LEFT JOIN `geonames_countries` ON (`forums_threads`.`countrycode` = `geonames_countries`.`iso_alpha2`)
			WHERE 1 %s
			ORDER BY `last_create_time` DESC
			", $where);
		$s = $this->dao->query($query);
		if (!$s) {
			throw new PException('Could not retrieve Threads!');
		}
		while ($row = $s->fetch(PDB::FETCH_OBJ)) {
			if (isset($row->continent) && $row->continent) {
				$row->continent = Forums::$continents[$row->continent];
			}
			$this->threads[] = $row;
		}
	}
	
	private $threads = array();
	public function getThreads() {
		return $this->threads;
	}
	

	private $continent;
	private $countrycode;
	private $admincode;
	private $geonameid;

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