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
class MediawikiPage extends RoxPageView
{
    protected function getPageTitle()
    {
        return $this->headline . " - " . $this->wikiname;
    }
    
    
    protected function teaserHeadline()
    {
        echo $this->headline . " - " . $this->wikiname;
    }
    
    
    /**
     * content of the middle column - this is the most important part
     */
    protected function columnsArea()
    {
        // get the translation module
        $words = $this->getWords();
        
        $contents = file_get_contents($this->inclusion_url);
        //TODO: replace URLs, add edit and historz linkz, caching, fix redirect, css
        
        echo '
<div style="margin: 20px">'.$contents.'</div>'
        ;
        
    }

}




?>