<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package geo
 * @author Philipp
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class GeoAdminPage extends RoxPageView 
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get the translation module
        $words = $this->getWords();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);        
        
		
		echo '
			<p>
			You are about to remove and readd the information for all locations (geonameids). Address and blog_content tables will be used as reference all ids used somewhere else might get lost. The process will take some time, so be patient.
			</p>
        ';
    
	
	//	$model = new GeoModel();
	//	$merge = $model->addressesToGeonames();

	//$flush = $model->deleteLinkList();
	//$model->getTree();
	

        
        echo '
			<p>
			<form method="POST" action="'.$page_url.'">
			'.$this->layoutkit->formkit->setPostCallback('GeoController', 'AdminCallback').'
			From: <input type="hidden" name="action"/ value="renew"> 	<input type="submit" value="Renew"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            } else {
            echo '
			<p>
			The geo tables have been renewed: Added '.$mem_redirect->counter['members'].' unique geoname Ids taken from the address table and '.$mem_redirect->counter['blog'].' unique Ids taken from the blog table.
			</p>
           ';
		   }


	}
    
    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo 'Geo Admin';
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