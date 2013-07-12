<?php


/**
 * AboutStatisticsPage
 *
 * @package about
 * @author design: Phillipp, structural refactoring: Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutStatisticsPage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome: Statistics';
    }

    protected function getCurrentSubpage() {
        return 'stats';
    }

    protected function teaserHeadline() {
        // TODO: why do we allow spaces in word codes?
        echo $this->getWords()->get('BW Statistics');
    }

    protected function column_col3() {
        require 'templates/stats.php';
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/stats.css';
        return $stylesheets;
    }
}
