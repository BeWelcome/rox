<?php

/**
* ForumBoardWidget
*
* @package forums
* @author Andreas (bw:lemon-head)
* @copyright whatever
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
*/


class ForumBoardWidget extends ItemlistWithPagination
{
    public function render() {
        ?><style>
        div.itemlist_element.odd {background:#f8f8f8;}
        </style><?php
        parent::render(); 
    }
    
    //-----------------------------------------------------------------
    // pagination
    
    protected function hrefPage($i_page) {
        return 'forum/s'.$this->topic->topic_id.'/page'.$i_page;
    }
    
    
    
    //-----------------------------------------------------------------
    // getting the posts
    
    /**
     * @return all posts for this topic
     */
    /*
    protected function getAllItems()
    {
        if (!$this->topic) {
            echo __METHOD__ . ' $this->topic not a topic.';
        } else if (!is_array($posts = $this->topic->posts)) {
            print_r($this->topic);
            return array('x', 'y', 'z');
        } else {
            echo 'how many posts: '.count($posts). '<br>';
            return $posts;
        }
    }
    */
    
    protected function getItemsInRange($begin, $count)
    {
        // echo '<pre>'; print_r(func_get_args()); print_r($this); echo '</pre>';
        if (!$this->board) {
            echo __METHOD__ . ' $this->board is undefined.';
        } else if (!is_array($topics = $this->board->topicsInRange($begin, $count))) {
            print_r($this->board);
            return array('x', 'y', 'z');
        } else {
            // echo 'how many posts: '.count($posts). '<br>';
            return $topics;
        }
    }
    
    protected function itemsTotalCount()
    {
        return $this->board->numberOfTopics();
    }
    
    
    //-----------------------------------------------------------------
    // list layout
    
    
    protected function showItems()
    {
        // choose a simple list of <div class="itemlist_element odd/even"> containers
        $this->showItems_list();
    }
    
    
    
    protected function showListItem($item, $i_row)
    {
        echo '<pre>'.__METHOD__.'<br>'; print_r($item); echo '</pre>';
    }
}


?>