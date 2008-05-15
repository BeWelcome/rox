<?php


/**
 * AboutThepeoplePage
 *
 * @package about
 * @author design: Michael Dettbarn (bw: lupochen). structural refactoring: Andreas (lemon-head).
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutThepeoplePage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome: The People *';
    }
    
    protected function getCurrentSubpage() {
        return 'thepeople';
    }
    
    protected function column_col3() {
        require TEMPLATE_DIR.'apps/rox/thepeople.php';
    }
}


?>