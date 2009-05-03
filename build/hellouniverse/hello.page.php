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
class HellouniversePage extends RoxPageView  /* HelloUniversePage doesn't work! */
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get the translation module
        $words = $this->getWords();
        
        echo '
<h3>The hello universe (advanced) middle column</h3>
<h4>Using the class <em>"'.get_class($this).'"</em>.</h4>
<ul>
    <li>Simple version in <a href="hellouniverse">hellouniverse</a>.</li>
    <li>More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!</li>
    <li>With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a></li>
    <li>See a page with all used css classes <a href="hellouniverse/styles">hellouniverse/styles</a></li>
</ul>

<p>
A translated word (wordcode "Groups"):
'.$words->getFormatted('Groups').'
</p>
        ';
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




?>
