<?php


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
        $words = new MOD_words();
        $message = $this->message;
        $contact_username = $message->senderUsername;
        $direction_in = true;
        if ($contact_username == $_SESSION['Username']) {
            $contact_username = $message->receiverUsername;
            $direction_in = false;
        }
        ?>
        <div class="floatbox">
            <div class="float_left">
                <?=MOD_layoutbits::PIC_50_50($contact_username) ?>
            </div>
            <div class="float_left">
                <p>
                  <span class="grey"><?=($direction_in ? $words->get('MessageFrom','<a href="people/'.$contact_username.'">'.$contact_username.'</a>') : $words->get('MessageTo','<a href="people/'.$contact_username.'">'.$contact_username.'</a>')) ?> </span>
                  &nbsp;&nbsp;&nbsp;&nbsp;
                  <a href="messages/with/<?=$contact_username ?>"><img src="images/icons/comments.png" alt="<?=$words->getSilent('messages_allmessageswith',$contact_username)?>" title="<?=$words->getSilent('messages_allmessageswith',$contact_username)?>"> <?=$words->getSilent('messages_allmessageswith',$contact_username)?></a>
                </p>
                <p>
                  <span class="grey"><?=$words->get('MessagesDate')?> : </span> <?=date($words->getSilent('DateFormatShort'),strtotime($message->DateSent)) ?>
                </p>
            </div>
        </div>
        <?=$words->flushBuffer()?>
        <p id="messagecontent">
        <?echo str_replace("\n","<br />",$message->Message) ; ?>
        </p>
        <p>
          <?php if ($direction_in) { ?>
          <a class="button" href="messages/<?=$message->id ?>/reply"><?=$words->get('replymessage')?></a>
          <?php } else { ?>
          <a class="button" href="messages/<?=$message->id ?>/edit"><?=$words->get('editmessage')?></a>
          <?php } ?>
        </p>
        <?php
    }

    protected function getSubmenuActiveItem() {
        return 'received';
    }
}

class ReplyMessagePage extends ComposeMessagePage
{
    protected function column_col3()
    {
        $message = $this->message;
        // Sender becomes Receiver
        $message->receiverUsername = $message->senderUsername;
        $message->senderUsername = $_SESSION['Username'];
        $message->Message = "\n\n>".str_replace("\n", "\n> ", wordwrap($message->Message, 70));
        $contact_username = $message->senderUsername;
        $direction_in = true;
        if ($contact_username == $_SESSION['Username']) {
            $contact_username = $message->receiverUsername;
            $direction_in = false;
        }
        ?><div class="floatbox">
        <div style="float:left">
        <?=MOD_layoutbits::linkWithPicture($contact_username) ?>
        </div>
        <?php
        parent::column_col3();
    }
}

class EditMessagePage extends ComposeMessagePage
{

}

class MessageSentPage extends ReadMessagePage
{
    protected function column_col3()
    {
        $words = new MOD_words();
        echo '<p class="note">'.$words->get('Message_hasbeensent').'</p>';
        parent::column_col3();
    }
}




?>
