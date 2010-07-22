<?php
class ForumWidget extends ItemlistWidget
{
    private $_uri;
    
    public function setURI($uri)
    {
        $this->_uri = $uri;
    }

    /**
     * returns a good-looking url for a forum thread
     * // copied over from class "ForumsView"
     *
     * @param $thread as read from the threads database with mysql_fetch_object
     * @return string to be used as url
     */
    public function threadURL($thread)
    {
        return $this->_uri.'s'.$thread->threadid.'-'.preg_replace('[^A-Za-z0-9]', '_',$this->getWords()->fTrad($thread->IdTitle) ) ;
    }

    public  function postURL($post)
    {
	
        return $this->_uri.'s'.$post->threadid.'-'.preg_replace('[^A-Za-z0-9]', '_',$this->getWords()->fTrad($post->IdTitle) ) ;
    }
}
class ForumPreviewWidget extends ForumWidget
{
    private $_group;
    private $_limit;
    
    public function render() {
        parent::render();
    }
    
    public function setGroup($group)
    {
        $this->_group = $group;
    }
    
    public function setLimit($limit)
    {
        $this->_limit = $limit;
    }

    // pagination

    protected function hrefPage($i_page) {
        return 'messages/inbox/'.$i_page.$_SERVER['QUERY_STRING'] ;
    }

    protected function showItems()
    {
        // by default, show as a table
        $this->showItems_list();
    }

    //-----------------------------------------------------------------
    // getting the items

    protected function getItems()
    {
        // set items by dependency injection,
        // or override this method.
        $items = $this->_group->getForumThreads();
        // limit output
        if (!empty($items) && $this->_limit != 0) {
            $items = array_chunk($items,$this->_limit);
            return $items[0];
        }
        return $items;
    }
    

    //-----------------------------------------------------------------
    // table layout

    /**
     * Columns for messages table.
     * The $key of a column is used as a suffix for method tableCell_$key
     *
     * @return array table columns, as $name => Column title
     */
    protected function showListItem($item, $i_row)
    {
        $words = $this->getWords();
        $Forums = new Forums();
        $thread = $item;
        
        $url = $this->threadURL($thread);
		if ($url{0}=='s') { // JeanYves Hack/Fix to be sure that forums/ is written in the beginning of the links !
			$url="forums/".$url ;
		}
        $max = $thread->replies + 1;
        $maxPage = ceil($max / $Forums->POSTS_PER_PAGE);

        $last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
//        $last_url = $uri.'s'.$thread->threadid.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
        ?>

        <img src="styles/css/minimal/images/iconsfam/comment_add.png" alt="<?=$words->getBuffered('tags')?>" title="<?=$words->getBuffered('tags')?>" />
        <?php
        echo $words->flushBuffer();
		if ($thread->ThreadDeleted=='Deleted') {
			echo "[Deleted]";
		}
		?>
        <a href="<?=$url?>" class="news">
            <?=$words->fTrad($thread->IdTitle)?>
        </a><br />
        <span class="small grey">by <a href="people/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a> -
            <?php echo date($words->getFormatted('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time)); ?>
            <?php // echo date($words->getFormatted('DateHHMMShortFormat'), $thread->last_create_time); ?>
        </span>
        <a href="<?php echo $last_url; ?>"><img src="styles/css/minimal/images/iconsfam/bullet_go.png" alt="<?php echo $words->getBuffered('to_last'); ?>" title="<?php echo $words->getBuffered('to_last'); ?>" /></a>

        <?php
        echo $words->flushBuffer();
    }

}

?>
