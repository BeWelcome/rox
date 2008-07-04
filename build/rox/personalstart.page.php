<?php


class PersonalStartpage extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'main';
    }

    protected function teaserContent()
    {
        $words = new MOD_words();
        $thumbPathMember = MOD_layoutbits::smallUserPic_userId($_SESSION['IdMember']);
        //$imagePathMember = MOD_user::getImage();
        
        $_newMessagesNumber = $this->model->getNewMessagesNumber($_SESSION['IdMember']);
        
        if ($_newMessagesNumber > 0) {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
        } else {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNoNewMessages');
        }
        require TEMPLATE_DIR.'apps/rox/teaser_main.php';
    }
    
    protected function getPageTitle() {
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'];
        } else {
            // this should not happen actually!
            return 'Welcome, Guest!';
        }
    }
    
    protected function leftSidebar()
    {
        require TEMPLATE_DIR.'apps/rox/userbar.php';
    }
    
    protected function column_col3()
    {
        // echo '<h3>Your messages</h3>';
        /*
        $inbox_widget = new MailboxWidget_Personalstart;
        $inbox_widget->model = new MessagesModel;
        $inbox_widget->items_per_page = 4;
        */
        // $inbox_widget->render();
        // echo '<a href="bw/mymessages.php">more...</a>';
        
        $Forums = new ForumsController;
        $citylatlong = $this->model->getAllCityLatLong();
        $google_conf = PVars::getObj('config_google');  
        require TEMPLATE_DIR.'apps/rox/mainpage.php';
    }
}

/*
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
        // $time_difference = $item->unixtime_created - $prev_item->unixtime_created;
        // $seconds = $time_difference % 60;
        // $time_difference = (int)($time_difference/60);
        // $minutes = $time_difference % 60;
        // $time_difference = (int)($time_difference/60);
        // $hours = $time_difference % 24;
        // $time_difference = (int)($time_difference/24);
        // $days = $time_difference;
        // echo '<div style="color:#ccc;">'.$days.' days and '.$hours.':'.$minutes.':'.$seconds.' between messages</div>';
        echo '<p class="small">'.MOD_layoutbits::ago($item->unixtime_created).'</p>';
    }
}
*/

?>