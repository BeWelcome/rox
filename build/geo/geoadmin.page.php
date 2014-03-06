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
			Generate new geo tables based on the data in the address and blog tables: <input type="hidden" name="action"/ value="renew"> 	<input type="submit" class="button" value="Renew"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            } elseif ($mem_redirect->renew) {
            echo '
			<p>
			The geo tables have been renewed: Added '.$mem_redirect->counter['members'].' unique geoname Ids taken from the address table and '.$mem_redirect->counter['blog'].' unique Ids taken from the blog table.
			</p>
           ';
		   }

        echo '
			<p>
			<form method="POST" action="'.$page_url.'">
			'.$this->layoutkit->formkit->setPostCallback('GeoController', 'AdminCallback').'
			Recalculate how many items (blogs, members) we have in each region: <input type="hidden" name="action"/ value="recount"> 	<input type="submit" class="button" value="Update"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            } elseif ($mem_redirect->recount) {
            echo '
			<p>
			The counters in geo_usage have been recalculated.
			</p>
           ';
		   }

        echo '
			<p>
			<form method="POST" action="'.$page_url.'">
			'.$this->layoutkit->formkit->setPostCallback('GeoController', 'AdminCallback').'
			Data by Id: <input type="hidden" name="action"/ value="byId"> 	<input type="text" name="id"><input type="submit" class="button" value="byId"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            } elseif ($mem_redirect->recount) {
            echo '
			<p>
			</p>
           ';
		   }

        echo '
			<p>
			<form method="POST" action="'.$page_url.'">
			'.$this->layoutkit->formkit->setPostCallback('GeoController', 'AdminCallback').'
			Get updates from Geonames.org: <input type="hidden" name="action"/ value="getUpdates"> <input type="submit" class="button" value="getUpdates"/>
			</form>
			</p>
        ';
		
		if (!$mem_redirect = $this->layoutkit->formkit->getMemFromRedirect()) {
            } elseif ($mem_redirect->getUpdates1 && $mem_redirect->getUpdates2) {
            echo '
			<p>
			update successful
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

    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }
}




?>