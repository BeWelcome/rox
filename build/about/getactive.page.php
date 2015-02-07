<?php


/**
 * AboutGetactivePage
 *
 * @package about
 * @author design: Michael Dettbarn (bw: lupochen), structural refactoring: Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutGetactivePage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome: Get Active *';
    }
    
    protected function getCurrentSubpage() {
        return 'getactive';
    }
    
    protected function column_col3() {
        require 'templates/getactive.php';
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/getactive.css';
        return $stylesheets;
    }
}

