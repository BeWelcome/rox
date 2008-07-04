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
     */
    protected function showHeader()
    {
		echo $this->formatFeedTitle("BeWelcome Forum Feed", "forums/", "Feed for the BeWelcome forum"); 
    }
    
        
    /**
     */
    protected function showItem($post)
    {
        $thread_id = $post->threadid;
        $post_id = $post->postid;
        
        $post_link = "forums/s".$post->threadid."/#".$post_id;
        //print_r($post);
        //echo "LINK1: ".$post_link." ".$post->postid;
        echo $this->formatFeedItem($post->title, $post->message, $post->create_time, $post_link, $post->author);
    }

}



?>