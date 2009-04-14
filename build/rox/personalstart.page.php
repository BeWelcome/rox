<?php


class PersonalStartpage extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'main';
    }
    
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3.css';
        return $stylesheets;
    }

    protected function teaserContent()
    {
        $this->__call('teaserContent', array());
    }

    protected function getPageTitle() {
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'];
        } else {
            // this should not happen actually!
            return 'Welcome, Guest!';
        }
    }

    protected function column_col1()
    {

    }
}


class MailboxWidget_Personalstart extends MailboxWidget_Received
{
    protected function getMessages()
    {
        if (!isset($_SESSION['IdMember'])) {
            // not logged in - no messages
            return array();
        } else {
            $member_id = $_SESSION['IdMember'];
            return $this->model->filteredMailbox('messages.IdReceiver = '.$member_id.' AND messages.Status = "Sent" AND messages.InFolder = "Normal"','messages.WhenFirstRead');
        }
    }	
	
    protected function showItems()
    {
        // don't need a table - a simple list is enough.
        $this->showItems_list();
    }

    protected function showListItem($message, $i_row)
    {
        $words = new MOD_words();
        extract(get_object_vars($message));
        $readstyle = '';
        if ($message->unixtime_WhenFirstRead == false) $readstyle = 'message unread';
        //print_r($message);
        ?>
        
        <div class="floatbox">
            <a class="float_right" href="messages/<?=$i_row?>/reply" alt="Reply">
                <img src="images/icons/email_go.png">
            </a>
            <?= MOD_layoutbits::PIC_30_30($senderUsername,'','float_left framed')?>
            <a href="messages/<?=$id?>" class="<?=$readstyle?>">
        
            <?php
            if (strlen($Message) >= 61) echo substr($Message, 0, 58).'... ';
            else echo $Message;
            ?>
        
            </a><br />
            <span class="small grey"><?=$words->get('from')?> <a href="bw/member.php?cid=<?=$senderUsername?>"><?=$senderUsername?>: </a>
            <?=MOD_layoutbits::ago($message->unixtime_created)?></span>
        </div>
        
        <?php
    }


    protected function showBetweenListItems($prev_item, $item, $i_row)
    {
        echo '<hr style="border-color: #dddddd" />';
    }
}

class NotifyMemberWidget_Personalstart extends NotifyMemberWidget
{
    // currently no modifications here
}


?>
