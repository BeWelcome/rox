<?php
/**
* Country model
* 
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class Country extends PAppModel {
	private $_dao;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getCountryInfo($countrycode) {
		$query = sprintf("SELECT `name`, `continent`
			FROM `geonames_countries`
			WHERE `iso_alpha2` = '%s'",
			$this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		return $result->fetch(PDB::FETCH_OBJ);
	}
	
	public function getMembersOfCountry($countrycode) {
		$query = sprintf("SELECT `handle`
			FROM `user`
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `active` = '1' AND `geonames_cache`.`fk_countrycode` = '%s'
			ORDER BY `handle` ASC",
			$this->dao->escape($countrycode));
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve members list.');
		}
		$members = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$members[] = $row->handle;
		}
		return $members;
	}
	
	/**
	* Returns a 3-Dimensional array of all countries
	* Format:
	*	[Continent]
	*		[Country-Code]
	*			[Name] Name of the Country
	*			[Number] Number of members living in this country
	*/
	public function getAllCountries() {
		$query = "SELECT `fk_countrycode`, COUNT(`id`) AS `number`
			FROM `user`
			LEFT JOIN `geonames_cache` ON (`user`.`location` = `geonames_cache`.`geonameid`)
			WHERE `user`.`active` = '1' AND `user`.`location` IS NOT NULL
			GROUP BY `fk_countrycode`";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Country list.');
		}
		$number = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$number[$row->fk_countrycode] = $row->number;
		}
		
		$query = "SELECT `iso_alpha2` AS `code`, `name`, `continent`
			FROM `geonames_countries`
			ORDER BY `continent` ASC, `name` ASC";
		$result = $this->dao->query($query);
        if (!$result) {
            throw new PException('Could not retrieve Country list.');
		}
		$countries = array();
		while ($row = $result->fetch(PDB::FETCH_OBJ)) {
			$countries[$row->continent][$row->code]['name'] = $row->name;
			if (isset($number[$row->code]) && $number[$row->code]) {
				$countries[$row->continent][$row->code]['number'] = $number[$row->code];
			} else {
				$countries[$row->continent][$row->code]['number'] = 0;
			}
		}
		
        return $countries;
	}
	
}
?>