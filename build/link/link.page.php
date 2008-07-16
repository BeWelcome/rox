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
class LinkPage extends RoxPageView  
{
	
	
    
    /**
     * the constructor sets the tab name.
     *
     * @param string $tabname
     */
    public function __construct($tabname) {
        $this->_tabname = $tabname;
    }
	
	
	    /**
     * define the items of the submenu
     *
     * @return array items of the submenu
     */
    protected function getSubmenuItems() {
        return array(
            array('showlink', 'link/showlink', 'ShowLinks'),
            array('showfriends', 'link/showfriends', 'ShowFriends'),			
		    array('display', 'link/display', 'linkDisplay'),
            array(
                'update',  // name of the menu item
                'link/update',  // relative url
                'linkUpdate'  // word code for translation
            ),


        );
    }
	
	
    /**
     * define the name of the active menu item
     *
     * @return string name of the menu item
     */
    protected function getSubmenuActiveItem() {
        return $this->_tabname;
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo 'Link it';
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return 'Link it!';
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        echo 'Link Sidebar';
    }
}




?>