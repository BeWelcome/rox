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
	
	public function getPosts() {
		return $this->posts;
	}
	
	/**
	 * All forum posts
	 * bw/htdocs/rss
	 */	
	public function getForumFeed() {

		$feed = $this->formatFeedTitle("", "", "");

		$query = (
            "
SELECT * FROM forums_posts as p, forums_threads as t
WHERE p.threadId = t.id
LIMIT 15
            "
        );
        if (!$s = $this->dao->query($query)) {
            throw new PException('... !');
        } else for ($i=1; $post = $s->fetch(PDB::FETCH_OBJ); ++$i) {
       		//print_r($post);
       		$postid = $post->IdContent;
       		$message = $post->message;
       		$title = $post->title;
       		$create_time = $post->create_time;

			$feed .= $this->formatFeedItem($title, $message, $create_time, "");
       		//$post = $words->fTrad($post->IdContent);
       		
            $i++;
        }
	    return $feed;
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
		
		
		$feed = $this->formatFeedTitle("Thread", "thread/".$thread_id, "");		

		$query = (
            "
SELECT *
FROM forums_posts as p, forums_threads as t
WHERE p.threadId = $thread_id
AND p.threadId = t.id
LIMIT 15
            "
        );
        $s = $this->dao->query($query);
        if (!$s) {
            throw new PException('... !');
        } else for ($i = 1; $post = $s->fetch(PDB::FETCH_OBJ); ++$i) {
       		//print_r($post);
       		$postid = $post->IdContent;
       		$message = $post->message;
       		$title = $post->title;
            $thread_id = $post->threadid;
            $post_id = $post->id;
            $create_time = $post->create_time;

			$feed .= $this->formatFeedItem($title, $message, $create_time, "forums/s$thread_id/#post$post_id");  		
       		//$post = $words->fTrad($post->IdContent);
        }
	    return $feed;
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
	    if (!$s = $this->dao->query($query)) {
            throw new PException('... !');
            return false;
        } else while ($post = $s->fetch(PDB::FETCH_OBJ)) {
            $postid = $post->IdContent;
            $message = $post->message;
            $title = $post->title;
            $thread_id = $post->threadid;
            $post_id = $post->id;
            $tag_id = $post->tagid;
            $tag_name = $post->tagname;
            //TODO: format time more suitable to rss?
            $create_time = $post->create_time;

			if ($i == 1) {
				$feed .= $this->formatFeedTitle("Forum Tag", "tag/".$tag_id.'-'.$tag_name, "");
			}
			$feed .= $this->formatFeedItem($title, $message, $create_time, "forums/s$thread_id/#post$post_id");
            $i++;
        }
        return $feed;
	}


	
	/**
	 * To be refactored into rss.page.whatever class(es)
	 */
	public function formatFeedTitle($feed_type = "Forum", 
		$feed_link = "", 
		$feed_description = "Feed for the BeWelcome forum") 
		{
			
		return
'<atom:link href="'.PVars::getObj('env')->baseuri.'rss/'.$feed_link.'" rel="self" type="application/rss+xml" />
  <title>BeWelcome '.$feed_type.' Feed</title>
  <link>'.PVars::getObj('env')->baseuri.'rss/'.$feed_link.'</link>
  <description>'.strip_tags($feed_description).'</description>  
'
		;
		
	}
	
		

	/**
	 * To be refactored into rss.page.whatever class(es)
	 */
	public function formatFeedItem($title="", $message="", $pubdate, $link="") {
		$phpdate = strtotime( $pubdate );
		$pubdate = date("D, d M Y H:i:s", $phpdate)." GMT";//'Y-m-d H:i:s', $phpdate );
		return "
		  <item>
		    <title>".strip_tags($title)."</title>
		    <description>".strip_tags($message)."</description>
		    <pubDate>$pubdate</pubDate>
		    <category>BeWelcome</category>
		   	<guid>".PVars::getObj('env')->baseuri.$link."</guid>
		    <link>".PVars::getObj('env')->baseuri.$link."</link>
		  </item>
		";		
	}	
	
	
}



?>