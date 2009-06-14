<?php


/**
 * Explore page
 *
 * @package explore
 * @author: Micha (bw: lupochen)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ExplorePage extends RoxPageView
{

    protected function getTopmenuActiveItem() {
        return 'explore';
    }

    protected function getPageTitle() {
        return 'Explore BeWelcome *';
    }

    protected function teaserContent() {
        require 'templates/teaser_explore.php';
    }

    protected function getColumnNames ()
    {
        return array('col3');
    }

    protected function leftSidebar()
    {
        $currentSubPage = $this->getCurrentSubPage();
        require 'templates/aboutbar.php';
    }

    protected function column_col3() {
        require 'templates/body_explore.php';
    }
}


?>
