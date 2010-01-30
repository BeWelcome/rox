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
class OneNewsLetterPage extends RoxPageView  /* HelloUniversePage doesn't work! */
{
    /**
    Constructor

    @$_Data has been previously filled with the dynamic data to display

    **/
    public function __construct($_Data) {
        $this->Data=$_Data;
    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
		if (empty($_SESSION['Username'])) {
			echo '<p>',$this->getWords()->get('BroadCast_Body_'.$this->Data->LetterName,'member'),'</p>' ;
		}
		else {
			echo '<p>',$this->getWords()->get('BroadCast_Body_'.$this->Data->LetterName,$_SESSION['Username']),'</p>' ;
			if ($this->Data->CountSent > 0) echo '<p>Sent to '.$this->Data->CountSent,' members</p>' ;
			if ($this->Data->CountToSend > 0) 
			echo '<p>Still to be sent to '.$this->Data->CountToSend,' members</p>' ;
		}
    }
    
    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserHeadline() {
        echo $this->getWords()->get('BroadCast_Title_'.$this->Data->LetterName);
    }
    
    /**
     * configure the page title (what appears in your browser's title bar)
     * @return string the page title
     */
    protected function getPageTitle() {
		$ss="" ;
        $ss = $this->getWords()->get('BroadCast_Title_'.$this->Data->LetterName);
		return($ss) ;
    }
    
    /**
     * configure the sidebar
     */
    protected function leftSidebar()
    {

    }
} // end of OneNewsLetterPage

?>
