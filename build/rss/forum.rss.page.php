<?php


/**
 * 
 * TODO: so far we don't use this class. Would it be a good alternative to the above?
 * 
 * For validating feeds: 
 * http://feedvalidator.org/
 * 
 * usage:
 * $page = new PageWithForumRSS();
 * $page->posts = ...;  // an array of forum posts from the database
 */
class PageWithForumRSS
{
	private $model = null;
	private $posts = null;
	

    public function render()
    {
        //header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="iso-8859-1"?>
<rss version="2.0">
<channel>';
        
        $this->showHeader();
        
        foreach ($this->posts as $post) {
            $this->showItem($post);
        }
        
        echo '</channel>
</rss>';
        PVars::getObj('page')->output_done = true;
    }
    
    
    /**
     * This function could be overwritten in a subclass...
     *
     */
    protected function showItem($post)
    {
        $postid = $post->IdContent;
        $message = $post->message;
        $title = $post->title;
        $thread_id = $post->threadid;
        $post_id = $post->id;
        
        // TODO: what about the date?
        echo
"
  <item>
    <title>$title</title>
    <description>$message</description>
    <pubdate>Mon, 30 Jun 2003 08:00:00 UT</pubdate>
    <category>Category</category>
    <link>http://www.bewelcome.org/forums/s$thread_id/#post$post_id</link>
  </item>
"
        ;
    }
    
    
    /**
     * This function could be overwritten in a subclass...
     *
     */
    protected function showHeader()
    {
        echo
'
  <title>BeWelcome Forum Any Feed</title>
  <link>http://www.bewelcome.org/forum/feeds</link>
  <description>Feeds for the BeWelcome forum</description>
'
        ;
    }
}



?>