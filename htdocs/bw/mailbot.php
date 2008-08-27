<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

// Mail bot is a php script used to send automatically the mail

// tell the init.php that this is a mailbot
// (so it does not run dbupdate.php)

$i_am_the_mailbot = true;

require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

$baseuri = PVars::getObj('env')->baseuri;

if (IsLoggedIn()) {
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    
    <html>
    <head>
    <title>Mail bot manual page</title>
    </head>
    <body>
    <?php     
    if (HasRight("RunBot") <= 0) {
        echo "This need right <b>RunBot</b>";
        exit (0);
    }
    $IdTriggerer = $_SESSION['IdMember'];
} else { // case not logged
    // todo check if not logged that this script is effectively runned by the cron
    $IdTriggerer = 0; /// todo here need to set the Bot id
    $_SESSION['IdMember'] = 0;
} // not logged


// -----------------------------------------------------------------------------
// broadcast messages for members
// -----------------------------------------------------------------------------
$str = "
SELECT
    broadcastmessages.*,
    Username,
    members.Status AS MemberStatus,
    broadcast.Name AS word
FROM
    broadcast,
    broadcastmessages,
    members
WHERE
    broadcast.id = broadcastmessages.IdBroadcast  AND
    broadcastmessages.IdReceiver = members.id     AND
    broadcastmessages.Status = 'ToSend'
";
$qry = sql_query($str);

$countbroadcast = 0;
while ($rr = mysql_fetch_object($qry)) {
    $Email = GetEmail($rr->IdReceiver);
    $MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);
    
    $subj = wwinlang("BroadCast_Title_".$rr->word,$MemberIdLanguage, $rr->Username);
    $text = wwinlang("BroadCast_Body_".$rr->word,$MemberIdLanguage, $rr->Username);
//    if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
    if (!bw_mail($Email, $subj, $text, "", "newsletter@bewelcome.org", $MemberIdLanguage, "html", "", "")) {
        $str = "
UPDATE
    broadcastmessages
SET
    Status = 'Failed'
WHERE
    IdBroadcast =  $rr->IdBroadcast  AND
    IdReceiver = $rr->IdReceiver
        ";
        LogStr("Cannot send broadcastmessages.id=#" . $rr->IdBroadcast . " to <b>".$rr->Username."</b> \$Email=[".$Email."]","mailbot");
        
    } else {
        $str = "
UPDATE
    broadcastmessages
SET
    Status = 'Sent'
WHERE
    IdBroadcast = $rr->IdBroadcast  AND
    IdReceiver = $rr->IdReceiver
        ";
        $countbroadcast++ ;
    }
    sql_query($str);
}


// -----------------------------------------------------------------------------
// Forum notifications
// -----------------------------------------------------------------------------
$str = "
SELECT
    posts_notificationqueue.*,
    Username
FROM
    posts_notificationqueue,
    members
WHERE
    posts_notificationqueue.IdMember = members.id  AND
    (members.Status = 'Active' OR members.Status = 'ActiveHidden')  AND
    posts_notificationqueue.Status = 'ToSend'
";
$qry = sql_query($str);

$countposts_notificationqueue = 0;
while ($rr = mysql_fetch_object($qry)) {
    $Email = GetEmail($rr->IdMember);
    $MemberIdLanguage = GetDefaultLanguage($rr->IdMember);

    $rPost=LoadRow("
SELECT
    forums_posts.*,
    members.Username,
    members.id AS IdMember,
    forums_threads.title AS thread_title,
    forums_threads.threadid AS IdThread,
    forums_posts.message,
    cities.Name AS cityname,
    countries.Name AS countryname
FROM    
    cities,
    countries,
    forums_posts,
    forums_threads,
    members,
    user
WHERE
    forums_threads.threadid = forums_posts.threadid  AND
    forums_posts.authorid = user.id  AND
    members.Username = user.handle  AND
    forums_posts.postid = $rr->IdPost  AND
    cities.id = members.IdCity  AND
    countries.id = cities.IdCountry
    "); 
    $rImage=LoadRow("
SELECT
    *
FROM
    membersphotos
WHERE
    IdMember = $rPost->IdMember AND
    SortOrder = 0
    ");
    
    $UnsubscribeLink="" ;
    if ($rr->IdSubscription!=0) { // Compute the unsubscribe link according to the table where the subscription was coming from
        $rSubscription = LoadRow("
SELECT
    *
FROM
    $rr->TableSubscription
WHERE
    id = $rr->IdSubscription
        ");
        if ($rr->TableSubscription == "members_threads_subscribed") {
            $UnsubscribeLink = '<a href="'.$baseuri.'forums/subscriptions/unsubscribe/thread/'.$rSubscription->id.'/'.$rSubscription->UnSubscribeKey.'">'.wwinlang('ForumUnSubscribe',$MemberIdLanguage).'</a>';
        }
    }
    
    $NotificationType=$rr->Type ;

    switch ($rr->Type) {
    
        case 'newthread':
            //             $subj = wwinlang("ForumNotification_Title_newthread",$MemberIdLanguage, $ForumSenderUsername->Username);
            //             $text = wwinlang("ForumNotification_Body",$MemberIdLanguage,$rr->Username,$rr->type);
            $NotificationType=wwinlang("ForumMailbotNewThread",$MemberIdLanguage) ;
            break ;
        case 'reply':
            $NotificationType=wwinlang("ForumMailbotReply",$MemberIdLanguage) ;
            break ;
        case 'moderatoraction':
        case 'deletepost':
        case 'deletethread':
        case 'useredit':
            $NotificationType=wwinlang("ForumMailbotEditedPost",$MemberIdLanguage) ;
            break ;
        case 'translation':
            break ;
        case 'buggy':
        default :
            LogStr("problem with posts_notificationqueue \$Type=".$rr->Type."for id #".$rr->id,"mailbot");
            $text="Problem in forum notification Type=".$rr->Type."<br />" ;
            break ;
    }

// Setting some default values
    $subj = "Forum Bewelcome, ".$NotificationType.":".$rPost->thread_title." from ".$rPost->Username ; 
    $text = '<html><head><title>'.$subj.'</title></head><body><table border="0" cellpadding="0" cellspacing="10" width="700" style="margin: 20px; background-color: #fff; font-family:Arial, Helvetica, sans-serif; font-size:12px; color: #333;" align="left">
        <tr><th colspan="2"  align="left">
        <a href="'.$baseuri.'forums/s'.$rPost->IdThread.'">'.$rPost->thread_title.'</a>
        </th></tr>
        <tr><td colspan="2">from: <a href="'.$baseuri.'bw/member.php?cid='.$rPost->Username.'">'.$rPost->Username.'</a> '.$rPost->countryname.'('.$rPost->cityname.')</td></tr>
        <tr><td valign="top">';
    if (isset($rImage->FilePath)) {
       $text .= '<img alt="picture of '.$rPost->Username.'" height="150px" src="'.$baseuri.$rImage->FilePath.'"/>';
    } else {
       $text .= '<img alt="Bewelcome" src="http://www.bewelcome.org/styles/YAML/images/logo.gif" />';
    }
    $text .= '</td><td>'.$rPost->message.'</td></tr>';
    if ($UnsubscribeLink!="") {
       $text .= '<tr><td colspan="2">'.$UnsubscribeLink.'</td></tr>';
    } else {
        // This case should be for moderators only
        $text .= '<tr><td colspan="2"> IdPost #'.$rr->IdPost.' action='.$NotificationType.'</td></tr>';
    }
    $text .= '</table></body></html>';
    
    if (!bw_mail($Email, $subj, $text, "", "forum@bewelcome.org", $MemberIdLanguage, "html", "", "")) {
        LogStr("Cannot send posts_notificationqueue=#" . $rr->id . " to <b>".$rPost->Username."</b> \$Email=[".$Email."]","mailbot");
        // Telling that the notification has been not sent
        $str = "
UPDATE
    posts_notificationqueue
SET
    posts_notificationqueue.Status = 'Failed'
WHERE
    posts_notificationqueue.id = $rr->id
        ";
    } else {
        $countposts_notificationqueue++;
        // Telling that the notification has been sent
        $str = "
UPDATE
    posts_notificationqueue
SET
    posts_notificationqueue.Status='Sent'
WHERE
    posts_notificationqueue.id = $rr->id
        ";
    }
    sql_query($str);
}
$sResult = $countposts_notificationqueue . " forum notification sent <br \>";


// -----------------------------------------------------------------------------
// Normal messages between members
// -----------------------------------------------------------------------------

$str = "
SELECT
    messages.*,
    Username,
    members.Status AS MemberStatus
FROM
    messages,
    members
WHERE
    messages.IdSender = members.id  AND
    messages.Status = 'ToSend'
";
$qry = sql_query($str);

$count = 0;
while ($rr = mysql_fetch_object($qry)) {
    if (($rr->MemberStatus!='Active')and ($rr->MemberStatus!='ActiveHidden')) {  // Messages from not actived members will not be send this can happen because a member can have been just banned
        if (IsLoggedIn()) {
            echo "Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")" ;
        }
        $str = "
UPDATE
    messages
SET
    Status = 'Freeze'
WHERE
    id = $rr->id
        "; 
        sql_query($str);
        LogStr("Mailbot refuse to send message #".$rr->id." Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")","mailbot");
        continue ;
    } 
     
    $Email = GetEmail($rr->IdReceiver);
    $MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);
    $subj = ww("YouveGotAMail", $rr->Username);
    $urltoreply = $baseuri."bw/contactmember.php?action=reply&cid=".$rr->Username."&iMes=".$rr->id;
    $MessageFormatted=$rr->Message;
    if ($rr->JoinMemberPict=="yes") {
        $rImage=LoadRow("
SELECT
    *
FROM
    membersphotos
WHERE
    IdMember = $rr->IdSender  AND
    SortOrder = 0
        ");
        $MessageFormatted = '
            <html><head>
            <title>'.$subj.'</title></head>
            <body>
            <table>
            <tr><td>
        ';
        if (isset($rImage->FilePath)) {
            $MessageFormatted .= '<img alt="picture of '.$rr->Username.'" height="200px" src="'.$baseuri.$rImage->FilePath.'"/>';
        }
        $MessageFormatted .= '</td><td>';
//      $MessageFormatted.=ww("YouveGotAMailText", $rr->Username, $rr->Message, $urltoreply);
        $MessageFormatted .= ww("mailbot_YouveGotAMailText", fUsername($rr->IdReceiver),$rr->Username, $rr->Message, $urltoreply,$rr->Username,$rr->Username);
        $MessageFormatted .= '</td>';
        
        if (IsLoggedIn()) { // In this case we display the tracks for the admin who will probably need to check who is sending for sure and who is not
            echo " from ".$rr->Username." to ".fUsername($rr->IdReceiver). " email=".$Email,"<br>" ;
        }
        if ((isset($rr->JoinSenderMail)) and ($rr->JoinSenderMail=="yes")) { // Preparing what is needed in case a joind sender mail option was added
            $MessageFormatted .= '<tr><td colspan=2>'.ww('mailbot_JoinSenderMail', $rr->Username, GetEmail($rr->IdSender)).'</td>';
        }
        
        $MessageFormatted .= '</table></body></html>';
        
        $text=$MessageFormatted;
        
    } else {
        // $text = ww("YouveGotAMailText", $rr->Username, $MessageFormatted, $urltoreply);
        $text = ww('mailbot_YouveGotAMailText', fUsername($rr->IdReceiver), $rr->Username, $rr->Message, $urltoreply, $rr->Username, $rr->Username);
    }
    
    // to force because context is not defined
    // TODO: What the hell is this?
    $_SERVER['SERVER_NAME'] = 'www.bewelcome.org';

    if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
        LogStr("Cannot send messages.id=#" . $rr->id . " to <b>".$rr->Username."</b> \$Email=[".$Email."]","mailbot");
        $str = "
UPDATE
    messages
SET
    Status = 'Failed'
WHERE
    id = $rr->id
        ";
    } else {
        $str = "
UPDATE
    messages
SET
    Status = 'Sent',
    IdTriggerer = $IdTriggerer,
    DateSent = NOW()
WHERE
    id = $rr->id
        ";
        $count++;
    }
    sql_query($str);

}
// and for Test server
$str = "
UPDATE
    hcvoltest.messages
SET
    Status = 'Sent',
    IdTriggerer = $IdTriggerer,
    DateSent = NOW()
WHERE
    Status = 'ToSend'
";
sql_query($str);
    
$sResult = $sResult.$count . " intermember Messages sent";
if ($countbroadcast>0) {
    $sResult=$sResult. " and ".$countbroadcast. " broadcast messages sent" ;
} 


if (IsLoggedIn()) {
    LogStr("Manual mail triggering " . $sResult, "mailbot");
    echo $sResult;
    echo "<br>\$_SYSHCVOL['MessageSenderMail']=",$_SYSHCVOL['MessageSenderMail'] ;
?>
</body></html>
<?php
} else {
    LogStr("Auto mail triggering " . $sResult, "mailbot");
}
?>
