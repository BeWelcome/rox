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
        return 'About BeWelcome - The Idea *';
    }
    
    protected function getCurrentSubpage() {
        return 'theidea';
    }
    
    protected function column_col3() {
        require_once "magpierss/rss_fetch.inc";    
        require TEMPLATE_DIR.'apps/rox/about.php';
    }
}


?>