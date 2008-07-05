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
    public function index($args = false)
    {
        $request = $args->request;
                
        if (!isset($request[0])) {
            // then who activated the about controller?
            $page = new AboutTheideaPage();
        } else if ($request[0] != 'about') {
            $page = $this->_getPageByKeyword($request[0], isset($request[1]) ? $request[1] : false);
        } else if (!isset($request[1])) {
            $page = new AboutTheideaPage();
        } else {
            $page = $this->_getPageByKeyword($request[1], isset($request[2]) ? $request[2] : false); 
        }
        return $page;
    }    
    
    private function _getPageByKeyword($keyword, $keyword_2)
    {   
        switch ($keyword) {
            case 'thepeople':
                return new AboutThepeoplePage();
            case 'getactive':
                return new AboutGetactivePage();
            case 'newsletters':
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
			//case 'faq':
			case 'faqs':
				$this->redirect('bw/faq.php');
				return false;
			case 'feedback':
            case 'contact':
            case 'contactus':
            case 'support':
			    $this->redirect('bw/feedback.php');
			    return false;
            case 'faq':
                $model = new AboutModel;
                $faq_categories = $model->getFaqsCategorized();
                if ($faq_section = $model->getFaqSection($keyword_2)) {
                    $page = new AboutFaqsectionPage;
                    $page->faq_section = $faq_section;
                    $page->key = $keyword_2;
                } else {
                    $page = new AboutFaqPage;
                }
                $page->faq_categories = $faq_categories;
                return $page;
            case 'idea':
            case 'theidea':
            default:
                return new AboutTheideaPage();
        }
    }
}


?>