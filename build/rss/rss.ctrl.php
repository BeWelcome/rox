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
        $request = $args->request;
        
        $model = new RssModel();
        
        // $request[0] is 'rss', anyway. Don't need to do any ifs and switches for that.
        
        if (!isset($request[1])) {
            // request was ..bw.org/rss
            $rss = $model->getForumFeed();
        } else switch($request[1]) {
        	
            case 'thread':
                // request is ..bw.org/rss/thread, or ..bw.org/rss/thread/*
                
                // check if $request[2] identifies a thread id.
                if (!isset($request[2])) {
                    // can't show a thread rss, because the thread id is not given.
                    // show a global rss instead
                    $rss = $model->getForumFeed();
                } else if (!$rss = $model->getThreadFeed($request[2])) {
                    // an id (or name?) was given, but there is no thread with that id
                    $rss = $model->getForumFeed();
                } else { //http://localhost/bw/htdocs/rss/thread/1
                    // cool, found one!!
                    $rss = $model->getThreadFeed($request[2]);
                }
                break;
                
            case 'tag':
                // request is ..bw.org/rss/tag, or ..bw.org/rss/tag/*
                if (!isset($request[2])) {
                    // can't show a thread rss, because the thread id is not given.
                    // show a global rss instead
                    $rss = $model->getForumFeed();
                    //$page->setPosts($model->getPosts());
                    
                } else if (!$rss = $model->getTagFeed($request[2])) {
                    // no such tag found..
                    $rss = $model->getForumFeed();
                    
                } else {
                	$rss = $model->getTagFeed($request[2]);
                }
                break;
                
            case 'meeting':
            case 'meetings': 
            	$rss = $model->getTagFeed("Meetings");
            	break;
            	
            default:
                // request is ..bw.org/rss/*, but none of the above
            	$rss = $model->getForumFeed();
        }
        //TODO: request[1] & request[2] exist = rss/thread/345, rss/tag/help or so
        
        // create the $page object, and give it the chosen $rss string.
        $page = new PageWithGivenRSS();
        $page->content_string = $rss;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
        
}
?>