<?php
/**
* Country view
*
* @package country
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class CountryView extends PAppView {
	private $_model;
	
	public function __construct(Country $model) {
		$this->_model = $model;
	}

    // only for testing
	public function testpage() {
		require TEMPLATE_DIR.'apps/country/testPage.php';
	}	
    // only for testing // END
	public function teasercountry($countrycode,$country,$region,$city) {
		require TEMPLATE_DIR.'apps/country/teaserCountry.php';
	}
	public function countrybar() {
		require TEMPLATE_DIR.'apps/country/countrybar.php';
	}
	public function submenu($subTab) {
        require TEMPLATE_DIR.'apps/searchmembers/submenu.php';
	}
	public function displayCountryInfo($countryinfo, $members) {
		$memberlist = $this->generateMemberList($members);
		$forums = '';
		$wiki = new WikiController();
		$wikipage = str_replace(' ', '', ucwords($countryinfo->name));
		require TEMPLATE_DIR.'apps/country/countryInfo.php';
	}
	public function displayRegionInfo($regioninfo, $members) {
		$memberlist = $this->generateMemberList($members);
		$forums = '';
		$wiki = new WikiController();
		$wikipage = str_replace(' ', '', ucwords($regioninfo->region));
		require TEMPLATE_DIR.'apps/country/regionInfo.php';
	}
	public function displayCityInfo($cityinfo, $members) {
		$memberlist = $this->generateMemberList($members);
		$forums = '';
		$wiki = new WikiController();
		$wikipage = str_replace(' ', '', ucwords($cityinfo->city));
		require TEMPLATE_DIR.'apps/country/cityInfo.php';
	}
	private function generateMemberList($members) {
		$i18n = new MOD_i18n('apps/country/countryOverview.php');
		$text = $i18n->getText('text');   
		if (!$members) {
			return $text['no_members'];
		} else {
			$memberlist = '<ul class="floatbox">';
			foreach ($members as $member) {
                $image = new MOD_images_Image('',$member->username);
                $picURL = $image->getPicture($member->username);
                if (!$picURL){ $picURL = '/memberphotos/et_male.jpg';}
				$memberlist .= '<li class="userpicbox float_left"><a href="user/'.$member->username.'"><img src="bw'.$picURL.'" class="framed float_left" style="height:50px; width: 50px;">'.$member->username.'</a><p>from '.$member->city.'</p></li>';
			}
			$memberlist .= '</ul>';
			$memberlist .= '<p>Only displaying a maximum of 20 members.</p><p>To get a full list of members for this place, go use our <a href="searchmembers/index">advanced search</a>.';
			return $memberlist;
		}
	}

	public function displayCountryOverview($allcountries) {
		$countrylist = '<table><tr>';

		$countrylist .= '<td style="vertical-align: top;">'.$this->displayContinent('AF', $allcountries['AF']).'</td>';
		$countrylist .= '<td style="vertical-align: top;">'.$this->displayContinent('AS', $allcountries['AS']).'</td>';
		$countrylist .= '<td style="vertical-align: top;">'.$this->displayContinent('EU', $allcountries['EU']).'</td>';
		$countrylist .= '<td style="vertical-align: top;">'.$this->displayContinent('NA', $allcountries['NA']);
		$countrylist .= $this->displayContinent('SA', $allcountries['SA']).'</td>';
		$countrylist .= '<td style="vertical-align: top;">'.$this->displayContinent('OC', $allcountries['OC']).'</td>';
//		$countrylist .= $this->displayContinent('AN', $allcountries['AN']).'</td>';
		
		$countrylist .= '</tr></table>';
	
		require TEMPLATE_DIR.'apps/country/countryOverview.php';
	}
    
	public function displayRegions($regions,$countrycode) {
		$regionlist = '<ul>';
        
		foreach ($regions as $region) {
			$regionlist .= '<li><a href="country/'.$countrycode.'/'.$region.'">'.$region;
			$regionlist .= '</a></li>';
		}
		$regionlist .= '</ul>';        
	
		require TEMPLATE_DIR.'apps/country/regionOverview.php';
	}	
    
	public function displayCities($cities,$region,$countrycode) {
		$citylist = '<ul>';
        
		foreach ($cities as $city) {
			$citylist .= '<li><a href="country/'.$countrycode.'/'.$region.'/'.$city.'">'.$city;
			$citylist .= '</a></li>';
		}
		$citylist .= '</ul>';        
	
		require TEMPLATE_DIR.'apps/country/cityOverview.php';
	}	

	private function displayContinent($continent, $countries) {
		$i18n = new MOD_i18n('apps/country/countryOverview.php');
		$cont = $i18n->getText('continents');

		$html = '<h3>'.$cont[$continent].'</h3>';
		
		$html .= '<ul>';
		foreach ($countries as $code => $country) {
			$html .= '<li><a href="country/'.$code.'"><img src="images/icons/flags/'.strtolower($code).'.png" alt="" /></a> <a href="country/'.$code.'"'.($country['number'] ? ' style="font-weight: bold;"' : '').'>'.$country['name'];
			if ($country['number']) {
				$html .= ' ['.$country['number'].']';
			}
			$html .= '</a></li>';
		}
		$html .= '</ul>';
		return $html;	
	}
	
	public function countryNotFound() {
		echo '<h2>Country not found</h2>'; // TODO
	}
}
?>
