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
        $controlkit = new ReadWriteObject();
        
        $model = new RssModel();
        $page = new PageWithXML_parameterized();
        
        //echo "Rss control";
        
        if (!isset($request[0])) {
            // this should never happen!
            //$this->??
        } else switch($request[0]) {
        	
            case 'rss':
            	//$rss = $model->getThreadFeed(1);
            	$rss = $model->getForumFeed();
            	$page->model = $model;
            	$page->content_string = $rss;
            	//echo "<br />rss<pre>: ";
            	//print_r($rss);
            	            	
            	break;
        	
        	
            default:
            	//$page = $page->model->getTagFeed();
            	//$rss = $model->getTagFeed("sometag");
            	//$page->content_string = $rss;
            	
            	$rss = $model->getThreadFeed(1);
            	$page->model = $model;
            	$page->content_string = $rss;
            	
            	break;
                
        }
        //TODO: request[1] & request[2] exist = rss/thread/345, rss/tag/help or so
        
        //$page->model = $model;
        PVars::getObj('page')->output_done = true;
        return $page;
    }
        
}
?>