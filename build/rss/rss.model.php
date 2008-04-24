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
	
	/**
	 * All forum posts
	 * bw/htdocs/rss
	 */	
	public function getForumFeed() {

		
		$feed = $this->xmlHeaders()."
<channel>
  <title>BeWelcome Forum Feed</title>
  <link>http://www.bewelcome.org/rss/</link>
  <description>Feed for the BeWelcome forum</description>  
";


		$query = (
		            "
SELECT * FROM forums_posts as p, forums_threads as t
WHERE p.threadId = t.id
LIMIT 15
		            "
		        );
		        
       $s = $this->dao->query($query);
       if (!$s) {
			throw new PException('... !');
       }
       $i = 1;
       while ($post = $s->fetch(PDB::FETCH_OBJ)) {
       		//print_r($post);
       		$postid = $post->IdContent;
       		$message = $post->message;
       		$title = $post->title;

       		$feed .= "<item>
       					<title>".$title."</title>
       					<description>".$message."</description>
       					<pubdate>Mon, 30 Jun 2003 08:00:00 UT</pubdate>
  						<category>Category</category>
       					<link>http://www.bewelcome.org</link>
       				  </item>";
       		//$post = $words->fTrad($post->IdContent);
       		
		    $i++;
       }
		$feed .= "</channel>";
	    return $feed;
	}	    
	
		
	
	/**
	 * Specific thread
	 */
	public function getThreadFeed($thread_id)
	{
        if (!is_numeric($thread_id)) {
            return false;
        }
		
		$feed = $this->xmlHeaders()."
<channel>
  <title>BeWelcome Forum Thread Feed</title>
  <link>http://www.bewelcome.org/forum/feeds</link>
  <description>Feeds for the BeWelcome forum</description>  
";


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

       		$feed .= "<item>
       					<title>".$title."</title>
       					<description>".$message."</description>
       					<pubdate>Mon, 30 Jun 2003 08:00:00 UT</pubdate>
  						<category>Category</category>
       					<link>http://www.bewelcome.org".$i."</link>
       				  </item>";
       		//$post = $words->fTrad($post->IdContent);
        }
		$feed .= "</channel>";
	    return $feed;
	}	    
	
	
	/**
	 * Specific tag
	 */
	public function getTagFeed($tagname)
	{
	    if (is_numeric($tagname)) {
	        // it's rather a tag id.
            $query =
                "
SELECT forums_posts.*
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
SELECT forums_posts.*
FROM forums_posts, forums_threads, tags_threads, forums_tags
WHERE forums_tags.tag = '".$tagname."'
AND forums_tags.tagid = tags_threads.IdTag
AND tags_threads.IdThread = forums_threads.id
AND forums_threads.id = forums_posts.threadid
                "
	        ;
	    }
	    
	    $feed = '';
	    if (!$this->dao->query($query)) {
	        // didn't work. buuuh.
            throw new PException('... !');
            return false;
        } else for ($i=1; $post = $s->fetch(PDB::FETCH_OBJ); ++$i) {
            // yeah, do whatever with the $post.
            $postid = $post->IdContent;
            $message = $post->message;
            $title = $post->title;

            $feed .=
"
<item>
  <title>".$title."</title>
  <description>".$message."</description>
  <pubdate>Mon, 30 Jun 2003 08:00:00 UT</pubdate>
  <category>Category</category>
  <link>http://www.bewelcome.org".$i."</link>
</item>
"
            ;
        }
	}
	
	
	
	/**
	 * xml definitions
	 */
	protected function xmlHeaders() {
		return "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
<rss version=\"2.0\">";
	}
	
}



?>