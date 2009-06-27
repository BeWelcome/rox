<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class LinkRebuildPage extends LinkPage  /* HelloUniversePage doesn't work! */
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get the translation module
        $words = $this->getWords();
        
        echo '
<h3>Admin Links</h3>

<p>
Rebuildin the Tree of links
</p>
        ';
    
	$type = PRequest::get()->request;
	
	$model = new LinkModel();
	switch($type[1]) {
	    case 'rebuild':	
            //$model->rebuildLinks();
            break;
        case 'rebuildmissing':
            $model->rebuildMissingLinks();
            break;
    }


	
	}
    
    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo 'Update links';
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return 'Building tree !';
    }
    

}




?>