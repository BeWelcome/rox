<?php


/**
 * About page
 * Base class for other pages in about application
 *
 * @package about
 * @author design: Michael Dettbarn (bw: lupochen), structural refactoring: Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutBasePage extends PageWithActiveSkin
{
    protected function teaserHeadline() {
        echo $this->getWords()->AboutUsPage;
    }
    
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }
    
    protected function getPageTitle() {
        return $this->words->get('AboutUsPage');
    }
    
    protected function leftSidebar()
    {
        $currentSubPage = $this->getCurrentSubPage();
        require 'templates/aboutbar.php';
    }
    
    protected function getSubmenuItems()
    {
        $words = $this->getWords();
        return array(
            array('about', 'about', $words->getBuffered('AboutUsSubmenu')),
            array('faq', 'about/faq', $words->getBuffered('Faq')),
            array('contactus', 'about/feedback', $words->getBuffered('ContactUs')),
        );
    }
    
    protected function getSubmenuActiveItem() {
        return 'about';
    }
}


?>
