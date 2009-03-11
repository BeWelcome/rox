<?php


/**
 * Base class for all pages in messages application
 *
 * @package messages
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesBasePage extends RoxPageView
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/YAML/screen/custom/bw_basemod_2col_left.css';
        return $stylesheets;
    }
    
    protected function teaserContent() {
        // &gt; or &raquo; ?
        $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : 'Nobody';
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="messages"><?=$username ?>'s Messages</a></h1>
        </div>
        </div><?php
    }
    
    public function leftSidebar()
    {
        $words = $this->getWords();
        $items = array();
        $items[] = array('received', 'messages/received', 'Received');
        $items[] = array('sent', 'messages/sent', 'Sent');
        $items[] = array('spam', 'messages/spam', 'Spam');
        $items[] = array('drafts', 'messages/drafts', 'Drafts');
        ?>
        <div id="sidebar_menu">
          <ul>
      <?php
        $active_menu_item = $this->getSubmenuActiveItem();
        foreach ($items as $index => $item) {
            $name = $item[0];
            $url = $item[1];
            $label = $item[2];
            if ($name === $active_menu_item) {
                $attributes = ' class="active"';
            } else {
                $attributes = '';
            }
            
            ?><li id="messages_sub<?=$index ?>" <?=$attributes ?>>
              <a style="cursor:pointer;" href="<?=$url ?>">
                <span><?=$label ?></span>
              </a>
              <?=$words->flushBuffer(); ?>
            </li>
            <?php
            
        }
        ?></ul>
        </div>
        <?php
    }
    
    protected function getSubmenuItems()
    {
        
        $words = $this->getWords();
        $items = array();
        $items[] = array('received', 'messages/received', 'Received');
        $items[] = array('sent', 'messages/sent', 'Sent');
        $items[] = array('spam', 'messages/spam', 'Spam');
        $items[] = array('drafts', 'messages/drafts', 'Drafts');
        return $items; 
    }
}





?>