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
class Trip extends RoxModelBase
{
    public function __construct()
    {
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

    /**
     * fetches a geo identity, by geoname_id
     *
     * @param int $geonameid
     * @access public
     * @return object|false
     */
    public function getBlogGeo($geonameid)
    {
        return $this->createEntity('Geo')->findById($geonameid);
    }
    
    public function createTrip($vars, Member $user)
    {
        if (!is_array($vars) || !$user->isLoaded())
        {
            $vars['errors'][] = 'not_created';
            return false;
        }
        $tripId = $this->insertTrip($vars['n'], $vars['d'], $user->id);
        if (!$tripId)
        {
            $vars['errors'][] = 'not_created';
            return false;
        }
        if (isset($vars['cg']) && $vars['cg'])
        {
            $Gallery = new GalleryModel;
            $galleryId = $Gallery->createGallery($vars['n']);
            if (!$galleryId)
            {
                $vars['errors'][] = 'gallery_not_created';
            }
            else
            {
                $this->assignGallery($tripId, $galleryId);
            }
        }
        return $tripId;
    }
    
    /**
     * Intended to replace old functions:
     * tripropdown($userID)
     */
    public function getTripsForUser($userId)
    {
        $query = <<<SQL
SELECT 
    t.trip_id,
    d.trip_name 
FROM trip AS t
LEFT JOIN trip_data AS d ON d.trip_id = t.trip_id
WHERE t.IdMember = '{$this->dao->escape($userId)}'
ORDER BY trip_touched DESC
SQL;
        return $this->bulkLookup($query);
    }
    
    public function insertTrip($name, $description, $userId)
    {
        if (!intval($userId))
        {
            return false;
        }
        $s = $this->dao->prepare('
INSERT INTO `trip`
(`trip_id`, `trip_options`, `trip_touched`, IdMember)
VALUES
(?, 0, NOW(), ?)
        ');
        $s->prepare("
INSERT INTO `trip_data` (`trip_id`, `trip_name`, `trip_text`, `trip_descr`) VALUES (?, ?, '', ?);
");
        $s->setCursor(0);
        $s->execute(array(0=>$this->dao->nextId('trip'), 1=>$userId));
        if (!$tripId = $s->insertId())
          return false;
        $s->setCursor(1);
        $s->execute(array(0=>$tripId, 1=>$name, 2=>$description)); 
        return $tripId;
    }
    
    private $tripids;
	public function getTrips($handle = false)
    {
		$query = <<<SQL
SELECT trip.trip_id, trip_data.trip_name, trip_text, trip_descr, members.Username AS handle, geonames_cache.fk_countrycode, trip_to_gallery.gallery_id_foreign 
    FROM trip
    RIGHT JOIN trip_data ON trip.trip_id = trip_data.trip_id
    LEFT JOIN members ON members.id = trip.IdMember
    LEFT JOIN addresses ON addresses.IdMember = members.id
    LEFT JOIN geonames_cache ON addresses.IdCity = geonames_cache.geonameid
    LEFT JOIN trip_to_gallery ON trip_to_gallery.trip_id_foreign = trip.trip_id
SQL;
		if ($handle) {
			$query .= "    WHERE members.Username = '{$this->dao->escape($handle)}'";
		}
        $query .= " ORDER BY `trip_touched` DESC";
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

	public function getTripData()
    {
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
	        if ($member = $this->getLoggedInMember()) {
	        	$query .= '
	        		OR (`flags` & '.(int)Blog::FLAG_VIEW_PRIVATE.' AND blog.IdMember = '.(int)$member->id.')
	        		OR (`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' AND blog.IdMember = '.(int)$member->id.')
                    ';
                    /* pending deletion
	        		OR (
	            		`flags` & '.(int)Blog::FLAG_VIEW_PROTECTED.' 
	            		AND
	            		(SELECT COUNT(*) FROM `user_friends` WHERE `user_id_foreign` = blog.`user_id_foreign` AND `user_id_foreign_friend` = '.(int)$User->getId().')
	        		)';
                    */
	        }
        	$query .= ") ORDER BY `blog_start` ASC, `name` ASC";
			
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
	
	public function getTrip($tripid)
    {
		$this->tripids = array($tripid);
        $query = <<<SQL
        SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, members.Username AS handle, members.id AS IdMember, members.id AS user_id_foreign, `trip_to_gallery`.`gallery_id_foreign`
			FROM `trip`
			RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
			LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)
			LEFT JOIN members ON (members.id = trip.IdMember)
			WHERE `trip`.`trip_id` = '{$this->dao->escape($tripid)}'
SQL;
		$result = $this->dao->query($query);
		if (!$result)
        {
			throw new PException('Could not retrieve trips.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}
	
	public function reorderTripItems($items)
    {
		if (!$this->checkTripItemOwnerShip($items)) {
			return;
		}
		
		$this->dao->query("START TRANSACTION");
		foreach ($items as $position => $item)
        {
			$query = sprintf("UPDATE `blog_data` SET `blog_display_order` = '%d' WHERE `blog_id` = '%d'", ($position + 1), $item);
			$this->dao->query($query);
		}
		$this->dao->query("COMMIT");
	}
	
	private function checkTripItemOwnerShip($items)
    {
        if (!$member = $this->getLoggedInMember() || !is_array($items))
        {
            return false;
        }
		// Get the blog entries matching the items in the request
        $i = array();
        foreach ($items as $it)
        {
            $i[] = $this->dao->escape($it);
        }
        $items = implode("','", $i);
		$query = "SELECT blog_id, IdMember FROM blog WHERE blog_id IN ('{$items}')";
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve blogs to check.');
		}
		$entries = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ))
        {
			$entries[$row->blog_id] = $row->IdMember;
		}
		
		foreach ($entries as $entry)
        {
			if ($entry != $member->id)
            {
				return false;
			}
		}
		return true;
	}
	
    // todo: refactor call to getloggedinmember
    // todo: refactor call to getVars
	public function prepareEditData($tripId, $callbackId)
    {
		if (!$member = $this->getLoggedInMember()) {
			throw new PException('Permission denied, Login required');
		}
        $trip_id = $this->dao->escape($tripId);

		$query = <<<SQL
SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, IdMember AS `user_id_foreign`, IdMember, `trip_to_gallery`.`gallery_id_foreign` 
    FROM `trip`
    RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
    LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)
    WHERE `trip`.`trip_id` = '{$trip_id}' AND IdMember = '{$member->id}'
SQL;
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trip (Access Error?).');
		}
		$trip = $result->fetch(PDB::FETCH_OBJ);
		
		$vars =& PPostHandler::getVars($callbackId);
		$vars['trip_id'] = $trip->trip_id;
		$vars['n'] = $trip->trip_name;
		$vars['trip_text'] = $trip->trip_text;
		$vars['d'] = $trip->trip_descr;
        $vars['gallery'] = $trip->gallery_id_foreign;
	}

    // todo: refactor call to getVars
	public function editProcess($callbackId)
    {
		$vars =& PPostHandler::getVars($callbackId);

		if ($this->checkTripOwnership($vars['trip_id'])) {
		
			// Update the Tripdata
	        $query = <<<SQL
UPDATE `trip_data`
SET
    `trip_name` = '{$this->dao->escape($vars['n'])}',
    `trip_descr` = '{$this->dao->escape($vars['d'])}',
    `edited` = NOW()
WHERE `trip_id` = '{$this->dao->escape($vars['trip_id'])}'
SQL;

			$this->dao->query($query);
            
            if (isset($vars['cg']) && $vars['cg']) {
                $Gallery = new GalleryModel;
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
	
    // todo: refactor call to getloggedinmember
	private function checkTripOwnership($tripid)
    {
		// Check the ownership of the trip - better safe than sorry
        
		if (!$member = $this->getLoggedInMember())
        {
			return false;
		}
	
		$query = <<<SQL
SELECT trip_id
FROM trip
WHERE trip_id = '{$this->dao->escape($tripid)}' AND IdMember = '{$member->id}'
SQL;
		$result = $this->dao->query($query);
		if (!$result)
        {
			return false;
		}
		$row = $result->fetch(PDB::FETCH_OBJ);
		return ($row->trip_id > 0);
	}

    // todo: refactor call to getVars
	public function delProcess($callbackId)
    {
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
    
	public function getTripsDataForLocation($search)
    {
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
    
	public function getTripsForLocation()
    {
		$query = <<<SQL
SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, members.Username AS handle, `geonames_cache`.`fk_countrycode`, `trip_to_gallery`.`gallery_id_foreign` 
    FROM `trip`
    RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
    LEFT JOIN members ON members.id = trip.IdMember
    LEFT JOIN addresses ON addresses.IdMember = members.id
    LEFT JOIN geonames_cache ON addresses.IdCity = geonames_cache.geonameid
    LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)
    ORDER BY trip_touched DESC
SQL;
		return $this->bulkLookup($query);
	}
    
    public function touchTrip($tripId)
    {
        if (!isset($tripId) || !$tripId) return false; 
        // insert into db
        $query = <<<SQL
UPDATE `trip`
SET
    `trip_touched` = NOW()
WHERE `trip_id` = '{$this->dao->escape($tripId)}'
SQL;
        return $this->dao->exec($query);
    }
}
