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
 * $page = new PageWithTagRSS();
 * $page->posts = ...;  // an array of forum posts from the database
 */
class PageWithTagRSS extends PageWithGivenRSS
{

    /**
     */
    protected function showHeader()
    {
    	//echo "<pre>";
    	//print_r($this->posts[0]);
    	
		$post_id = $this->posts[0]->id;
		$tag_id = $this->posts[0]->tagid;
		$tag_name = $this->posts[0]->tagname;
		$site_link = "forums/t$tag_id-$tag_name";
    	echo $this->formatFeedTitle("BeWelcome Forum Feed for tag '$tag_name'", "tag/".$tag_id.'-'.$tag_name, "");
    }

    
    /**
     */
    protected function showItem($post)
    {
        $thread_id = $post->threadid;
        $post_id = $post->id;
        $post_link = "forums/s".$post->threadid."/#".$post->id;
        echo $this->formatFeedItem($post->title, $post->message, $post->create_time, $post_link);
    }
}



?>