<?php
/**
 * Overview of RSS feeds
 * @package rss
 * @author Kasper
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 * 
 */

class RssOverviewPage extends RoxPageView {

    protected function teaserHeadline() {
        echo "Overview of RSS feeds";
        
    }

    protected function leftSidebar() {
    }

    protected function column_col3() {
        
        ?>
        <ul>
            <li><a href="rss/forumthreads">forum RSS feed</a></li>
        </ul>
        <?php
    }
}

?>
