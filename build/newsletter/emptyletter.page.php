<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

class EmptyLetterPage extends RoxPageView  /* HelloUniversePage doesn't work! */
{
    /**
    Constructor

    @$_Data has been previously filled with the dynamic data to display

    **/
    public function __construct($LetterName) {
        $this->LetterName=$LetterName;
    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        
    }
    
    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return ;
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo '<h3>No such letter '.$this->LetterName.'</h3>' ;
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        $ss = 'No such letter '.$this->LetterName;
		return($ss) ;
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {

    }
} // end of EmptyLetterPage




?>
