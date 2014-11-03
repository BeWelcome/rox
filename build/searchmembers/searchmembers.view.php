<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * searchmembers view
 *
 * @package searchmembers
 * @author matrixpoint
 */
class SearchmembersView extends PAppView {
    
    private $_model;
    public $page ;
            
    public function __construct(Searchmembers $model) {
        $this->_model = $model;
    }

    public function quicksearch_results($TReturn)
    {

        $this->page->title='Search Results - Bewelcome' ;
        require 'templates/quicksearch.php';
    }

    public function searchmembers($queries, $mapstyle, $varsOnLoad, $varsGet, $TabAccomodation)
    {
        $google_conf = PVars::getObj('config_google');
        include 'templates/index.php';
    }
    public function searchmembersFilters($TGroup, $TabAccomodation, $TabTypicOffer, $TabSortOrder)
    {
        include 'templates/filters.php';
    }
    public function searchmembers_ajax($TList, $vars, $mapstyle)
    {
    	include 'templates/ajax.php';
    }
    

    public function quicksearch_form()
    {
        $TList=array() ;
        $searchtext="" ;
        $mapstyle="mapoff";
        require 'templates/memberlist_quicksearch.php';
    }

    public function teaser($mapstyle, $TabSortOrder, $vars = false) {
        require 'templates/teaser.php';
    }
    
    public function search_column_col3($sortorder, $queries, $mapstyle, $varsOnLoad, $varsGet, $TabAccomodation) {
        $google_conf = PVars::getObj('config_google');
        if ($mapstyle == "mapoff") require 'templates/search_nomap.column_col3.php';
        else require 'templates/search.column_col3.php';
    }
    
    public function teaserquicksearch($mapstyle) {
        require 'templates/teaser_quicksearch.php';
    }

    public function memberlist($mapstyle,$TabSortOrder, $quicksearch=0) {
        if (!$quicksearch) {
        require 'templates/memberlist.php';
        } else {
        require 'templates/memberlist_quicksearch.php';
        }
    }
    
    /* This adds other custom styles to the page*/
    public function customStyles($mapstyle,$quicksearch=0) {
        $out = '<link rel="stylesheet" href="styles/css/minimal/screen/custom/searchmembers.css?1" type="text/css"/>';
        $out .= '<link rel="stylesheet" type="text/css" href="styles/css/minimal/screen/custom/prototip.css" />';
        return $out;
    }

    public function showFeatureIsClosed()
    {
        $this->page->title='Feature Closed - Bewelcome' ;
        ob_start();
        require 'templates/featureclosed.php';
        return ob_get_clean();
    } // end of showFeatureIsClosed()

}
