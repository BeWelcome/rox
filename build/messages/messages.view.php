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
    
    protected function getTableColumns()
    {
        return array(
            'contact' => 'From/To',
            'title' => 'Text',
            'dateSent' => 'Date'
        );
    }
    
    protected function column_col3()
    {
        $this->mailboxDescription();
        $messages = $this->getMessages();
        $columns = $this->getTableColumns();
        if (empty($messages)) {
            echo 'no messages in this folder';
        } else {
            echo '<table>';
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
    
    protected function msgTableCell_contact($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $contact_id = $direction_in ? $message->IdSender : $message->IdReceiver;
        ?>
        <table><tr>
        <td><?=MOD_layoutbits::linkWithPicture($contact_username) ?></td>
        <td>
        <?=$direction_in ? 'From' : 'To' ?><br>
        <strong><?=$contact_username ?></strong><br>
        <a href="bw/member.php?cid=<?=$contact_username ?>">profile</a><br>
        <a href="messages/with/<?=$contact_username ?>">messages</a>
        </td>
        </tr></table><?php
    }
    
    protected function msgTableCell_title($message)
    {
        ?><a href="messages/<?=$message->id ?>"><?=$message->Message ?></a>
        <?php
    }
    protected function msgTableCell_dateSent($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        ?>
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'Received on' : 'Sent on' ?></span><br>
        <?=$message->DateSent ?>
        <?php
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
    

    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        $columns['contact'] = 'To';
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

    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        $columns['contact'] = 'From';
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
    
    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        unset($columns['dateSent']);
        $columns['contact'] = 'To';
        return $columns;
    }
}

class MessagesContactboxPage extends MessagesMailboxBasePage
{
    private $_contact;
    
    public function setMember($member)
    {
        $this->_contact = $member;
    }
    
    protected function getMessages()
    {
        return $this->getModel()->getMessagesWith($this->_contact->id);
    }
    
    protected function msgTableCell_contact($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $contact_id = $direction_in ? $message->IdSender : $message->IdReceiver;
        ?>
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'From' : 'To' ?></span><br>
        <?=$contact_username ?>
        <?php
    }
    
    protected function mailboxDescription()
    {
        $contactUsername = $this->_contact->Username;
        $myselfUsername = $_SESSION['Username'];
        ?><div class="floatbox">
        <div style="float:left"><?=MOD_layoutbits::linkWithPicture($contactUsername) ?></div>
        <h3>Messages between <a href="bw/member.php?cid=<?=$contactUsername ?>"><?=$contactUsername ?></a>
        and <a href="<?=$myselfUsername ?>"><?=$myselfUsername ?></a> ( = myself)</h3>
        (in both directions)
        </div>
        <?php
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


class MessagesMustloginPage extends MessagesBasePage
{
    private $_redirect_url = 'messages';
    
    // the address after login
    public function setRedirectURL($url)
    {
        $this->_redirect_url = $url;
    }
    
    protected function column_col3()
    {
        $url = $this->_redirect_url;
        ?><h3>Please log in!</h3>
        You tried to open<br>
        <a href="<?=$url ?>"><?=$url ?></a><br><br>
        which is only visible to logged-in members.<br>
        (anonymous people don't have a mailbox)<?php
        
        // TODO: This could be done without a 'UserController' object!
        $User = new UserController;
        $User->displayLoginForm($url);
    }
    
    /*
    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }
    */
}


class ReadMessagePage extends MessagesBasePage
{
    private $_message = 0;
    
    public function setMessage($message)
    {
        $this->_message = $message;
    }
    
    protected function column_col3()
    {
        $message = $this->_message;
        $contactUsername = $message->senderUsername;
        $direction_in = true;
        if ($contactUsername == $_SESSION['Username']) {
            $contactUsername = $message->receiverUsername;
            $direction_in = false;
        }
        ?><div class="floatbox">
        <div style="float:left">
        <?=MOD_layoutbits::linkWithPicture($contactUsername) ?>
        </div>
        <div>
        <p>
          <span class="grey small"><?=($direction_in ? 'Message from' : 'Message to') ?> : </span>
          <a href="bw/member.php?cid=<?=$contactUsername ?>"><?=$contactUsername ?></a>
        </p>
        <p>
          <span class="grey small">Message date : </span> <?=$message->DateSent ?>
        </p>
        </div>
        </div>
        <p id="messagecontent">
        <?=$message->Message ?>
        </p>
        <p>
          <?php if ($direction_in) { ?>
          <a class="button" href="messages/<?=$message->id ?>/reply">reply</a>
          <?php } else { ?>
          <a class="button" href="messages/<?=$message->id ?>/edit">edit</a>
          <?php } ?>
        </p>
        <?php
    }
}


class ReplyMessagePage extends ReadMessagePage
{
    
}

class EditMessagePage extends ReadMessagePage
{
    
}

?>