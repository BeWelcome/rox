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

class MessagesPageWithMailbox extends MessagesBasePage
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
            ?>no messages in this folder<?php
        } else {
            ?><table>
            <tr><?php
            foreach ($columns as $key => $value) {
                ?><th><?=$value ?></th><?php
            }
            echo '</tr>';
            foreach ($messages as $message) {
                ?><tr style="border:1px solid #eee; border-width:1px 0"><?php
                foreach ($columns as $key => $value) {
                    $methodname = 'msgTableCell_'.$key;
                    ?><td><?php $this->$methodname($message) ?></td><?php
                }
                ?></tr><?php
            }
            ?></table><?php
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
        <a href="bw/member.php?cid=<?=$contact_username ?>"><strong><?=$contact_username ?></strong></a><br>
        <a href="messages/with/<?=$contact_username ?>">mailbox</a><br>
        <a href="messages/compose/<?=$contact_username ?>">send new</a>
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


class MessagesInboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem() {
        return 'received';
    }
    
    protected function getMessages()
    {
        return $this->getModel()->receivedMailbox();
    }
    
    protected function mailboxDescription() {
        echo 'This is your inbox';
    }
}

class MessagesSentboxPage extends MessagesPageWithMailbox
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
    
    protected function mailboxDescription() {
        echo 'These are your sent messages';
    }
}


class MessagesSpamboxPage extends MessagesPageWithMailbox
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
    
    protected function mailboxDescription() {
        echo 'These messages are marked as spam';
    }
}


class MessagesDraftsboxPage extends MessagesPageWithMailbox
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

    
    protected function mailboxDescription() {
        echo 'These are your drafts.';
    }
}

class MessagesContactboxPage extends MessagesPageWithMailbox
{
    protected function getMessages()
    {
        return $this->getModel()->getMessagesWith($this->get('contact_member')->id);
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
        
        $login_widget = $this->createWidget('LoginFormWidget');
        
        if ($memory = $this->memory) {
            $login_widget->memory = $memory;
        }
        
        $login_widget->render();
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
    
    protected function column_col3()
    {
        $message = $this->message;
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

class EditMessagePage extends ComposeMessagePage
{
    
}

class MessageSentPage extends ReadMessagePage
{
    protected function column_col3()
    {
        echo 'message has been sent.';
        parent::column_col3();
    }
}




?>