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
 * Preview in Google reader: 
 * http://www.google.com/reader/view/feed/http://82.181.155.106/bw/htdocs/rss/blog/tag/1
 * 
 * usage:
 * $page = new PageWithTagRSS();
 * $page->posts = ...;  // an array of forum posts from the database
 */
class PageWithBlogRSS extends PageWithGivenRSS
{
	
    /**
     */
    protected function showHeader()
    {
    	$title = "BeWelcome Blog Feed";
    	$link = "blog/";
    	
    	if(isset($this->posts[0]->blog_tag_id)) {
			$title = "BeWelcome Blog Feed for tag ".$this->posts[0]->name;
    		$link .= "tag/".$this->posts[0]->name;
    	}
    	else if(isset($this->posts[0]->uid)) {
    		$title = "BeWelcome Blog Feed for ".$this->posts[0]->author;
    		$link .= "author/".$this->posts[0]->author;
    	}
    	
    	echo $this->formatFeedTitle($title, $link, "Feed for BeWelcome Blogs");
    }
    
    	
    
    /**
     * TODO: proper linking to blog items
     */
    protected function showItem($post)
    {
        $post_link = "blog/";
        echo $this->formatFeedItem($post->blog_title, $post->blog_text, $post->edited, $post_link, $post->author);
    }
}



?>