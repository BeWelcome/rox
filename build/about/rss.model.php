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
       					<link>http://www.bewelcome.org".$i."</link>
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
	public function getThreadFeed($thread_id) {

		
		$feed = $this->xmlHeaders()."
<channel>
  <title>BeWelcome Forum Thread Feed</title>
  <link>http://www.bewelcome.org/forum/feeds</link>
  <description>Feeds for the BeWelcome forum</description>  
";


		$query = (
		            "
SELECT * FROM forums_posts as p, forums_threads as t
WHERE p.threadId = $thread_id
AND p.threadId = t.id
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
       					<link>http://www.bewelcome.org".$i."</link>
       				  </item>";
       		//$post = $words->fTrad($post->IdContent);
       		
		    $i++;
       }
		$feed .= "</channel>";
	    return $feed;
	}	    
	
	
	/**
	 * Specific tag
	 */
	public function getTagFeed($tagname) {
		$sql = "
SELECT * FROM forums_posts, forums_tags
WHERE forum_tags.tag = '".$tagname."'
AND forums_tags.
				";
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