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
    
    protected $posts;

    public function getPosts() {
        return $this->posts;
    }
    
    /**
     * All forum posts
     * bw/htdocs/rss
     */    
    public function getForumFeed()
    {
        $this->posts = $this->bulkLookup(
            "
SELECT
    p.*,
    t.*, 
    m.Username AS author
FROM
    forums_posts    AS p,
    forums_threads  AS t, 
    members			AS m
WHERE
    p.threadid = t.threadid AND
    p.IdWriter = m.id
ORDER BY
    p.create_time DESC 
LIMIT 15
            "
        );
        
        //echo 'eselmilch<br><pre>'; print_r($this->posts); echo '</pre>';
        
        if ($this->posts == null) return false;
        return true;
    }        
    
    /**
     * Specific thread, e.g.
     * rss/thread/2
     * rss/thread/test
     */
    public function getThreadFeed($thread)
    {
        if (!is_numeric($thread)) {
            $condition = "t.title = '".$thread."' ";
        }
        else {
            $condition = "p.threadId = ".$thread." ";
        }
        
        $query = (
            "
SELECT
    p.*, 
    t.*, 
    m.Username AS author
FROM
    forums_posts    AS p,
    forums_threads  AS t, 
    members			AS m
WHERE
    $condition   AND
    p.threadId = t.threadid AND
    p.IdWriter = m.id
ORDER BY
    p.create_time DESC 
LIMIT 15
            "
        );
        $this->posts = $this->bulkLookup($query);
        if ($this->posts == null) return false;
        return true;
    }        
    
     
    
    public function getBlogFeed() {
            $query =" 
SELECT
    b.*, 
    bd.*, 
   	u.handle AS author
FROM 
    blog       AS b,
    blog_data  AS bd, 
   	user	   AS u
WHERE
    b.blog_id = bd.blog_id AND
   	b.user_id_foreign = u.id
ORDER BY
    bd.edited DESC
LIMIT 0, 30
";            
        
        $this->posts = $this->bulkLookup($query);
        //echo "<pre>model posts:";
        //print_r($this->posts);
        
        if ($this->posts == null) return false;
        return true;        
    }
    
    public function getBlogFeedByAuthor($author)
    {
        $query =
            " 
SELECT
    b.*, 
    bd.*, 
    u.handle AS author, 
    u.id	 AS uid
FROM
    blog      AS b,
    blog_data AS bd,
    user      AS u
WHERE
    u.handle  = '$author'          AND
    u.id      = b.user_id_foreign  AND
    b.blog_id = bd.blog_id
ORDER BY
    bd.edited DESC
            "
        ;            
        //echo "<pre>q: ".$query;
        $this->posts = $this->bulkLookup($query);
        if ($this->posts == null) return false;
        return true;        
    }
    

    public function getBlogFeedByTag($tag) {
    
        $condition = "";
        if (is_numeric($tag)) {
            $condition .= "bt.blog_tag_id = $tag";
        } else {
            $condition .= "bt.name = '".$tag."'";
        }
        

        $query =
            " 
SELECT
    b.*, 
    bd.*,
    btt.*,
    bt.*, 
    u.handle AS author
FROM
    blog        AS b,
    blog_data   AS bd,
    blog_to_tag AS btt,
    blog_tags   AS bt,
    user 		AS u
WHERE
    $condition                                     AND
    b.blog_id               = bd.blog_id           AND
    bd.blog_id              = btt.blog_id_foreign  AND
    btt.blog_tag_id_foreign = bt.blog_tag_id	   AND
    b.user_id_foreign 		= u.id      
ORDER BY
    bd.edited DESC
            "
        ;            
    
        //echo "<pre>q: ".$query;
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
    public function getTagFeed($tag)
    {
        if (is_numeric($tag)) {
        	$condition = "ft.tagid     = ".$tag;
        } else {
            $condition = "ft.tag = '".$tag."'";
        }
            $query =
                "
SELECT
    p.*,
    t.title,
    ft.tagid     AS tagid,
    ft.tag       AS tagname, 
    m.Username 	 AS author
FROM
    forums_posts	AS p,
    forums_threads	AS t,
    tags_threads	AS tt,
    forums_tags		AS ft,
    members 			AS m
WHERE
    ".$condition." AND
    ft.tagid     = tt.IdTag   AND
    tt.IdThread  = ft.id      AND
    ft.id     	 = p.threadid AND
	m.id		 = p.IdWriter
ORDER BY
    p.create_time DESC 
LIMIT 0,30
                "
            ;
            
        //echo "<pre>q: ".$query;
        
        $this->posts = $this->bulkLookup($query);
        if ($this->posts == null) return false;
        return true;
    }
}
?>