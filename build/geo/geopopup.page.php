<?php


/**
 * Geo Popup Page
 * This is a popup that is beeing used as an alternative to javascript based geo location selection
 * We redefine the methods of RoxPageView to configure this page.
 *
 * @package geo
 * @author Micha (bw: lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class GeoPopupPage extends RoxPageView 
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function body()
    {
        // get the translation module
        $words = $this->getWords();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);   
		$request = PRequest::get()->request;
		
		$callbacktag = $this->layoutkit->formkit->setPostCallback('GeoController', 'SelectorCallback');
		
        if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            $locations_print = '';
        } elseif ($mem_redirect->location) {
            $Geo = new GeoController;
            $locations_print = $Geo->GeoSearch($mem_redirect->location,40,false, $callbacktag);
        } else {
            $Geo = new GeoController;
            $locations_print = $Geo->GeoSearch(' ',40,false, $callbacktag);
		}
		// Just for testing:
		// if ($this->_session->has( 'GeoVars' ) var_dump($this->_session->get('GeoVars'));
		// if ($this->_session->has( 'GeoVars']['geonamename' ) var_dump($this->_session->get('GeoVars']['geonamename'));
		// if (isset($request[2]) && $request[2] == 'save' && $mem_redirect->geolocation) {
			// $geolocation = $mem_redirect->geolocation;
			// list($geonameid, $geonamename) = preg_split('/[//]/', $geolocation);
			// $this->_session->set( 'SignupBWVars']['geonameid', $geonameid )
			// $this->_session->set( 'SignupBWVars']['geonamename', $geonamename )
			// print 'GEO SET';
		// } else {
			// print 'GEO NOT SET';
		// }
        
		require 'templates/popup.php';
	}
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return 'Geo!';
    }
    

}




?>
