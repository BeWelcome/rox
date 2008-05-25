<?php
/** 
 * RSS model
 * 
 * @package rss
 * @author Anu (narnua)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */


class RssModel extends RoxModelBase
{
	
	private $posts;

	
	/**
	 * All forum posts
	 * bw/htdocs/rss
	 */	
	public function getForumFeed() {
		$query = (
            "
SELECT * FROM forums_posts as p, forums_threads as t
WHERE p.threadId = t.id
LIMIT 15
            "
        );
        
        $this->posts = $this->bulkLookup($query);
		if ($this->posts == null) return false;
		return true;
	}	    
	
		
	
	/**
	 * Specific thread, e.g.
	 * rss/thread/2
	 */
	public function getThreadFeed($thread_id)
	{
        if (!is_numeric($thread_id)) {
            return false;
        }
        
		$query = (
            "
SELECT *
FROM forums_posts as p, forums_threads as t
WHERE p.threadId = $thread_id
AND p.threadId = t.id
LIMIT 15
            "
        );
        $this->posts = $this->bulkLookup($query);
        if ($this->posts == null) return false;
		return true;
	}	    
	
	 
	 
	/**
	 * Specific tag 
	 * rss/tag
	 * rss/tag/2
	 * rss/tag/Milk
	 */
	public function getTagFeed($tagname)
	{
	    if (is_numeric($tagname)) {
	        // it's rather a tag id.
            $query =
                "
SELECT forums_posts.*, forums_threads.title, forums_tags.tagid as tagid, forums_tags.tag as tagname
FROM forums_posts, forums_threads, tags_threads, forums_tags
WHERE forums_tags.tagid = ".$tagname."
AND forums_tags.tagid = tags_threads.IdTag
AND tags_threads.IdThread = forums_threads.id
AND forums_threads.id = forums_posts.threadid
                "
            ;
	    } else if (empty($tagname)) {
	        return false;
	    } else {
            // tagname as string
            // TODO: evtl we don't need all of these tables?
	        $query =
                "
SELECT forums_posts.*, forums_threads.title, forums_tags.tagid as tagid, forums_tags.tag as tagname
FROM forums_posts, forums_threads, tags_threads, forums_tags
WHERE forums_tags.tag = '".$tagname."'
AND forums_tags.tagid = tags_threads.IdTag
AND tags_threads.IdThread = forums_threads.id
AND forums_threads.id = forums_posts.threadid
                "
	        ;
	    }
	    $feed = '';
		$i = 1;
		$this->posts = $this->bulkLookup($query);
		if ($this->posts == null) return false;
		return true;
	}	
}
?>