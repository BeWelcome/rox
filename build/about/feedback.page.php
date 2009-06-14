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
class FeedbackPage extends AboutBasePage
{
  
    protected function getCurrentSubpage() {
        return 'about';
    }
    
    protected function getSubmenuActiveItem() {
        return 'contactus';
    }

}


?>
