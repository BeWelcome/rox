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
	const TRIPS_TYPE_PAST = 'past';
    const TRIPS_TYPE_UPCOMING = 'upcoming';
    const TRIPS_TYPE_MYTRIPS = 'mytrips';
	const TRIPS_TYPE_ALL = 3;

	const TRIPS_ADDITIONAL_INFO_SINGLE = 1;
	const TRIPS_ADDITIONAL_INFO_COUPLE = 2;
	const TRIPS_ADDITIONAL_INFO_FRIENDS_MIXED = 4;
	const TRIPS_ADDITIONAL_INFO_FRIENDS_SAME = 8;
	const TRIPS_ADDITIONAL_INFO_FAMILY = 16;

	const TRIPS_OPTIONS_LOOKING_FOR_A_HOST = 1;
	const TRIPS_OPTIONS_LIKE_TO_MEETUP = 2;

	public static function getAdditonalInfoOptions() {
		$words = new MOD_words(); // self::getWords();
		$options = array(
			self::TRIPS_ADDITIONAL_INFO_SINGLE => $words->getBuffered('TripsAdditionalInfoSingle'),
			self::TRIPS_ADDITIONAL_INFO_COUPLE => $words->getBuffered('TripsAdditionalInfoCouple'),
			self::TRIPS_ADDITIONAL_INFO_FRIENDS_MIXED => $words->getBuffered('TripsAdditionalInfoFriendsMixed'),
			self::TRIPS_ADDITIONAL_INFO_FRIENDS_SAME => $words->getBuffered('TripsAdditionalInfoFriendsSame'),
			self::TRIPS_ADDITIONAL_INFO_FAMILY => $words->getBuffered('TripsAdditionalInfoFamily'),
		);
		return $options;
	}

	public static function getLocationOptions() {
		$words = new MOD_words(); // self::getWords();
		$options = array(
			self::TRIPS_OPTIONS_LOOKING_FOR_A_HOST => $words->getBuffered('TripsLocationOptionLookingForAHost'),
			self::TRIPS_OPTIONS_LIKE_TO_MEETUP => $words->getBuffered('TripsLocationOptionLikeToMeetup'),
		);
		return $options;
	}

	public function __construct()
	{
		parent::__construct();
	}

	public function getEmptyLocationDetails() {
		$locationDetails = new StdClass;
		$locationDetails->subTripId = 0;
		$locationDetails->geonameId = "";
		$locationDetails->name = "";
		$locationDetails->arrival = "";
		$locationDetails->departure = "";
		$locationDetails->arrivalTS = "";
		$locationDetails->departureTS = "";
		$locationDetails->latitude = "";
		$locationDetails->longitude = "";
		$locationDetails->options = 0;
		return $locationDetails;
	}

    private function _getWhereForTripsType($type) {
        switch($type) {
            case self::TRIPS_TYPE_UPCOMING:
                $where = 'AND (st.arrival >= NOW() or st.departure >= NOW())';
                break;
            case self::TRIPS_TYPE_PAST:
                $where = 'AND (st.arrival < NOW() or st.departure < NOW())';
                break;
            case self::TRIPS_TYPE_MYTRIPS:
                $member = $this->getLoggedInMember();
                $where = 'AND t.memberId = ' . $member->id;
                break;
            default:
                $where = 'AND (1 != 0)';
        }
        return $where;
    }

    private function _getOrderForTripsType($type) {
        switch($type) {
            case self::TRIPS_TYPE_UPCOMING:
                $order = 'st.arrival, st.departure';
                break;
            case self::TRIPS_TYPE_PAST:
                $order = 'st.arrival DESC, st.departure DESC';
                break;
            default:
                $order = "t.id DESC";
        }
        return $order;
    }

    private function _getTrips($type, $offset = false, $limit = false) {
        $where = $this->_getWhereForTripsType($type);
        $order = $this->_getOrderForTripsType($type);
        if (($offset !== false) && ($limit !== false)) {
            $sqlLimit = "LIMIT " . $offset . ", " . $limit;
        } else {
            $sqlLimit = "";
        }

        // get trip ids for $type trips from the database and
        // afterwards create entities

        $trips = array();
        $query = "
			SELECT DISTINCT
				t.id AS id
			FROM
				trips t,
				subtrips st
			WHERE
				t.id = st.tripId " . $where . "
			ORDER BY "
                . $order . "
            " . $sqlLimit;
        $sql = $this->dao->query($query);
        if ($sql) {
            while ($row = $sql->fetch(PDB::FETCH_OBJ)) {
                $trips[] = $row->id;
            }
        }
        return $trips;

    }

    /**
     * @param int $type
     * @return array
     */
    public function getAllTrips($type = self::TRIPS_TYPE_ALL)
	{
        $mapTrips = array();
        switch($type) {
            case self::TRIPS_TYPE_PAST;
            case self::TRIPS_TYPE_UPCOMING:
            case self::TRIPS_TYPE_MYTRIPS:
                $trips = $this->_getTrips($type);
                // Collect information needed for maps overlay
                foreach($trips as $trip) {
                    $trip = new Trip($trip);
                    $t = array(
                        'id' => $trip->getPKValue(),
                        'title' => $trip->title,
                        'username' => $trip->username,
                        'subtrips' => array(),
                    );
                    $subTrips = array();
                    foreach($trip->getSubTrips() as $subTrip) {
                        $st = array(
                            'id' => $subTrip->id,
                            'latitude' => $subTrip->latitude,
                            'longitude' => $subTrip->longitude,
                            'arrival' => $subTrip->arrival,
                            'departure' => $subTrip->departure
                        );
                        $subTrips[] = $st;
                    }
                    $t['subtrips'] = $subTrips;
                    $mapTrips[] = $t;
                }
                break;
        }
        return array('trips' => $mapTrips);
	}

    private function _getTripsCount($type) {
        $where = $this->_getWhereForTripsType($type);
        $count = 0;
        $query = "
			SELECT
				COUNT(DISTINCT t.id) AS count
			FROM
				trips t,
				subtrips st
			WHERE
				t.id = st.tripId " . $where;
        $sql = $this->dao->query($query);
        if ($sql) {
            $row = $sql->fetch(PDB::FETCH_OBJ);
            $count = $row->count;
        }
        return $count;

    }

	public function getTripsCount($type)
	{
        switch($type) {
            case self::TRIPS_TYPE_PAST;
            case self::TRIPS_TYPE_UPCOMING:
            case self::TRIPS_TYPE_MYTRIPS:
                $count = $this->_getTripsCount($type);
                break;
            default:
                $count = 0;
        }
        return $count;
	}

    public function getTrips($type, $pageNumber, $itemsPerPage) {
        $offset = ($pageNumber -1) * $itemsPerPage;

        return $this->_getTrips($type, $offset, TripsController::TRIPS_PER_PAGE);
    }

	public function getTripsNearMe($member, $pageNumber, $itemsPerPage)
	{
		// Reuse activities nearme or add new preference
		$distance = 50;
		$limit = ($pageNumber-1) * $itemsPerPage;

		// get all locations in a certain area
		$query = "SELECT latitude, longitude FROM geonames WHERE geonameid = " . $member->IdCity;
		$sql = $this->dao->query($query);
		if (!$sql) {
			return false;
		}
		$row = $sql->fetch(PDB::FETCH_OBJ);

		// calculate rectangle around place with given distance
		$lat = deg2rad(doubleval($row->latitude));
		$long = deg2rad(doubleval($row->longitude));

		$longne = rad2deg(($distance + 6378 * $long) / 6378);
		$longsw = rad2deg((6378 * $long - $distance) / 6378);

		$radiusAtLatitude = 6378 * cos($lat);
		$latne = rad2deg(($distance + $radiusAtLatitude * $lat) / $radiusAtLatitude);
		$latsw = rad2deg(($radiusAtLatitude * $lat - $distance) / $radiusAtLatitude);
		if ($latne < $latsw) {
			$tmp = $latne;
			$latne = $latsw;
			$latsw = $tmp;
		}
		if ($longne < $longsw) {
			$tmp = $longne;
			$longne = $longsw;
			$longsw = $tmp;
		}

		$rectangle = 'geonames.latitude < ' . $latne . '
            AND geonames.latitude > ' . $latsw . '
            AND geonames.longitude < ' . $longne . '
            AND geonames.longitude > ' . $longsw;

		// retrieve the visiting members handle and trip data
		$query = "
            SELECT
            	t.trip_id
            FROM
            	trip AS t
			LEFT JOIN blog b ON b.trip_id_foreign = t.trip_id
			LEFT JOIN blog_data bd ON bd.blog_id = b.blog_id
            LEFT JOIN geonames ON bd.blog_geonameid = geonames.geonameid
            WHERE " .
			$rectangle . "
            ORDER BY
                t.trip_id, bd.blog_start ASC
            LIMIT " . $limit . "," . $itemsPerPage;
//                 AND bd.blog_start >= CURDATE() AND bd.blog_start <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
		$result = $this->dao->query($query);
		if (!$result) {
			throw new PException('Could not retrieve trips');
		}

		return $this->_getTripData($result);
	}

	public function getTripsForMemberCount($member) {
		$query = "
			SELECT
				count(*) as cnt
			FROM
				trips t
			WHERE
				t.memberId = " . $member->id . "
			";
		$row = $this->singleLookup($query);
		return $row->cnt;
	}

	public function getTripsForMember($member, $pageNumber, $itemsPerPage)
	{
		$temp = new Trip();
		$offset = ($pageNumber - 1) * $itemsPerPage;
		$trips = $temp->findByWhereMany('memberId = ' . $member->id, $offset , $itemsPerPage);
		return $trips;
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

	public function checkCreateEditVars($vars)
	{
		$tripInfo = array(
			'trip-id' => 0,
			'trip-title' => '',
			'trip-description' => '',
			'trip-count' => null,
			'trip-additional-info' => null
		);
		$errors = array();
		if (isset($vars['trip-id'])) {
			$tripInfo['trip-id'] = $vars['trip-id'];
		}
		if (isset($vars['trip-title'])) {
			$tripInfo['trip-title'] = $vars['trip-title'];
		}
		if (empty($tripInfo['trip-title'])) {
			$errors[] = 'TripErrorNameEmpty';
		}
		if (isset($vars['trip-description'])) {
			$tripInfo['trip-description'] = $vars['trip-description'];
		}
		if (empty($tripInfo['trip-description'])) {
			$errors[] = 'TripErrorDescriptionEmpty';
		}
		if (isset($vars['trip-count'])) {
			// Check if count and additional info matches
			$tripInfo['trip-count'] = $vars['trip-count'];
			$count = $tripInfo['trip-count'];
			$additionalInfo = isset($vars['trip-additional-info']) ? $vars['trip-additional-info'] : false;
			if (($count == 1) && ($additionalInfo <> self::TRIPS_ADDITIONAL_INFO_SINGLE)) {
				$errors[] = 'TripErrorCountAdditionalMismatch';
			}
			if (($count <> 1) && ($additionalInfo == self::TRIPS_ADDITIONAL_INFO_SINGLE)) {
				$errors[] = 'TripErrorCountAdditionalMismatch';
			}
			if (($count == 2) && ($additionalInfo == self::TRIPS_ADDITIONAL_INFO_SINGLE
					|| $additionalInfo == self::TRIPS_ADDITIONAL_INFO_FAMILY)) {
				$errors[] = 'TripErrorCountAdditionalMismatch';
			}
		}
		if (isset($vars['trip-additional-info'])) {
			$tripInfo['trip-additional-info'] = $vars['trip-additional-info'];
			$additionalInfo = $tripInfo['trip-additional-info'];
			$count = isset($vars['trip-count']) ? $vars['trip-count'] : false;
			if (!$count && ($additionalInfo == self::TRIPS_ADDITIONAL_INFO_FRIENDS_MIXED
					|| $additionalInfo == self::TRIPS_ADDITIONAL_INFO_FRIENDS_SAME
					|| $additionalInfo == self::TRIPS_ADDITIONAL_INFO_FAMILY)) {
				$errors[] = 'TripErrorNumberOfPartyMissing';
			}
		}
		$locations = array();
		if (isset($vars['location'])) {
			// remove empty rows and build locations array
			$count = count($vars['location']);
			for ($i = 0; $i < $count; $i++) {
				$location = new StdClass;
                $location->subTripId = $vars['location-subtrip-id'][$i];
				$location->geonameId = $vars['location-geoname-id'][$i];
				$location->latitude = $vars['location-latitude'][$i];
				$location->name = $vars['location'][$i];
				$location->longitude = $vars['location-longitude'][$i];
				$location->arrival = $vars['location-arrival'][$i];
				$location->arrivalTS = strtotime($vars['location-arrival'][$i]);
				$location->departure = $vars['location-departure'][$i];
				$location->departureTS = strtotime($vars['location-departure'][$i]);
				if (isset($vars['location-options'])) {
					$location->options = $vars['location-options'][$i];
				} else {
					$location->options = '';
				}
				$emptyRow = empty($location->name) && empty($location->arrival) && empty($location->departure);
				if (!$emptyRow) {
					$locations[] = $location;
					if (!($location->arrivalTS)) {
						if (!in_array('TripErrorWrongArrivalFormat###' . ($i +1), $errors)) {
							$errors[] = 'TripErrorWrongArrivalFormat###' . ($i + 1);
						}
					}
					if (!($location->departureTS)) {
						if (!in_array('TripErrorWrongDepartureFormat###' . ($i + 1), $errors)) {
							$errors[] = 'TripErrorWrongDepartureFormat###' . ($i + 1);
						}
					}
				}
			}

			$count = count($locations);
			if ($count == 0) {
				$errors[] = 'TripErrorNoLocationSpecified';
			}

			// check that date range is start <= end
			if (count($locations) > 1) {
				for($i = 0; $i < $count; $i++) {
					$start = $locations[$i]->arrivalTS;
					$end = $locations[$i]->departureTS;
					if ($start && $end && ($start > $end)) {
						$temp = $locations[$i]->arrivalTS;
						$tempString = $locations[$i]->arrival;
						$locations[$i]->arrival = $locations[$i]->departure;
						$locations[$i]->arrivalTS = $locations[$i]->departureTS;
						$locations[$i]->departure =  $temp;
						$locations[$i]->departureTS =  $tempString;
					}
				}
			}

			// check that date range don't overlap (except on start and end dates)
			if (count($locations) > 1) {
				$overlap = false;
				for ($i = 0; $i < $count - 1; $i++) {
					$start1 = $locations[$i]->arrivalTS;
					$end1 = $locations[$i]->departureTS;
					for ($j = $i + 1; $j < $count; $j++) {
						$start2 = $locations[$j]->arrivalTS;
						$end2 = $locations[$j]->departureTS;
						$overlap |= (($start1 < $end2) and ($end1 > $start2));
					}
				}
				if ($overlap) {
					$errors[] = 'TripErrorOverlappingDates';
				}
			}

			if (count($errors) == 0) {
				// order locations by start date (ascending)
				usort($locations, function($a, $b)
				{
					if ($a->arrivalTS == $b->arrivalTS)
					{
						if ($a->departureTS < $b->departureTS) {
							return -1;
						} else {
							return 1;
						}
					}
					else if ($a->arrivalTS < $b->arrivalTS)
					{
						return -1;
					}
					else {
						return 1;
					}
				});
			}
		} else {
			$errors[] = 'TripErrorNoLocationSpecified';
		}
		if (count($errors) > 0) {
			// Make sure that there is an location empty row in case of an error
			$locations[] = $this->getEmptyLocationDetails();
		}
		$tripInfo['locations'] = $locations;

		return array($errors, $tripInfo);
	}

	public function createTrip($tripInfo) {
		$member = $this->getLoggedInMember();
		if (!$member) {
			return false;
		}
		$trip = new Trip();
		$trip->title = $tripInfo['trip-title'];
		$trip->description = $tripInfo['trip-description'];
		$trip->countOfTravellers = $tripInfo['trip-count'];
		$trip->additionalInfo = $tripInfo['trip-additional-info'];
		$trip->memberId = $member->id;
		// add sub trips
		foreach($tripInfo['locations'] as $location) {
			$trip->addSubTrip($location);
		}
		$trip->insert();
		return $trip;
	}

	public function editTrip($tripInfo) {
		$member = $this->getLoggedInMember();
		if (!$member) {
			return false;
		}
		if ($member->id != $tripInfo->memberId)
		$trip = new Trip($tripInfo['trip-id']);
		$trip->title = $tripInfo['trip-title'];
		$trip->description = $tripInfo['trip-description'];
		$trip->countOfTravellers = $tripInfo['trip-count'];
		$trip->additionalInfo = $tripInfo['trip-additional-info'];
		$trip->memberId = $member->id;
		// add sub trips
		foreach($tripInfo['locations'] as $location) {
			$trip->addSubTrip($location);
		}
		$trip->update();
		return $trip;
	}
}
