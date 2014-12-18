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
class TripsModel extends RoxModelBase
{
	const TRIPS_TYPE_PAST = 1;
	const TRIPS_TYPE_UPCOMING = 2;
	const TRIPS_TYPE_ALL = 3;

	public function __construct()
	{
		parent::__construct();
	}

	public function checkCreateEditVars($vars)
	{
		$errors = array();
		if (empty($vars['trip-name'])) {
			$errors[] = 'TripErrorNameEmpty';
		}
		if (empty($vars['trip-desc'])) {
			$errors[] = 'TripErrorDescEmpty';
		}

		return $errors;
	}

	public function createTrip($vars, Member $user)
	{
		$errors = array();
		if (!is_array($vars) || !$user->isLoaded()) {
			$errors[] = 'TripErrorNotCreated';
			return false;
		}
		$tripId = $this->insertTrip($vars['trip-name'], $vars['trip-desc'], $user->id);
		if (!$tripId) {
			$errors[] = 'TripErrorNotCreated';
			return false;
		}
		if (isset($vars['cg']) && $vars['cg']) {
			$Gallery = new GalleryModel;
			$galleryId = $Gallery->createGallery($vars['n']);
			if (!$galleryId) {
				$errors[] = 'TripErrorGalleryNotCreated';
			} else {
				$this->assignGallery($tripId, $galleryId);
			}
		}
		return $errors;
	}

	public function assignGallery($tripId, $galleryId)
	{
		$query = '
DELETE FROM `trip_to_gallery` WHERE
`trip_id_foreign` = ' . (int)$tripId;
		$this->dao->exec($query);
		$query = '
INSERT INTO `trip_to_gallery` (`trip_id_foreign`, `gallery_id_foreign`) VALUES
(' . (int)$tripId . ', ' . (int)$galleryId . ')
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

	public function insertTrip($name, $description, $userId)
	{
		if (!intval($userId)) {
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
		$s->execute(array(0 => $this->dao->nextId('trip'), 1 => $userId));
		if (!$tripId = $s->insertId())
			return false;
		$s->setCursor(1);
		$s->execute(array(0 => $tripId, 1 => $name, 2 => $description));
		return $tripId;
	}

	private $tripids;

	public function getTripsCount($handle = false)
	{
		$query = "
		SELECT count(*) cnt
		FROM trip
		RIGHT JOIN trip_data ON trip.trip_id = trip_data.trip_id
		LEFT JOIN members ON members.id = trip.IdMember AND members.Status IN (" . Member::ACTIVE_ALL . ")
		WHERE NOT members.Username IS NULL
";
		if ($handle) {
			$query .= " AND members.Username = '{$this->dao->escape($handle)}'";
		}
		$query .= "
		AND (
		(
			`trip_options` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0
			AND `trip_options` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
		)
		    ";
		$member = $this->getLoggedInMember();
		if ($member) {
			$query .= '
	        		OR (`trip_options` & ' . (int)Blog::FLAG_VIEW_PRIVATE . ' AND trip.IdMember = ' . (int)$member->id . ')
	        		OR (`trip_options` & ' . (int)Blog::FLAG_VIEW_PROTECTED . ' AND trip.IdMember = ' . (int)$member->id . ')
                    ';
		}
		$query .= ")";
		$result = $this->singleLookup($query);
		if (!$result) {
			throw new PException('Could not retrieve count for trips.');
		}
		return $result->cnt;
	}

	public function getTrips($handle = false, $page_no = 1, $items = 5)
	{
		$low = ($page_no - 1) * $items;
		$query = "
SELECT trip.trip_id, trip_data.trip_name, trip_text, trip_descr, members.Username AS handle, geonames_cache.fk_countrycode, trip_to_gallery.gallery_id_foreign
    FROM trip
    RIGHT JOIN trip_data ON trip.trip_id = trip_data.trip_id
    LEFT JOIN members ON members.id = trip.IdMember AND members.Status IN (" . Member::ACTIVE_ALL . ")
    LEFT JOIN addresses ON addresses.IdMember = members.id
    LEFT JOIN geonames_cache ON addresses.IdCity = geonames_cache.geonameid
    LEFT JOIN trip_to_gallery ON trip_to_gallery.trip_id_foreign = trip.trip_id
WHERE NOT members.Username IS NULL
";
		if ($handle) {
			$query .= " AND members.Username = '{$this->dao->escape($handle)}'";
		}
		$query .= "
            AND (
            (
                `trip_options` & '.(int)Blog::FLAG_VIEW_PRIVATE.' = 0
                AND `trip_options` & '.(int)Blog::FLAG_VIEW_PROTECTED.' = 0
            )
		    ";
		$member = $this->getLoggedInMember();
		if ($member) {
			$query .= '
	        		OR (`trip_options` & ' . (int)Blog::FLAG_VIEW_PRIVATE . ' AND trip.IdMember = ' . (int)$member->id . ')
	        		OR (`trip_options` & ' . (int)Blog::FLAG_VIEW_PROTECTED . ' AND trip.IdMember = ' . (int)$member->id . ')
                    ';
		}
		$query .= ") ORDER BY `trip_touched` DESC
        		LIMIT " . $low . ", " . $items;
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
			            `flags` & " . (int)Blog::FLAG_VIEW_PRIVATE . " = 0
			            AND `flags` & " . (int)Blog::FLAG_VIEW_PROTECTED . " = 0
        			)
	        ";
		if ($member = $this->getLoggedInMember()) {
			$query .= '
	        		OR (`flags` & ' . (int)Blog::FLAG_VIEW_PRIVATE . ' AND blog.IdMember = ' . (int)$member->id . ')
	        		OR (`flags` & ' . (int)Blog::FLAG_VIEW_PROTECTED . ' AND blog.IdMember = ' . (int)$member->id . ')
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

	private function _getAllTrips($member = false, $type = self::TRIPS_TYPE_ALL, $limit = false)
	{
		if (!$limit) {
			$limit = PVars::getObj('activities')->max_activities_on_map;
		}
		switch ($type) {
			case self::TRIPS_TYPE_PAST:
				$typeQuery = "	HAVING (tripEndDate IS NOT NULL) AND (tripEndDate <= NOW())";
				break;
			case self::TRIPS_TYPE_UPCOMING:
				$typeQuery = "	HAVING (tripStartDate IS NOT NULL) AND (tripStartDate >= NOW())";
				break;
			default:
				$typeQuery = "";
		};
		$memberQuery = " t.IdMember = m.id ";
		if ($member) {
			$memberQuery .= "AND m.id = " . $member->id;
		}

		$query = "
			SELECT
				t.*,
				td.*,
				b.*,
				UNIX_TIMESTAMP(bd.blog_start) AS tripStartDate,
				UNIX_TIMESTAMP(bd.blog_end) AS tripEndDate,
				g.latitude AS latitude,
				g.longitude AS longitude,
				m.username
			FROM
				trip t
			LEFT JOIN trip_data td ON t.trip_id = td.trip_id
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			LEFT JOIN geonames g ON bd.blog_geonameid = g.geonameid
			LEFT JOIN members m ON m.id = t.IdMember
			WHERE " . $memberQuery . "
			ORDER BY
				tripStartDate DESC
		";
		$query .= $typeQuery;
		$query .= " LIMIT 0, " . $limit;

		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}
		$trips = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$trips[] = $row;
		}
		return $trips;
	}

	public function getAllTrips($type = self::TRIPS_TYPE_ALL, $limit = false)
	{
		return $this->_getAllTrips($limit);
	}

	public function getAllUpcomingTrips()
	{
		return $this->_getAllTrips(false, self::TRIPS_TYPE_UPCOMING);
	}

	public function getAllTripsForMember($member, $type = self::TRIPS_TYPE_ALL, $limit = false)
	{
		return $this->_getAllTrips($member, $type, $limit);
	}

	public function getUpcomingTripsCount()
	{
		$query = "
			SELECT
				count(DISTINCT t.trip_id) AS cnt
			FROM
				trip t
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			WHERE
				((bd.blog_start IS NOT NULL) AND (DATE(bd.blog_start) >= NOW()))
				 OR ((bd.blog_end IS NOT NULL ) AND (DATE(bd.blog_end) >= NOW()))
			";
		$row = $this->singleLookup($query);
		return $row->cnt;
	}

	public function getPastTripsCount()
	{
		$query = "
			SELECT DISTINCT
				count(DISTINCT t.trip_id) AS cnt
			FROM
				trip t
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			WHERE
				((bd.blog_start IS NOT NULL) AND (DATE(bd.blog_start) <= NOW()))
				 OR ((bd.blog_end IS NOT NULL ) AND (DATE(bd.blog_end) <= NOW()))
			";
		$row = $this->singleLookup($query);
		return $row->cnt;
	}

	/**
	 * Gets the data to the found trips needs to be done in a two step process due to the organisation of the tables
	 * otherwise the limit for the trips per page is enforced on the sub trips leading to a rather strange layout
	 *
	 * @param $result
	 * @return array
	 */
	private function _getTripData($result)
	{
		// get all trip ids first
		$tripIds = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$tripIds[] = $row->trip_id;
		}

		if (count($tripIds) == 0) {
			return array();
		}

		// Now get the trip data
		$query = "
		SELECT
				td.*,
				b.*,
				bd.blog_title,
				UNIX_TIMESTAMP(bd.blog_start) AS tripStartDate,
				UNIX_TIMESTAMP(bd.blog_end) AS tripEndDate,
				g.latitude AS latitude,
				g.longitude AS longitude,
				g.geonameid AS geonameid,
				m.username
			FROM
				trip t
			LEFT JOIN trip_data td ON t.trip_id = td.trip_id
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			LEFT JOIN geonames g ON bd.blog_geonameid = g.geonameid
			LEFT JOIN members m ON m.id = t.IdMember
			WHERE
				t.trip_id IN ('" . implode("', '", $tripIds) . "')
			ORDER BY
				t.trip_id, bd.blog_start, bd.blog_end";
		$result = $this->dao->query($query);

		$trips = array();
		$tripInfo = new StdClass;
		$lastTripId = 0;
		while ($row= $result->fetch(PDB::FETCH_OBJ)) {
			$tripId = $row->trip_id;
			if ($tripId <> $lastTripId) {
				$lastTripId = $tripId;
				$tripInfo = new StdClass;
				$tripInfo->name = $row->trip_name;
				$tripInfo->description = $row->trip_descr;
				$tripInfo->member = new Member($row->IdMember);
				$tripInfo->data = array();
				$startDate = $endDate = 0;
			}
			$tripData = $tripInfo->data;
			if ($row->tripStartDate != 0) {
				$blogStart = $row->tripStartDate;
				if ($startDate == 0) {
					$startDate = $blogStart;
				}
				if ($blogStart <> 0) {
					$startDate = min($blogStart, $startDate);
				}
			}
			if (($row->tripEndDate != 0) || (($row->tripEndDate == null) && ($row->tripStartDate !=0))) {
				$blogEnd = max($row->tripEndDate, $row->tripStartDate);
				if ($endDate == 0) {
					$endDate = $blogEnd;
				}
				if ($blogEnd <> 0) {
					$endDate = max($blogEnd, $endDate);
				}
			}
			if ($row->geonameid) {
				$geo = new Geo($row->geonameid);
				$geoAlternateName = $this->createEntity('GeoAlternateName');
				$geoName = $geoAlternateName->getNameForLocation($geo, $_SESSION['lang']);
				if (!$geoName) {
					$geoName = $geo->getName();
				}
				$tripData[$row->blog_id] = array(
					"title" => $row->blog_title,
					"startdate" => date('Y-m-d', $row->tripStartDate),
					"enddate" => date('Y-m-d', $row->tripEndDate),
					"location" => $geoName,
				);
			}
			$duration = '';
			if ($startDate <> 0) {
				$duration .= date('Y-m-d', $startDate);
			}
			if ($endDate != $startDate) {
				$duration .= " - " . date('Y-m-d', $endDate);
			}
			$tripInfo->startdate = date('Y-m-d', $startDate);
			$tripInfo->enddate = date('Y-m-d', $endDate);
			$tripInfo->duration = $duration;

			$tripInfo->data = $tripData;
			$trips[$tripId] = $tripInfo;
		}

		return $trips;
	}

	public function getUpcomingTrips($pageNumber, $itemsPerPage) {
		$limit = ($pageNumber-1) * $itemsPerPage;

		$query = "
			SELECT DISTINCT
				t.trip_id
			FROM
				trip t
			LEFT JOIN trip_data td ON t.trip_id = td.trip_id
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			WHERE
				((bd.blog_start IS NOT NULL) AND (DATE(bd.blog_start) >= NOW()))
				OR ((bd.blog_end IS NOT NULL ) AND (DATE(bd.blog_end) >= NOW()))
			ORDER BY
				t.trip_id ASC, bd.blog_start, bd.blog_end
			LIMIT " . $limit . "," . $itemsPerPage;

		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}

		return $this->_getTripData($result);
	}

	public function getPastTrips($pageNumber, $itemsPerPage) {
		$limit = ($pageNumber-1) * $itemsPerPage;

		$query = "
			SELECT DISTINCT
				t.trip_id
			FROM
				trip t
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			WHERE
				((bd.blog_start IS NOT NULL) AND (DATE(bd.blog_start) <= NOW()))
				 OR ((bd.blog_end IS NOT NULL ) AND (DATE(bd.blog_end) <= NOW()))
			ORDER BY
				t.trip_id DESC, bd.blog_start DESC, bd.blog_end DESC
			LIMIT " . $limit . "," . $itemsPerPage;

		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}

		return $this->_getTripData($result);
	}

	public function getTripsForMemberCount($member) {
		$query = "
			SELECT
				count(*) as cnt
			FROM
				trip t
			WHERE
				t.IdMember = " . $member->id . "
			";
		$row = $this->singleLookup($query);
		return $row->cnt;
	}

	public function getTripsForMember($member, $pageNumber, $itemsPerPage) {
		$limit = ($pageNumber-1) * $itemsPerPage;

		$query = "
			SELECT
				t.*,
				td.*,
				b.*,
				bd.*,
				DATE(bd.blog_start) AS tripStartDate,
				DATE(bd.blog_end) AS tripEndDate,
				g.latitude AS latitude,
				g.longitude AS longitude,
				m.username
			FROM
				trip t
			LEFT JOIN trip_data td ON t.trip_id = td.trip_id
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
			LEFT JOIN geonames g ON bd.blog_geonameid = g.geonameid
			LEFT JOIN members m ON m.id = t.IdMember
			WHERE
				t.IdMember = " . $member->id . "
			ORDER BY
				tripStartDate DESC
			LIMIT " . $limit . "," . $itemsPerPage;

		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips.');
		}
		$trips = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$trips[] = $row;
		}
		return $trips;
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

	public function editTrip($vars, $member)
    {
		$errors = array();
		if ($this->checkTripOwnership($vars['trip-id'], $member)) {

			// Update the Tripdata
	        $query = <<<SQL
UPDATE `trip_data`
SET
    `trip_name` = '{$this->dao->escape($vars['trip-name'])}',
    `trip_descr` = '{$this->dao->escape($vars['trip-desc'])}',
    `edited` = NOW()
WHERE `trip_id` = '{$this->dao->escape($vars['trip-id'])}'
SQL;

			$this->dao->query($query);
		} else {
			$errors[] = 'TripErrorNotOwner';
		}
		return $errors;
	}

	private function checkTripOwnership($tripId, $member)
    {
		// Check the ownership of the trip - better safe than sorry
		$query = <<<SQL
SELECT trip_id
FROM trip
WHERE trip_id = '{$this->dao->escape($tripId)}' AND IdMember = '{$member->id}'
SQL;
		$result = $this->dao->query($query);
		if (!$result)
        {
			return false;
		}
		$row = $result->fetch(PDB::FETCH_OBJ);
		return ($row->trip_id > 0);
	}

	public function deleteTrip($vars, $member)
    {
		$errors = array();
		if ($this->checkTripOwnership($vars['trip-id'], $member)) {
			$this->dao->query('START TRANSACTION');

			// Update all blog entries and remove the trip-foreign key
	        $query = sprintf("UPDATE `blog` SET `trip_id_foreign` = NULL WHERE `trip_id_foreign` = '%d'",
				$vars['trip-id']);
			$this->dao->query($query);

			// Delete the trip data
	        $query = sprintf("DELETE FROM `trip_data` WHERE `trip_id` = '%d' LIMIT 1",
				$vars['trip-id']);
			$this->dao->query($query);

			// Delete the trip
	        $query = sprintf("DELETE FROM `trip` WHERE `trip_id` = '%d' LIMIT 1",
				$vars['trip-id']);
			$this->dao->query($query);

			$this->dao->query('COMMIT');
		} else {
			$errors[] = 'TripErrorNotOwner';
		}
		return $errors;
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
			WHERE `geonames_cache`.`name` LIKE '%1\$s'
            OR `blog_title` LIKE '%1\$s'
            OR `blog_text` LIKE '%1\$s'",
			$this->dao->escape($search));

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
        // Make use of the previously filled $this->tripsid array
		$query = "
SELECT `trip`.`trip_id`, `trip_data`.`trip_name`, `trip_text`, `trip_descr`, members.Username AS handle, `geonames_cache`.`fk_countrycode`, `trip_to_gallery`.`gallery_id_foreign`
    FROM `trip`
    RIGHT JOIN `trip_data` ON (`trip`.`trip_id` = `trip_data`.`trip_id`)
    LEFT JOIN members ON members.id = trip.IdMember AND members.status IN (" . Member::ACTIVE_ALL .")
    LEFT JOIN addresses ON addresses.IdMember = members.id
    LEFT JOIN geonames_cache ON addresses.IdCity = geonames_cache.geonameid
    LEFT JOIN `trip_to_gallery` ON (`trip_to_gallery`.`trip_id_foreign` = `trip`.`trip_id`)
    WHERE `trip`.`trip_id` IN ('" . implode("', '", $this->tripids) . "')
    ORDER BY trip_touched DESC
    LIMIT 0,100
";

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
