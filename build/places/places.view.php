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
    private $_model;
    
    public function __construct(Places $model) {
        $this->_model = $model;
    }

    public function customStyles(){       
        // calls a 1column layout 
         echo '<link rel="stylesheet"
                    href="styles/css/minimal/screen/custom/places.css?1"
                    type="text/css"/>';
    }
    
    public function teaserplaces($countrycode,$country,$region,$city) {
        require 'templates/teaserCountry.php';
    }

    /*
     * contains template for memberlist and wiki
     * used for country-, region- and citypage
     */
    public function displayPlaceInfo($placeinfo, $members, $namevar,
                                     $placelisttype = 'none',$list = null) {
        $forums = '';
        $wiki = new WikiController();
        $wikipage = str_replace(' ', '', ucwords($placeinfo->$namevar));
        require 'templates/placeinfo.php';
    }

    /*
     * Shows countries by continent
     */
    public function displayCountries($allcountries) {
        $words = new MOD_words();
        $list = '<table><tr>';
        $list .= '<td style="vertical-align: top;"><h3>'.$words->getformatted('Africa').'</h3>'.$this->displayContinent('AF', $allcountries['AF']).'</td>';
        $list .= '<td style="vertical-align: top;"><h3>'.$words->getformatted('Asia').'</h3>'.$this->displayContinent('AS', $allcountries['AS']).'</td>';
        $list .= '<td style="vertical-align: top;"><h3>'.$words->getformatted('Europe').'</h3>'.$this->displayContinent('EU', $allcountries['EU']).'</td>';
        $list .= '<td style="vertical-align: top;"><h3>'.$words->getformatted('NorthAmerica').'</h3>'.$this->displayContinent('NA', $allcountries['NA']);
        $list .= '<h3>'.$words->getformatted('SouthAmerica').'</h3>'.$this->displayContinent('SA', $allcountries['SA']).'</td>';
        $list .= '<td style="vertical-align: top;"><h3>'.$words->getformatted('Oceania').'</h3>'.$this->displayContinent('OC', $allcountries['OC']).'</td>';
        $list .= '</tr></table>';

        $placelisttype = 'country';
        require 'templates/placeinfo.php';
    }

    /*
     * Shows the country page
     *
     * contains regionlist, members of this country and countrywiki
     */
    public function displayRegions($countrycode,$countryinfo,$members) {
        define('MINROWS',5); // minimum number of rows to be used before next column
        define('MAXCOLS',3); // maximum number columns before extending rows beyound MINROWS
        $regionlist = '<div class="floatbox places">';
        $regionlist .= '<ul class="float_left">';
        $listcnt = 0;
        $countryinfo->memberCount = 0;
        foreach ($this->regions as $region) {
            // counting total members for possible login-to-see-more message
            $countryinfo->memberCount += $region['number'];

            $listcnt++;
            if ($listcnt > max(MINROWS,count($this->regions)/MAXCOLS)) {
                $regionlist .= '</ul>';
                $regionlist .= '<ul class="float_left">';
                $listcnt = 1;
            }
            $regionlist .= '<li><a class="highlighted" href="places/'.$countrycode.'/'.$region['name'].'">'.$region['name'].' <span class="small grey">('.$region['number'].')</span>';
            $regionlist .= '</a></li>';
        }
        $regionlist .= '</ul></div>';

        $this->displayPlaceInfo($countryinfo, $members,'name','region',$regionlist);
    }

    /*
     * Shows the city page 
     */
    public function displayCities($region,$countrycode,$regioninfo,$members) {
        define('MINROWS',5); // minimum number of rows to be used before next column
        define('MAXCOLS',3); // maximum number columns before extending rows beyound MINROWS
        $citylist = '<div class="floatbox places">';
        $citylist .= '<ul class="float_left">';
        $listcnt = 0;
        $regioninfo->memberCount = 0;
        foreach ($this->cities as $city) {
            $regioninfo->memberCount += $city->NbMember;
            $listcnt++;
            if ($listcnt > max(MINROWS,count($this->cities)/MAXCOLS)) {
                $citylist .= '</ul>';
                $citylist .= '<ul class="float_left">';
                $listcnt = 1;
            }            
            $citylist .= '<li><a class="highlighted" href="places/'.$countrycode.'/'.$region.'/'.$city->city.'">'.$city->city.' <span class="small grey">('.$city->NbMember.')</span>';
            $citylist .= '</a></li>';
        }
        $citylist .= '</ul></div>';        

        $this->displayPlaceInfo($regioninfo, $members,'region','city',$citylist);
    }   

    /*
     * Creates the list of countries in a continent for the places page 
     */
    private function displayContinent($continent, $countries) {
        $html = '';
        $html .= '<ul>';
        foreach ($countries as $code => $country) {
           $html .= '<li class="spritecontainer"><div class="sprite sprite-'.strtolower($code).'"><a href="places/'.$code.'"></a></div> <a href="places/'.$code.'" class="'.($country['number'] ? 'highlighted' : 'grey').'">'.$country['name'];
            if ($country['number']) {
               $html .= '<span class="small grey"> ('.$country['number'].')</span>';
            }
            $html .= '</a></li>';
        }
        $html .= '</ul>';
        return $html;   
    }
    
    public function placesNotFound($placename="") {
        // Tsjoek 16072013 - fixed the xss hole, but the whole thing
        // is still far from graceful ;-) should be fixed one other day
        echo '<h2>Places '.htmlspecialchars($placename).' not found</h2>';
    }
}
?>
