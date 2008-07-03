<?php


/**
 * AboutTheideaPage
 *
 * @package about
 * @author design: Michael Dettbarn (bw: lupochen), structural refactoring: Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutTheideaPage extends AboutBasePage
{
    protected function getPageTitle() {
        $w = $this->getWords();
        // getSilent is the same as getBuffered,
        // but the name makes more sense.
        return $w->getSilent('AboutUsPage').' - '.$w->getSilent('AboutUs_TheIdea');
    }

    protected function getCurrentSubpage() {
        return 'theidea';
    }
    
    protected function column_col3() {
        require_once "magpierss/rss_fetch.inc";    
        require 'templates/about.php';
    }
}


?>