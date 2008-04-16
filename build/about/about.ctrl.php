<?php

/**
 * Aboutus controller
 *
 * @package about
 * @author Andreas (lemon-head)
 * @copyright hmm
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class AboutController extends RoxControllerBase
{
    public function index()
    {
        $request = PRequest::get()->request;
        
        if (!isset($request[0])) {
            // then who activated the about controller?
            $page = new AboutTheidea();
        } else if ($request[0] != 'about') {
            $page = $this->_getPageByKeyword($request[0]);
        } else if (!isset($request[1])) {
            $page = new AboutTheidea();
        } else {
            $page = $this->_getPageByKeyword($request[1]); 
        }
        return $page;
    }    
    
    private function _getPageByKeyword($keyword)
    {   
        switch ($keyword) {
            case 'thepeople':
                return new AboutThepeoplePage();
            case 'getactive':
                return new AboutGetactivePage();
            case 'bod':
            case 'help':
            case 'terms':
            case 'impressum':
            case 'affiliations':
            case 'privacy':
                $page = new AboutGenericPage($keyword);
                $page->setModel(new AboutModel());
                return $page;
            case 'stats':
			case 'statistics':
                $page = new AboutStatisticsPage();
                $page->setModel(new StatsModel());
                return $page;
			case 'faq':
				$this->redirect('bw/faq.php');
				return false;
			case 'feedback':
            case 'contact':
            case 'contactus':
			    $this->redirect('bw/feedback.php');
			    return false;
			case 'idea':
            case 'theidea':
            default:
                return new AboutTheideaPage();
        }
    }
}


?>