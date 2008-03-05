<?php 

/**
 * Hello universe view, a first simple version.
 * We redefine the methods of RoxPageView to configure this page.
 * We don't need to redefine all the methods, we already get something for an empty subclass of RoxPageView.
 * For the start, we only redefine the content of the main column.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseSimplePage extends RoxPageView
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3() {
        ?>
        <h3>The hello universe middle column</h3>
        using the class HellouniverseSimplePage.<br>
        More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
        With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        <?php
    }
}



//-------------------------------------------------------------------------------


/**
 * Hello universe view.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we make some more decorations.
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniversePage extends RoxPageView
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3() {
        ?>
        <h3>The hello universe (advanced) middle column</h3>
        using the class HellouniversePage.<br>
        Simple version in <a href="hellouniverse">hellouniverse</a>.<br>
        More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
        With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        <?php
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
    protected function teaserContent() {
		echo 'The hello universe teaser';
	}
	
	/**
	 * configure the page title (what appears in your browser's title bar)
	 * @return string the page title
	 */
    protected function getPageTitle() {
        return 'Hello Unviverse!';
    }
    
    /**
     * configure the sidebar
     */
	protected function leftSidebar()
	{
	    echo 'Hello Universe Sidebar';
	}
}


//-------------------------------------------------------------------------------

/**
 * Hello universe tabbed view.
 * We redefine the methods of RoxPageView to configure this page.
 * Here we add a submenu.
 * All the rest we get from HellouniversePage via inheritance
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class HellouniverseTabbedPage extends HellouniversePage
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
                'hellouniverse/tab1',  // relative url
                'HellouniverseTab1'  // word code for translation
            ),
            array('tab2', 'hellouniverse/tab2', 'HellouniverseTab2'),
            array('tab3', 'hellouniverse/tab3', 'HellouniverseTab3')
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
    protected function column_col3() {
        ?>
        <h3>The hello universe (tabbed) middle column</h3>
        Using the class HellouniverseTabbedPage.<br>
        Simple version in <a href="hellouniverse">hellouniverse</a>.<br>
        More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
        With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        <?php
    }
}



?>