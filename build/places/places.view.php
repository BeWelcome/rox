<?php
/**
* Places view
*
* @package places
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class PlacesView extends PAppView
{
//    private $_model;
//
//    public function __construct(Places $model) {
//        $this->_model = $model;
//    }
//
//    public function customStyles(){
//        // calls a 1column layout
//         echo '<link rel="stylesheet"
//                    href="styles/css/minimal/screen/custom/places.css?2"
//                    type="text/css"/>
//                <link rel="stylesheet" href="styles/css/minimal/screen/basemod_minimal_col3.css" />';
//    }
//
//    public function teaserplaces($countrycode,$country,$region,$city) {
//        require 'templates/teaserCountry.php';
//    }
//
//    /*
//     * contains template for memberlist and wiki
//     * used for country-, region- and citypage
//     */
//    public function displayPlaceInfo($placename, $members, $namevar,
//                                     $placelisttype = 'none',$list = null) {
//        $forums = '';
//        $wiki = new WikiController();
//        $wikipage = str_replace(' ', '', ucwords($placename));
//        require 'templates/placeinfo.php';
//    }
//
//    /*
//     * Shows countries by continent
//     */
//    public function displayCountries($allcountries, $continents) {
//        $words = new MOD_words();
//        // Show a table with 5 columns. The columns are filled with the countries one after another.
//        // show approx. 51 entries per column
//        $list = '<table id="places"><tr>';
//
//        $i = 0;
//        $max = 53;
//        $top = true;
//        $columns = array();
//        $lastcontinent = "";
//        foreach($continents as $continent => $value) {
//            foreach($allcountries[$continent] as $country) {
//                if ($top) {
//                    $list .= '<td>';
//                    if ($continent == $lastcontinent) {
//                        $list .= '<h3>' . $value[1] . '</h3>';
//                    } else {
//                        $list .= '<h3>' . $value[0] . '</h3>';
//                    }
//                    $list .= '<ul>';
//                    $top = false;
//                    $i++;
//                } else {
//                    if ($continent != $lastcontinent) {
//                        $list .= '<h3>' . $value[0] . '</h3>';
//                        $list .= '<ul>';
//                        $i++;
//                    }
//                }
//                $lastcontinent = $continent;
//                $list .= '<li><i class="famfamfam-flag-' . strtolower($country->country) .'"></i><div class="name"><a';
//                if ($country->number) {
//                    $list .= ' class="highlighted"';
//                }
//                $list .= ' href="/places/' . htmlspecialchars($country->name) . '/' . $country->country . '/">'. htmlspecialchars($country->name) . '</a>';
//                if ($country->number) {
//                    $list .= ' <span class="small grey">(' . $country->number . ')</span>';
//                }
//                $list .= '</div></li>';
//                $i++;
//                if ($i > $max) {
//                    $i = 0;
//                    $list .= '</ul></td>';
//                    $top = true;
//                }
//            }
//        }
//        if ($i <= $max) {
//            $list .= '</ul></td>';
//        }
//        $list .= '</tr></table>';
//
//        $placelisttype = 'country';
//        require 'templates/placeinfo.php';
//    }
//
//    /*
//     * Shows the country page
//     *
//     * contains regionlist, members of this country and countrywiki
//     */
//    public function displayRegions($countrycode,$countryname,$members) {
//        define('MINROWS',1); // minimum number of rows to be used before next column
//        define('MAXCOLS',5); // maximum number columns before extending rows beyound MINROWS
//        $regionlist = '<div class="floatbox places">';
//        $regionlist .= '<ul class="float_left">';
//        $listcnt = 0;
//        $memberCount = 0;
//        foreach ($this->regions as $code => $region) {
//            // counting total members for possible login-to-see-more message
//            $memberCount += $region['number'];
//
//            $listcnt++;
//            if ($listcnt > max(MINROWS,ceil(count($this->regions)/MAXCOLS))) {
//                $regionlist .= '</ul>';
//                $regionlist .= '<ul class="float_left">';
//                $listcnt = 1;
//            }
//            $regionlist .= '<li><a ';
//            if ($region['number'] != 0) {
//                $regionlist .= 'class="highlighted" ';
//            }
//            $regionlist .= 'href="places/' . htmlspecialchars($countryname) . '/' . $countrycode . '/'
//                . htmlspecialchars($region['name']) . '/' . $code . '">'. htmlspecialchars($region['name']) . '</a>';
//            if ($region['number'] != 0) {
//                $regionlist .= ' <span class="small grey">('.$region['number'].')</span>';
//            }
//            $regionlist .= '</li>';
//        }
//        $regionlist .= '</ul></div>';
//
//        $this->displayPlaceInfo($membersCount, $members, 'name', 'region', $regionlist);
//    }
//
//    /*
//     * Shows the city page
//     */
//    public function displayCities($countrycode,$regioninfo,$members) {
//        define('MINROWS',5); // minimum number of rows to be used before next column
//        define('MAXCOLS',3); // maximum number columns before extending rows beyound MINROWS
//        $citylist = '<div class="floatbox places">';
//        $citylist .= '<ul class="float_left">';
//        $listcnt = 0;
//        $regioninfo->memberCount = 0;
//        foreach ($this->cities as $city) {
//            $regioninfo->memberCount += $city->NbMember;
//            $listcnt++;
//            if ($listcnt > max(MINROWS,ceil(count($this->cities)/MAXCOLS))) {
//                $citylist .= '</ul>';
//                $citylist .= '<ul class="float_left">';
//                $listcnt = 1;
//            }
//            $citylist .= '<li><a class="highlighted" href="places/'.$countrycode.'/'.$regioninfo->region . '-' . $regioninfo->admin1.'/'.$city->city.'">'. htmlentities($city->city, ENT_COMPAT, 'utf-8') .' <span class="small grey">('.$city->NbMember.')</span>';
//            $citylist .= '</a></li>';
//        }
//        $citylist .= '</ul></div>';
//
//        $this->displayPlaceInfo($regioninfo, $members,'region','city',$citylist);
//    }
//
//    /*
//     * Creates the list of countries in a continent for the places page
//     */
//    private function displayContinent($continent, $countries) {
//        $html = '';
//        $html .= '<table>';
//
//        foreach ($countries as $country) {
//           $html .= '<li class="spritecontainer"><div class="sprite sprite-'.strtolower($country->country).'"><a href="places/'.$country->country.'"></a></div> <a href="places/'.$country->country.'" class="'.($country->number ? 'highlighted' : 'grey').'">'.$country->name;
//           if ($country->number) {
//               $html .= '<span class="small grey"> ('.$country->number.')</span>';
//            }
//            $html .= '</a></li>';
//        }
//        $html .= '</ul>';
//        return $html;
//    }
//
//    public function placesNotFound($placename="") {
//        // Tsjoek 16072013 - fixed the xss hole, but the whole thing
//        // is still far from graceful ;-) should be fixed one other day
//        echo '<h2>Places '.htmlspecialchars($placename).' not found</h2>';
//    }
}
?>
