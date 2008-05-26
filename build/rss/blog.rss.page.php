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
     */
    protected function showItem($post)
    {
    	//print_r($this->posts[0]);
        $post_link = "blog/";
        echo $this->formatFeedItem($post->blog_title, $post->blog_text, $post->edited, $post_link);
    }
    
    
    /**
     */
    protected function showHeader()
    {
    	echo $this->formatFeedTitle("Blog", "blog/", "");
    }
}



?>