<?php


/**
 * Hello universe page.
 * This is a base class for other pages in the same application.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package newsletter
 * @author JeanYves
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class NewsletterPage extends RoxPageView  
{
    public function __construct($_Data) {
        $this->Data=$_Data;
    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
		echo "<p>" ;
		foreach ($this->Data as $OneLetter) {
			echo "<a href='".$OneLetter->Name."'>",$this->getWords()->get('BroadCast_Title_'.$OneLetter->Name)."</a></br>" ;
		}
		echo "</p>" ;
 
    }
    
    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {

    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo 'BeWelcome news letters';
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
        return $this->getWords()->getFormatted('NewsLetters');
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {
        
    }
}




?>
