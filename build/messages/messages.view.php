<?php 

/**
 * Base class for all pages in messages application
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesBasePage extends RoxPageView
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/YAML/screen/custom/bw_basemod_2col.css';
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



//-------------------------------------------------------------------------------

class MessagesMailboxBasePage extends MessagesBasePage
{
    /**
     * redefine in your subclasses
     *
     * @return array the messages to be shown in the list
     */
    protected function getMessages()
    {
        return array();
    }
    
    protected function getColumns()
    {
        return array(
            'from' => 'From',
            'to' => 'To',
            'title' => 'Text',
            'dateSent' => 'Date Sent'
        );
    }
    
    protected function column_col3()
    {
        echo '<table>';
        $messages = $this->getMessages();
        $columns = $this->getColumns();
        if (empty($messages)) {
            echo 'no messages in this folder';
        } else {
            $first_msg = $messages[0];
            echo '<tr style="font-weight:bold;">';
            foreach ($columns as $key => $value) {
                echo "<td>$value</td>";
            }
            echo '</tr>';
            foreach ($messages as $message) {
                echo '<tr style="border:1px solid #eee; border-width:1px 0">';
                foreach ($columns as $key => $value) {
                    echo '<td>';
                    eval('$this->msgTableCell_'.$key.'($message);');
                    echo '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }
    }
    
    protected function msgTableCell_from($message)
    {
        $username = $message->senderUsername;
        echo '<a href="bw/member.php?cid='.$username.'">'.$username.'</a>';
    }
    protected function msgTableCell_to($message)
    {
        $username = $message->receiverUsername;
        echo '<a href="bw/member.php?cid='.$username.'">'.$username.'</a>';
    }
    protected function msgTableCell_title($message)
    {
        echo $message->Message;
    }
    protected function msgTableCell_dateSent($message)
    {
        echo $message->DateSent;
    }
}


class MessagesInboxPage extends MessagesMailboxBasePage
{
    protected function getSubmenuActiveItem() {
        return 'received';
    }
    
    protected function getMessages()
    {
        return $this->getModel()->receivedMailbox();
    }
    
    protected function getColumns()
    {
        $columns = parent::getColumns();
        unset($columns['to']);
        return $columns;
    }
}

class MessagesSentboxPage extends MessagesMailboxBasePage
{
    protected function getSubmenuActiveItem() {
        return 'sent';
    }
    
    protected function getMessages()
    {
        return $this->getModel()->filteredMailbox('IdSender = '.$_SESSION['IdMember']);
    }
    

    protected function getColumns()
    {
        $columns = parent::getColumns();
        unset($columns['from']);
        return $columns;
    }
}


class MessagesSpamboxPage extends MessagesMailboxBasePage
{
    protected function getSubmenuActiveItem() {
        return 'spam';
    }
    
    protected function getMessages()
    {
        return $this->getModel()->filteredMailbox(array(
            'IdReceiver = '.$_SESSION['IdMember'],
            'SpamInfo = "SpamSayMember"'
        ));
    }

    protected function getColumns()
    {
        $columns = parent::getColumns();
        unset($columns['to']);
        return $columns;
    }
}


class MessagesDraftsboxPage extends MessagesMailboxBasePage
{
    protected function getSubmenuActiveItem() {
        return 'drafts';
    }
    
    protected function getMessages()
    {
        return $this->getModel()->filteredMailbox(array(
            'messages.IdSender = '.$_SESSION['IdMember'],
            'messages.Status = "Draft"'
        ));
    }
    
    protected function getColumns()
    {
        $columns = parent::getColumns();
        unset($columns['dateSent']);
        unset($columns['from']);
        return $columns;
    }
}


/**
 * Page for reading a single message
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ReadMessagePage extends MessagesBasePage
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get the translation module
        $words = $this->getWords();
        ?>
        <h3>The hello universe (advanced) middle column</h3>
        using the class HellouniversePage.<br>
        Simple version in <a href="hellouniverse">hellouniverse</a>.<br>
        More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
        With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        <br>
        <br>
        A translated word (wordcode 'Groups'):
        <?=$words->getFormatted('Groups') ?>
        <?php
    }
    
    /**
     * configure the teaser (the content of the orange bar)
     */
    protected function teaserContent() {
		echo 'The hello universe teaser';
	}
	
	/**
	 * configure the page title (what appears in your browser's title bar)
	 * @return string the page title
	 */
    protected function getPageTitle() {
        return 'Hello Unviverse!';
    }
    
    /**
     * configure the sidebar
     */
	protected function leftSidebar()
	{
	    echo 'Hello Universe Sidebar';
	}
}


//-------------------------------------------------------------------------------

/**
 * Page for writing a new message
 * 
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class WriteMessagePage extends MessagesBasePage
{
    
    /**
     * the constructor sets the tab name.
     *
     * @param string $tabname
     */
    public function __construct($tabname) {
        $this->_tabname = $tabname;
    }
    
    /**
     * define the items of the submenu
     *
     * @return array items of the submenu
     */
    protected function getSubmenuItems() {
        return array(
            array(
                'tab1',  // name of the menu item
                'hellouniverse/tab1',  // relative url
                'HellouniverseTab1'  // word code for translation
            ),
            array('tab2', 'hellouniverse/tab2', 'HellouniverseTab2'),
            array('tab3', 'hellouniverse/tab3', 'HellouniverseTab3')
        );
    }
    
    /**
     * define the name of the active menu item
     *
     * @return string name of the menu item
     */
    protected function getSubmenuActiveItem() {
        return $this->_tabname;
    }
    
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get translation module
        $words = $this->getWords();
        ?>
        <h3>The hello universe (tabbed) middle column</h3>
        Using the class HellouniverseTabbedPage.<br>
        Simple version in <a href="hellouniverse">hellouniverse</a>.<br>
        More beautiful in <a href="hellouniverse/advanced">hellouniverse/advanced</a>!<br>
        With tabs in <a href="hellouniverse/tab1">hellouniverse/tab1</a>!
        <br>
        <br>
        A translated word (wordcode 'Groups'):
        <?=$words->getFormatted('Groups') ?>
        <?php
    }
}



?>