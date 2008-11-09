<?php
/**
 * trip model
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.model.php 233 2007-02-28 13:37:19Z marco $
 */
class Trip extends PAppModel {
    protected $dao;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function assignGallery($tripId, $galleryId)
    {
    	$query = '
DELETE FROM `trip_to_gallery` WHERE
`trip_id_foreign` = '.(int)$tripId
        ;
        $this->dao->exec($query);
        $query = '
INSERT INTO `trip_to_gallery` (`trip_id_foreign`, `gallery_id_foreign`) VALUES
('.(int)$tripId.', '.(int)$galleryId.')
        ';
        $s = $this->dao->query($query);
        return ($s->affectedRows() != -1);
    }
    
    public function createProcess()
    {
        $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
    	if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $vars =& PPostHandler::getVars();
            $errors = array();
            if (!isset($vars['n']) || !$vars['n'])
                $errors[] = 'name';
            $vars['errors'] = $errors;
            if (count($errors) > 0)
              return false;

            $tripId = $this->insertTrip($vars['n'], $vars['d'], (int)$User->getId());
            if (!$tripId) {
            	$vars['errors'][] = 'not_created';
                return false;
            }
            if (isset($vars['cg']) && $vars['cg']) {
                $Gallery = new Gallery;
                $galleryId = $Gallery->createGallery($vars['n']);
                if (!$galleryId) {
                    $vars['errors'][] = 'gallery_not_created';
                } else {
                	$this->assignGallery($tripId, $galleryId);
                }
            }
    		return PVars::getObj('env')->baseuri.'trip/'.$tripId;
    	} else {
    		PPostHandler::setCallback($callbackId, __CLASS__, __FUNCTION__);
            return $callbackId;
    	}
    }
    
    /**
     * Intended to replace old functions:
     * tripropdown($userID)
     */
    public function getTripsForUser($userId) {
        $s = $this->dao->prepare('
SELECT 
    t.`trip_id`,
    d.`trip_name` 
FROM `trip` AS t
LEFT JOIN `trip_data` AS d ON
    d.`trip_id` = t.`trip_id`
WHERE t.`user_id_foreign` = ?
ORDER BY `trip_touched` DESC
        ');
        $s->execute($userId);
        if ($s->numRows() == 0)
            return false;
        return $s;
    }
    
    public function insertTrip($name, $description, $userId) {
        $s = $this->dao->prepare('
INSERT INTO `trip`
(`trip_id`, `trip_options`, `trip_touched`, `user_id_foreign`)
VALUES
(?, 0, NOW(), ?)
        ');
        $s->prepare('
INSERT INTO `trip_data` (`trip_id`, `trip_name`, `trip_text`, `trip_descr`) VALUES (?, ?, \'\', ?);
');
        $s->setCursor(0);
        $s->execute(array(0=>$this->dao->nextId('trip'), 1=>$userId));
        if (!$tripId = $s->insertId())
          return false;
        $s->setCursor(1);
        $s->execute(array(0=>$tripId, 1=>$name, 2=>$description)); 
        return $tripId;
    }
    
    private $tripids;
	public function getTrips($handle = false) {
		$query = "SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, `user`.`handle`, `geonames_cache`.`fk_countrycode`, `trip_to_gallery`.`gallery_id_foreign` 
			FROM `trip`
			RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
			LEFT JOIN `user` ON (`user`.`id` = `trip`.`user_id_foreign`)
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)";
		if ($handle) {
			$query .= sprintf("WHERE `user`.`handle` = '%s'", $handle);
		}
			$query .= "ORDER BY `trip_touched` DESC";
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}
		$trips = array();
		$this->tripids = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$trips[] = $row;
			$this->tripids[] = $row->trip_id;
		}
        return $result;
	}

	public function getTripData() {
		if (!$this->tripids) {
            return array();
		}
		
		$query = sprintf("SELECT `blog`.`trip_id_foreign`, `blog`.`blog_id`, 
				`blog_title`, `blog_text`, DATE(`blog_start`) AS `blog_start`, `blog_geonameid`, 
				`geonames_cache`.`name`, `geonames_cache`.`latitude`, `geonames_cache`.`longitude`
			FROM `blog`
			LEFT JOIN `blog_data` ON (`blog`.`blog_id` = `blog_data`.`blog_id`)
			LEFT JOIN `geonames_cache` ON (`blog_data`.`blog_geonameid` = `geonames_cache`.`geonameid`)
			WHERE `blog`.`trip_id_foreign` IN (%s)",
			implode(',', $this->tripids));
			
			// Copied from blog.model
			$query .= "AND 
				(
        			(
			            `flags` & ".(int)Blog::FLAG_VIEW_PRIVATE." = 0 
			            AND `flags` & ".(int)Blog::FLAG_VIEW_PROTECTED." = 0
        			)
	        ";
	        if ($User = APP_User::login()) {
	        	$query .= '
	        		OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND blog.`user_id_foreign` = '.(int)$User->getId().')
	        		OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND blog.`user_id_foreign` = '.(int)$User->getId().')
	        		OR (
	            		`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
	            		AND
	            		(SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = blog.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$User->getId().')
	        		)';
	        }
        	$query .= ")";
			
			$query .= 'ORDER BY `blog_display_order` ASC, `blog_start` ASC, `name` ASC';
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve tripdata.');
		}
		$trip_data = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$trip_data[$row->trip_id_foreign][$row->blog_id] = $row;
		}
		return $trip_data;
	}
	
	public function getTrip($tripid) {
		$this->tripids = array($tripid);
		$query = sprintf("SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, `user`.`handle`, `user_id_foreign`, `trip_to_gallery`.`gallery_id_foreign`
			FROM `trip`
			RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
			LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)
			LEFT JOIN `user` ON (`user`.`id` = `trip`.`user_id_foreign`)
			WHERE `trip`.`trip_id` = '%d'",
			$tripid);
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}
		$trip = $row = $result->fetch(PDB::FETCH_OBJ);
		return $trip;
	}
	
	public function reorderTripItems($items) {
		if (!$this->checkTripItemOwnerShip($items)) {
			return;
		}
		
		$this->dao->query("START TRANSACTION");
		foreach ($items as $position => $item) {
			$query = sprintf("UPDATE `blog_data` SET `blog_display_order` = '%d' WHERE `blog_id` = '%d'", ($position + 1), $item);
			$this->dao->query($query);
		}
		$this->dao->query("COMMIT");
	}
	
	private function checkTripItemOwnerShip($items) {
		// Get the blog entries matching the items in the request
		$query = sprintf("SELECT `blog_id`, `user_id_foreign` FROM `blog` WHERE `blog_id` IN (%s)", implode(',', $items));
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve blogs to check.');
		}
		$entries = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$entries[$row->blog_id] = $row->user_id_foreign;
		}
		
		// Check if they really all belong to the user
		$User = APP_User::login();
		if (!$User) {
			return false;
		}
		$userid = $User->getId();
		foreach ($entries as $entry) {
			if ($entry != $userid) {
				return false;
			}
		}
		return true;
	}
	
	public function prepareEditData($tripId, $callbackId) {
		$User = APP_User::login();
		if (!$User) {
			throw new PException('Permission denied, Login required');
		}
		$userid = $User->getId();
	
		$query = sprintf("SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, `user_id_foreign`, `trip_to_gallery`.`gallery_id_foreign` 
			FROM `trip`
			RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
			LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)
			WHERE `trip`.`trip_id` = '%d' AND `user_id_foreign` = '%d'",
			$tripId, $userid);
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trip (Access Error?).');
		}
		$trip = $row = $result->fetch(PDB::FETCH_OBJ);
		
		$vars =& PPostHandler::getVars($callbackId);
		$vars['trip_id'] = $trip->trip_id;
		$vars['n'] = $trip->trip_name;
		$vars['trip_text'] = $trip->trip_text;
		$vars['d'] = $trip->trip_descr;
        $vars['gallery'] = $trip->gallery_id_foreign;
	}
	
	public function editProcess($callbackId) {
		$vars =& PPostHandler::getVars($callbackId);

		if ($this->checkTripOwnership($vars['trip_id'])) {
		
			// Update the Tripdata
	        $query = sprintf("UPDATE `trip_data` SET `trip_name` = '".$this->dao->escape($vars['n'])."', `trip_descr` = '".$this->dao->escape($vars['d'])."', `edited` = NOW() WHERE `trip_id` = '%d'",
				$vars['trip_id']);
			$this->dao->query($query);
            
            if (isset($vars['cg']) && $vars['cg']) {
                $Gallery = new Gallery;
                $galleryId = $Gallery->createGallery($vars['n']);
                if (!$galleryId) {
                    $vars['errors'][] = 'gallery_not_created';
                } else {
                	$this->assignGallery($vars['trip_id'], $galleryId);
                }
            } elseif (isset($vars['gallery']) && $vars['gallery']) {
                $this->assignGallery($vars['trip_id'], $vars['gallery']);
            }
			
			return PVars::getObj('env')->baseuri.'trip/'.$vars['trip_id'];
		}
	
	}
	
	private function checkTripOwnership($tripid) {
		// Check the ownership of the trip - better safe than sorry
		$User = APP_User::login();
		if (!$User) {
			return false;
		}
		$userid = $User->getId();
	
		$query = sprintf("SELECT COUNT(*) AS `num`
			FROM `trip`
			WHERE `trip_id` = '%d' AND `user_id_foreign` = '%d'",
			$tripid, $userid);
		$result = $this->dao->query($query);
		if (!$result) {
			return false;
		}
		$row = $result->fetch(PDB::FETCH_OBJ);
		return ($row->num > 0);
	}
	
	public function delProcess($callbackId) {
		$vars =& PPostHandler::getVars($callbackId);
		if ($this->checkTripOwnership($vars['trip_id'])) {
			$this->dao->query('START TRANSACTION');
			
			// Update all blog entries and remove the trip-foreign key
	        $query = sprintf("UPDATE `blog` SET `trip_id_foreign` = NULL WHERE `trip_id_foreign` = '%d'",
				$vars['trip_id']);
			$this->dao->query($query);
			
			// Delete the trip data
	        $query = sprintf("DELETE FROM `trip_data` WHERE `trip_id` = '%d' LIMIT 1",
				$vars['trip_id']);
			$this->dao->query($query);
			
			// Delete the trip
	        $query = sprintf("DELETE FROM `trip` WHERE `trip_id` = '%d' LIMIT 1",
				$vars['trip_id']);
			$this->dao->query($query);
			
			$this->dao->query('COMMIT');
			
			return PVars::getObj('env')->baseuri.'trip';
		}
	}
    
	public function getTripsDataForLocation($search) {
		
        //TODO: Fix OR-part of query
		$query = sprintf("SELECT `blog`.`trip_id_foreign`, `blog`.`blog_id`, 
				`blog_title`, `blog_text`, DATE(`blog_start`) AS `blog_start`, `blog_geonameid`, 
				`geonames_cache`.`name`, `geonames_cache`.`latitude`, `geonames_cache`.`longitude`
			FROM `blog`
			LEFT JOIN `blog_data` ON (`blog`.`blog_id` = `blog_data`.`blog_id`)
			LEFT JOIN `geonames_cache` ON (`blog_data`.`blog_geonameid` = `geonames_cache`.`geonameid`)
			WHERE `geonames_cache`.`name` LIKE '%s'
            OR `blog_title` LIKE '%s'
            OR `blog_text` LIKE '%s'",
			$this->dao->escape($search),$this->dao->escape($search),$this->dao->escape($search));
        
        $query .= "ORDER BY `trip_id_foreign` DESC";
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}
		$this->tripids = array();
		$trip_data = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$this->tripids[] = $row->trip_id_foreign;
			$trip_data[$row->trip_id_foreign][$row->blog_id] = $row;
		}
		return $trip_data;
	}
    
	public function getTripsForLocation() {
		$query = "SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, `user`.`handle`, `geonames_cache`.`fk_countrycode`, `trip_to_gallery`.`gallery_id_foreign` 
			FROM `trip`
			RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
			LEFT JOIN `user` ON (`user`.`id` = `trip`.`user_id_foreign`)
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)";
        $query .= "ORDER BY `trip_touched` DESC";
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}
		$trips = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            if (in_array($row->trip_id,$this->tripids)) {
    			$trips[] = $row;
            }
		}
		return $trips;
	}
    
    public function touchTrip($tripId)
    {
        if (!isset($tripId) || !$tripId) return false; 
        // insert into db
        $query = '
UPDATE `trip`
SET
    `trip_touched` = NOW()
WHERE `trip_id` = '.(int)$tripId.'
';
        return $this->dao->exec($query);
    }
}
?>
