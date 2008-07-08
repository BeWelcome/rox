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
    protected function column_col3()
    {
        echo '
<h3>The hello universe middle column</h3>
using the class HellouniverseSimplePage.<br>
More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        ';
    }
}




?>