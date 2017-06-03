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
    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }
    
    protected function teaserContent() {
        $words = $this->getWords();
        // &gt; or &raquo; ?
        $username = $this->_session->has( 'Username' ) ? $this->_session->get('Username') : 'Nobody';
        ?>
        <div>
            <h1><a href="messages"><?php echo $words->get('UserMessages', $username); ?></a></h1>
        </div>
        <?php
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
