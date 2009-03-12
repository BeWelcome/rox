<?php


class PersonalStartpage extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'main';
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
}


class MailboxWidget_Personalstart extends MailboxWidget_Received
{
    protected function showItems()
    {
        // don't need a table - a simple list is enough.
        $this->showItems_list();
    }
    
    protected function showListItem($message, $i_row)
    {
        extract(get_object_vars($message));
        // print_r($message);
        echo '<a class="float_right" href="bw/contactmember.php?action=reply&cid='.$senderUsername . '&iMes='.$i_row .'">';
        echo '<img src="images/icons/icons1616/icon_reply.png"></a> ';
        echo '<a href="bw/member.php?cid='.$senderUsername.'">'.$senderUsername.': </a> ';
        if (strlen($Message) >= 50)
            echo substr($Message, 0, 50).'... ';
        else 
            echo $Message;
    }
    
    
    protected function showBetweenListItems($prev_item, $item, $i_row)
    {
        echo '<p class="small">'.MOD_layoutbits::ago($item->unixtime_created).'</p>';
    }
}



class NotifyMemberWidget_Personalstart extends NotifyMemberWidget
{
    // currently no modifications here
}


?>