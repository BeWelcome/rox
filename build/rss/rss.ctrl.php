<?php
/** 
 * RSS controller
 * 
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class RssController extends RoxControllerBase
{
    public function index($args = false)
    {
        // First check if the feature is closed
        if ($this->_session->get("Param")->RssFeedIsClosed!='No') {
            PPHP::PExit(); // To do find a better exit
            break ;
        } // end of test "if feature is closed" 


        $request = $args->request;
        
        $model = new RssModel();
        
        // $request[0] is 'rss', anyway. Don't need to do any ifs and switches for that.
        
		if (isset($request[1])) {
			$ss="Querying RSS with " ;
			$ss=$ss."[".$request[1]."]" ;
			if (isset($request[2])) {
				$ss=$ss."[".$request[2]."]" ;
				if (isset($request[3])) {
					$ss=$ss."[".$request[3]."]" ;
					if (isset($request[4])) {
						$ss=$ss."[".$request[4]."]" ;
					}
				}
			}
			MOD_log::get()->write($ss,"rss") ; 				
		}

		switch (isset($request[1]) ? $request[1] : false) {
		
        
            /**
             * thread/tagid
             * thread/tagname (TODO?)
             */            
            case 'thread':
            case 'threads':
            case 'forumthreads':
                // request is ..bw.org/rss/thread, or ..bw.org/rss/thread/*
                
                // check if $request[2] identifies a thread id.
                if (!isset($request[2])) {
                    // can't show a thread rss, because the thread id is not given.
                    // show a global rss instead
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                } else if (!$model->getThreadFeed($request[2])) {
                    // an id (or name?) was given, but there is no thread with that id
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                } else { //http://localhost/bw/htdocs/rss/thread/1
                    // cool, found one!!
                    //$model->getThreadFeed($request[2]);
                    $page = new PageWithThreadRSS();
                }
                break;
                
            /**
             * tag/tagid
             * tag/tagname (TODO?)
             */
            case 'tag':
            case 'tags':
                // request is ..bw.org/rss/tag, or ..bw.org/rss/tag/*
                if (!isset($request[2])) {
                    // can't show a thread rss, because the thread id is not given.
                    // show a global rss instead
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                    
                } else if (!$model->getTagFeed($request[2])) {
                    // no such tag found..
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                    
                } else {
                    //$rss = $model->getTagFeed($request[2]);
                    $model->getTagFeed($request[2]);
                    $page = new PageWithTagRSS();
                }
                break;
            case 'meeting':
            case 'meetings':
                if(!$model->getTagFeed($request[1])) {
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                } else {
                    $page = new PageWithTagRSS();
                }
                break;

            default:
                // request is ..bw.org/rss/*, but none of the above

                $page = new RssOverviewPage();
        }
        //TODO: request[1] & request[2] exist = rss/thread/345, rss/tag/help or so
        
        $page->setModel($model);
        PVars::getObj('page')->output_done = true;
        
        if (isset($args->get['debug']) && MOD_right::get()->hasRight('debug')) {
            $page->debug = true;
        }
        
        return $page;
    }
        
}
?>