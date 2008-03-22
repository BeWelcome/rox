<?php 

/**
 * Meetings view mockup 
 * We redefine the methods of RoxPageView to configure this page.
 * We don't need to redefine all the methods, we already get something for an empty subclass of RoxPageView.
 * For the start, we only redefine the content of the main column.
 *
 * @package meeting
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MeetingsSimplePage extends RoxPageView
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3() {
        ?>
        <h3>The meeting middle column</h3>
        using the class MeetingSimplePage.<br>
        More beautiful in <a href="meetings/advanced">meetings/advanced</a>!<br>
        With tabs in <a href="meetings/tab1">meetings/tab1</a>!
        <?php
    }
}



//-------------------------------------------------------------------------------


/**
 * Meetings view.
 *
 * @package meetings
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MeetingsPage extends RoxPageView
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        $meetings = $this->getModel()->getMeetings();
		
        foreach($meetings as $m) {
        	$d = $m->getData();
            ?><div>
            <a href="meeting/"><?php echo 
					" <b>WHEN:</b>> ".$d['date'].
					" <b>WHERE:</b> ".$d['coordinates'].
					" <b>WHAT:</b> ".$d['title']." ".$d['info'] ?></a>
            </div><?php
        }
        ?>
        </div>
                
        <?php
    }
    
    /**
     * which item in the top menu should be activated when showing this page?
     * Let's use the 'getanswers' menu item for this one.
     * @return string name of the menu
     */
    protected function getTopmenuActiveItem() {
        return 'getanswers';
        //return 'meetings';  ?
    }

    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserContent() {
		echo 'Meetings teaser';
	}
	
	/**
	 * configure the page title (what appears in your browser's title bar)
	 * @return string the page title
	 */
    protected function getPageTitle() {
        return 'Meetings';
    }
    
    /**
     * configure the sidebar
     */
	protected function leftSidebar()
	{
	    echo 'Meetings Sidebar';
	}
}


//-------------------------------------------------------------------------------

/**
 * Meetings tabbed view.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we add a submenu.
 * All the rest we get from HellouniversePage via inheritance
 *
 * @package meetings
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MeetingsTabbedPage extends MeetingsPage
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
            array(
                'tab1',  // name of the menu item
                'meetings/tab1',  // relative url
                'MeetingsTab1'  // word code for translation
            ),
            array('tab2', 'meetings/tab2', 'MeetingsTab2'),
            array('tab3', 'meetings/tab3', 'MeetingsTab3')
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
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get translation module
        $words = $this->getWords();
        ?>
        <h3>The meetings (tabbed) middle column</h3>
        Using the class MeetingsTabbedPage.<br>
        Simple version in <a href="meetings">meetings</a>.<br>
        More beautiful in <a href="meetings/advanced">meetings/advanced</a>!<br>
        With tabs in <a href="meetings/tab1">meetings/tab1</a>!
        <br>
        <br>
        A translated word (wordcode 'Meetings'):
        <?=$words->getFormatted('Meetings') ?>
        <?php
    }
}
?>