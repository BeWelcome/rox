<?php


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
    protected function column_col3()
    {
        // get translation module
        $words = $this->getWords();
        
        echo '
<h3>The hello universe (tabbed) middle column</h3>
<p>
Using the class "'.get_class($this).'".<br>
Simple version in <a href="hellouniverse">hellouniverse</a>.<br>
More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
</p>
<br>
<p>
A translated word (wordcode "Groups"):
'.$words->getFormatted('Groups').'
</p>
        ';
    }
}




?>