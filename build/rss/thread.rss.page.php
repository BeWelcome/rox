<?php
/**
 * RSS page
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 * 
 * For validating feeds: 
 * http://feedvalidator.org/
 * 
 * usage:
 * $page = new PageWithThreadRSS();
 * $page->posts = ...;  // an array of forum posts from the database
 */
class PageWithThreadRSS extends PageWithGivenRSS
{
    
    /**
     */
    protected function showItem($post)
    {
        $thread_id = $post->threadid;
        $post_id = $post->id;
        $post_link = "forums/s".$post->threadid."/#".$post->id;
        
        echo $this->formatFeedItem($post->title, $post->message, $post->create_time, $post_link);
    }
    
    
    /**
     */
    protected function showHeader()
    {
		echo $this->formatFeedTitle("Thread", "thread/".$this->posts[0]->threadid, ""); 
    }
}



?>