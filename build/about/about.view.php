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

class AboutGenericView extends AboutPageView
{
    public function __construct($pagename) {
        $this->_pagename = $pagename;
    }
    
    protected function getPageTitle() {
        $titles = array(
            'bod' => 'Board of Directors',
            //'getactive' => 'Get Active',
            'help' => 'Help',
            'terms' => 'Terms of Use',
            'impressum' => 'Impressum',
            'affiliations' => 'Affiliations',
            'privacy' => 'Privacy policy'
        ); 
        return 'About BeWelcome: '.$titles[$this->_pagename];
    }
    
    protected function getCurrentSubpage() {
        return $this->_pagename;
    }
    
    protected function column_col3() {
        if (!$model = $this->getModel()) {
            echo 'no model in AboutGenericView';
            $isvolunteer = false;
        } else if (!isset($_SESSION['IdMember'])) {
            $isvolunteer = false;
        } else {
            $isvolunteer = $this->getModel()->isVolunteer($_SESSION['IdMember']);
        }
        require TEMPLATE_DIR.'apps/rox/'.$this->_pagename.'.php';
    }
}


class StatsView extends AboutPageView
{
    protected function getPageTitle() {
        return 'About BeWelcome: Statistics';
    }
    
    protected function getCurrentSubpage() {
        return 'stats';
    }
    
    protected function column_col3() {
        $countryrank = $this->getModel()->getMembersPerCountry();
        $loginrank = $this->getModel()->getLastLoginRank();
        $loginrankgrouped = $this->getModel()->getLastLoginRankGrouped();        
        $statsall = $this->getModel()->getStatsLogAll();
        $statslast = $this->getModel()->getStatsLog2Month();
        
        require TEMPLATE_DIR.'apps/rox/stats.php';
    }
}



?>