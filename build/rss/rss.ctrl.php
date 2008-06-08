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
            $model->getForumFeed();
            $page = new PageWithForumRSS();
        } else switch ($request[1]) {
            
            /**
             * thread/tagid
             * thread/tagname (TODO?)
             */            
            case 'thread':
            case 'threads':
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


            /**
             * blog
             * blog/tag/tagid                
             * blog/tag/tagname 
             * blog/author/username
             */
            case 'blog':
                if (!isset($request[2])) {
                    $model->getBlogFeed();
                    $page = new PageWithBlogRSS();
                } else if ($request[2] == 'tag') {
                    if (!isset($request[3])) {
                        $model->getBlogFeed();
                        $page = new PageWithBlogRSS();
                    } else if (!$model->getBlogFeedByTag($request[3])) {
                        // no such found..
                        $model->getBlogFeed();
                        $page = new PageWithBlogRSS();
                        
                    } else {
                        $model->getBlogFeedByTag($request[3]);
                        $page = new PageWithBlogRSS();
                    }                    
                } else if ($request[2] == 'author') {
                    if (!isset($request[3])) {
                        $model->getBlogFeed();
                        $page = new PageWithBlogRSS();
                    } else if (!$model->getBlogFeedByAuthor($request[3])) {
                        $model->getBlogFeed();
                        $page = new PageWithBlogRSS();
                        
                    } else {
                        $model->getBlogFeedByAuthor($request[3]);
                        $page = new PageWithBlogRSS();
                    }                                            
                }                        
                break;
            case 'meeting':
                if (!$model->getTagFeed("meeting")) {
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                } else {
                    $page = new PageWithTagRSS();
                }
                break;
                
            case 'meetings':
                if(!$model->getTagFeed("meetings")) {
                    $model->getForumFeed();
                    $page = new PageWithForumRSS();
                } else {
                    $page = new PageWithTagRSS();
                }
                break;

            default:
                // request is ..bw.org/rss/*, but none of the above
                $model->getForumFeed();
        }
        //TODO: request[1] & request[2] exist = rss/thread/345, rss/tag/help or so
        
        $page->setModel($model);
        PVars::getObj('page')->output_done = true;
        return $page;
    }
        
}
?>