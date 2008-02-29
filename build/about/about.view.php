<?php 
/**
 * Translate view
 *
 * @package about
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutPageView extends RoxPageView
{
    protected function teaserContent() {
		require TEMPLATE_DIR.'apps/rox/teaser_getanswers.php';
	}
	
    protected function getPageTitle() {
        return 'About BeWelcome *';
    }
    
	protected function column_col1()
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

class AboutTheidea extends AboutPageView
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

class AboutThepeople extends AboutPageView
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

class AboutGetactive extends AboutPageView
{
    protected function getPageTitle() {
        return 'About BeWelcome: Get Active *';
    }
    
    protected function getCurrentSubpage() {
        return 'getactive';
    }
    
    protected function column_col3() {
        require TEMPLATE_DIR.'apps/rox/getactive.php';
    }
}



?>