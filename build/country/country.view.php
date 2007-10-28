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
	
	public function displayCountryInfo($countryinfo, $members) {
		$memberlist = $this->generateMemberList($members);
		$forums = '';
		$wiki = new WikiController();
		$wikipage = str_replace(' ', '', ucwords($countryinfo->name));
		require TEMPLATE_DIR.'apps/country/countryInfo.php';
	}
	

	private function generateMemberList($members) {
		$i18n = new MOD_i18n('apps/country/countryOverview.php');
		$text = $i18n->getText('text');

		if (!$members) {
			return $text['no_members'];
		} else {
			$memberlist = '<ul>';
			foreach ($members as $member) {
				$memberlist .= '<li><a href="user/'.$member.'">'.$member.'</a></li>';
			}
			$memberlist .= '</ul>';
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
		$countrylist .= '<td style="vertical-align: top;">'.$this->displayContinent('OC', $allcountries['OC']);
		$countrylist .= $this->displayContinent('AN', $allcountries['AN']).'</td>';
		
		$countrylist .= '</tr></table>';
	
		require TEMPLATE_DIR.'apps/country/countryOverview.php';
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