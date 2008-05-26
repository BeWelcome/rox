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
 * $page = new PageWithForumRSS();
 * $page->posts = ...;  // an array of forum posts from the database
 */
class PageWithForumRSS extends PageWithGivenRSS
{

    
    /**
     * This function could be overwritten in a subclass...
     *
     */
    protected function showItem($post)
    {
        $thread_id = $post->threadid;
        $post_id = $post->id;
        
        $post_link = "forums/s".$post->threadid."/#".$post->id;
        echo $this->formatFeedItem($post->title, $post->message, $post->create_time, $post_link);
    }
    
    
    /**
     * This function could be overwritten in a subclass...
     *
     */
    protected function showHeader()
    {
		echo $this->formatFeedTitle("", "", "Feed for the BeWelcome forum"); 
    }
}



?>