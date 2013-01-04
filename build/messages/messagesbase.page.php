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
       $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
       return $stylesheets;
    }
    
    protected function teaserContent() {
        $words = $this->getWords();
        // &gt; or &raquo; ?
        $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : 'Nobody';
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="messages"><?php echo $words->get('UserMessages', $username); ?></a></h1>
        </div>
        </div><?php
    }
    
    public function leftSidebar()
    {

    }
    
    public function column_col2()
    {
        
    }
    
    protected function getSubmenuItems()
    {
        
        $words = $this->getWords();
        $items = array();
        $items[] = array('received', 'messages/received', $words->get('MyMessagesReceived'));
        $items[] = array('sent', 'messages/sent', $words->get('MyMessagesSent'));
        $items[] = array('spam', 'messages/spam', $words->get('MyMessagesSpam'));
//        $items[] = array('drafts', 'messages/drafts', $words->get('MyMessagesDraft'));
        return $items; 
    }
}
