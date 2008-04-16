<?php


/**
 * About page
 * Base class for other pages in about application
 *
 * @package about
 * @author design: Micha, structural refactoring: Andreas (lemon-head)
 * @copyright hmm what to write here
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutBasePage extends PageWithActiveSkin
{
    
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    protected function teaserContent() {
        require TEMPLATE_DIR.'apps/rox/teaser_getanswers.php';
    }
    
    protected function getPageTitle() {
        return 'About BeWelcome *';
    }
    
    protected function leftSidebar()
    {
        $currentSubPage = $this->getCurrentSubPage();
        require TEMPLATE_DIR.'apps/rox/aboutbar.php';
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        $items[] = array('about', 'about', 'AboutUsSubmenu');
        $items[] = array('faq', 'bw/faq.php', 'Faq');
        $items[] = array('contactus', 'bw/feedback.php', 'ContactUs');
        return $items;
    }
    
    protected function getSubmenuActiveItem() {
        return 'about';
    }
}


?>