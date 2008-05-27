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
    protected function showHeader()
    {
    	$thread_id = $this->posts[0]->threadid;
    	$thread_title = $this->posts[0]->title;
		echo $this->formatFeedTitle("BeWelcome Forum Feed for Thread '$thread_title'", "forums/s$thread_id-$thread_title", ""); 
    }
    
        
    /**
     */
    protected function showItem($post)
    {
        $thread_id = $post->threadid;
        $post_id = $post->postid;
        $post_link = "forums/s".$post->threadid."/#".$post_id;

        echo $this->formatFeedItem($post->title, $post->message, $post->create_time, $post_link);
    }
    
}



?>