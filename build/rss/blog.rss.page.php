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
class PageWithBlogRSS extends PageWithGivenRSS
{
    
    /**
     * TODO: proper linking to blog items
     */
    protected function showItem($post)
    {
        $post_link = "blog/";
        echo $this->formatFeedItem($post->blog_title, $post->blog_text, $post->edited, $post_link);
    }
    
    
    /**
     */
    protected function showHeader()
    {
    	$title = "BeWelcome Blog Feed";
    	$link = "blog/";
    	
    	if(isset($this->posts[0]->blog_tag_id)) {
    		$title = "BeWelcome Blog Feed for ".$this->posts[0]->name;
    		$link .= $this->posts[0]->name;
    	}
    	else if(isset($this->posts[0]->handle)) {
    		$title = "BeWelcome Blog Feed for tag ".$this->posts[0]->handle;
    		$link .= $this->posts[0]->handle;
    	}
    	
    	echo $this->formatFeedTitle($title, $link, "Feed for BeWelcome Blogs");
    }
}



?>