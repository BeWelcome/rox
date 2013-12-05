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

$_SESSION['Param']=LoadRow("select * from params limit 1") ;

// First test if the MailBot param is in automode (cron or interactive runs it) or manual mode (only interactive mode is possible) or stopped
if (!isset($_SESSION['Param']->MailBotMode)) {
    die ('\$_SESSION[\'Param\']->MailBotMode is missing') ;
}
elseif ($_SESSION['Param']->MailBotMode=='Stop') {
    die ( 'MailBot is stopped') ;
}
elseif (($_SESSION['Param']->MailBotMode=='Manual') and (!IsLoggedIn()) ) {
    LogStr("MailBot is in Manual mode" , "mailbot"); // In this case silent exit to avoid mails notification in the sysadmin mail list
    exit(0) ;
}
elseif (($_SESSION['Param']->MailBotMode=='Manual') ) {
    echo 'MailBot is in Manual mode<br>' ;
}
elseif ($_SESSION['Param']->MailBotMode!='Auto') {
    die ('MailBot is in an unknown mode ('.$_SESSION['Param']->MailBotMode.')') ;
}


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
// broadcast messages for members (massmail)
// -----------------------------------------------------------------------------
$str = "
SELECT
    broadcastmessages.*,
    Username,
    members.Status AS MemberStatus,
    broadcast.Name AS word,
    broadcast.Type as broadcast_type,
    broadcast.EmailFrom as EmailFrom

FROM
    broadcast,
    broadcastmessages,
    members
WHERE
    broadcast.id = broadcastmessages.IdBroadcast  AND
    broadcastmessages.IdReceiver = members.id     AND
    broadcastmessages.Status = 'ToSend' LIMIT 100
";
$qry = sql_query($str);

$countbroadcast = 0;
while ($rr = mysql_fetch_object($qry)) {
    if ($_SESSION['Param']->MailBotMode!='Auto') {
        echo "broadcastmessages <b> Going to Get Email for IdMember : [".$rr->IdReceiver."]</b> broadcastmessages.id=".$rr->id."<br>" ;
    }
    $Email = GetEmail($rr->IdReceiver);
    $MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);

//    if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {

        if (empty($rr->EmailFrom)) {
            $sender_mail="newsletter@bewelcome.org" ;
            if ($rr->broadcast_type=="RemindToLog") {
                $sender_mail="reminder@bewelcome.org" ;
            }
            if ($rr->broadcast_type=="SuggestionReminder") {
                $sender_mail="suggestions@bewelcome.org" ;
            }
        }
        else {
            $sender_mail=$rr->EmailFrom ;
        }

    $subj = getBroadCastElement("Broadcast_Title_" . $rr->word, $MemberIdLanguage, $rr->Username);
    $text = getBroadCastElement("Broadcast_Body_" . $rr->word,$MemberIdLanguage, $rr->Username, $Email);

    $res = bw_mail($Email, $subj, $text, "", $sender_mail, $MemberIdLanguage, "html", "", ""," ");
    $res = true;
    if (!$res) {
        $str = "UPDATE   broadcastmessages
SET   Status = 'Failed'
WHERE    IdBroadcast =  $rr->IdBroadcast  AND    IdReceiver = $rr->IdReceiver        ";
        LogStr("Cannot send broadcastmessages.id=#" . $rr->IdBroadcast . " to <b>".$rr->Username."</b> \$Email=[".$Email."] Type=[".$rr->broadcast_type."]","mailbot");

    } else {

        // If this message was to count has a reminder
        if ($rr->broadcast_type=="RemindToLog") {
            sql_query("update members set NbRemindWithoutLogingIn=NbRemindWithoutLogingIn+1 where members.id=".$rr->IdReceiver);
        }


        $str = "UPDATE    broadcastmessages
SET    Status = 'Sent'
WHERE    IdBroadcast = $rr->IdBroadcast  AND    IdReceiver = $rr->IdReceiver        ";
        $countbroadcast++ ;
        LogStr("This log is to be removed in mailbot.php, for now we count each broadcast : currently \$countbroadcast=".$countbroadcast." send from:".$sender_mail,"Debug") ;
    }
    sql_query($str);
} // end of while on broadcast (massmail)
    if ($countbroadcast>0)  LogStr(" \$countbroadcast=".$countbroadcast." sent at this cycle","Debug") ;


// -----------------------------------------------------------------------------
// Forum notifications
// -----------------------------------------------------------------------------
$str = "
SELECT
    posts_notificationqueue.*,
    Username,
    TIMESTAMPDIFF( minute, posts_notificationqueue.created, now( )) as created_since_x_minute

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
global $fTradIdLastUsedLanguage  ; // This is set for the fTrad function (will define which language to use)
while ($rr = mysql_fetch_object($qry)) {
    if (($rr->created_since_x_minute < 10) and(($rr->Type == 'newthread') or ($rr->Type == 'reply'))) {
        continue ; // Don't process to recent change so it means give time for the user to fix it by an edit
    }
    if ($_SESSION['Param']->MailBotMode!='Auto') {
        echo "posts_notificationqueue <b> Going to Get Email for IdMember : [".$rr->IdMember."]</b> posts_notificationqueue.id=".$rr->id."<br>" ;
    }
    $Email = GetEmail($rr->IdMember);
    $fTradIdLastUsedLanguage=$MemberIdLanguage = GetDefaultLanguage($rr->IdMember);

    $rPost=LoadRow("
        SELECT
            forums_posts.*,
            members.Username,
            members.id AS IdMember,
            forums_threads.title AS thread_title,
            forums_threads.IdTitle,
            forums_threads.threadid AS IdThread,
            forums_threads.IdGroup AS IdGroup,
            forums_posts.message,
            forums_posts.IdContent,
            geonames_cache.name AS cityname,
            geonames_cache2.name AS countryname
        FROM
            forums_posts,
            forums_threads,
            members,
            geonames_cache,
            geonames_cache as geonames_cache2
        WHERE
            forums_threads.threadid = forums_posts.threadid  AND
            forums_posts.IdWriter = members.id  AND
            forums_posts.postid = $rr->IdPost AND
            geonames_cache.geonameid = members.IdCity  AND
            geonames_cache2.geonameid = geonames_cache.parentCountryId
    ");

    // Skip to next item in queue if there was no result from database
    if (!is_object($rPost)) {
        SetForumNotificationStatus($rr->id, 'Failed');
        continue;
    }

    // Sanitise IdMember
    $rPostIdMember = intval($rPost->IdMember);

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
    } elseif ($rr->TableSubscription == 'membersgroups') {
        $UnsubscribeLink = "<hr />" . wwinlang('ForumUnSubscribeGroup', $MemberIdLanguage);
    }

    if ($rPost->IdGroup!=0) { // Get group name
        $rGroupname = LoadRow("
            SELECT
                Name
            FROM
                groups
            WHERE
                id = $rPost->IdGroup
        ");
    }

    // Rewrite the title and the message to the corresponding default language for this member if any
    $rPost->thread_title=fTrad($rPost->IdTitle) ;
    $rPost->message=fTrad($rPost->IdContent) ;
    $rPost->message=str_replace('<p><br />\n</p>','',$rPost->message) ;

    $NotificationType='';

    switch ($rr->Type) {
        case 'newthread':
            break ;
        case 'reply':
            $NotificationType='Re: ';
            break ;
        case 'moderatoraction':
        case 'deletepost':
        case 'deletethread':
        case 'useredit':
            $NotificationType=wwinlang("ForumMailbotEditedPost",$MemberIdLanguage);
            break ;
        case 'translation':
            break ;
        case 'buggy':
        default :
          $word->$text="Problem in forum notification Type=".$rr->Type."<br />" ;
            break ;
    }

    // Setting some default values
    $subj = $NotificationType . $rPost->thread_title;
    if ($rPost->IdGroup != 0) {
        $from = "\"BW " . $rPost->Username . "\" <group@bewelcome.org>";
        $subj .= " [" . $rGroupname->Name . "]";
    } else {
        $from = "\"BW " . $rPost->Username . "\" <forum@bewelcome.org>";
    }
    $text = '<html><head><title>'.$subj.'</title></head>' ;
    $text.='<body><table border="0" cellpadding="0" cellspacing="10" width="700" style="margin: 20px; background-color: #fff; font-family:Arial, Helvetica, sans-serif; font-size:12px; color: #333;" align="left">' ;

    if ($rPost->IdGroup != 0) {
        $text.='<tr><th align="left"><a href="'.$baseuri.'forums/s'.$rPost->IdThread.'">'.$rPost->thread_title.'</a> [' . $rGroupname->Name . ']</th></tr>' ;
    } else {
        $text.='<tr><th align="left"><a href="'.$baseuri.'forums/s'.$rPost->IdThread.'">'.$rPost->thread_title.'</a></th></tr>' ;
    }
    $text.='<tr><td>'.wwinlang('PostFrom',$MemberIdLanguage).': <a href="'.$baseuri.'members/'.$rPost->Username.'">'.$rPost->Username.'</a> ('.$rPost->cityname.', '.$rPost->countryname.')</td></tr>' ;
    $text.='<tr><td>'.$rPost->message.'</td></tr>';
    if ($UnsubscribeLink!="") {
       $text .= '<tr><td>'.$UnsubscribeLink.'</td></tr>';
    } else {
        // This case should be for moderators only
        $text .= '<tr><td> IdPost #'.$rr->IdPost.' action='.$NotificationType.'</td></tr>';
    }
    $text .= '</table></body></html>';

    if (!bw_mail($Email, $subj, $text, "", $from, $MemberIdLanguage, "html", "", "")) {
        LogStr("Cannot send posts_notificationqueue=#" . $rr->id . " to <b>".$rPost->Username."</b> \$Email=[".$Email."]","mailbot");
        SetForumNotificationStatus($rr->id, 'Failed');
    } else {
        $countposts_notificationqueue++;
        // Telling that the notification has been sent
        SetForumNotificationStatus($rr->id, 'Sent');
    }
}
$sResult = "<br />".$countposts_notificationqueue . " forum notification sent <br \>";


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
    messages.Status = 'ToSend' AND
    messages.MessageType = 'MemberToMember'";
$qry = sql_query($str);

$count = 0;
while ($rr = mysql_fetch_object($qry)) {
//    if (($rr->MemberStatus!='Active')and ($rr->MemberStatus!='ActiveHidden')) {  // Messages from not actived members will not be send this can happen because a member can have been just banned, unless it is a reply
    if (($rr->MemberStatus!='Active')and ($rr->MemberStatus!='ActiveHidden')and ($rr->MemberStatus!='NeedMore')and ($rr->MemberStatus!='Pending')) {  // Messages from not actived members will not be send this can happen because a member can have been just banned, unless it is a reply

        if (IsLoggedIn()) {
            echo "Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")<br>\n" ;
        }
        $str = "
UPDATE
    messages
SET
    Status = 'Freeze'
WHERE
    id = $rr->id and IdParent=0
        ";
        sql_query($str);
        LogStr("Mailbot refuse to send message #".$rr->id." Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")","mailbot");
        continue ;
    }

    if ($_SESSION['Param']->MailBotMode!='Auto') {
        echo "messages <b> Going to Get Email for IdMember : [".$rr->IdReceiver."]</b> messages.id=".$rr->id."<br>" ;
    }
    $Email = GetEmail($rr->IdReceiver);
    $MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);
    $subj = ww("YouveGotAMail", $rr->Username);
    $urltoreply = $baseuri."messages/{$rr->id}/reply";
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
//            $MessageFormatted .= '<img alt="picture of '.$rr->Username.'" height="200px" src="'.$baseuri.$rImage->FilePath.'"/>';
            $MessageFormatted .= PictureInMail($rr->Username);
        }
        if  (($rr->MemberStatus=='NeedMore')) {
            LogStr("Mailbot procceds sending  message #".$rr->id." Message from Sender".$rr->Username."not active (".$rr->MemberStatus.")","mailbot");
            $MessageFormatted=$MessageFormatted."<br>Message sent by a may be not yet verified member<br>" ;
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

$sResult = $sResult.$count . " intermember Messages sent";
if ($countbroadcast>0) {
    $sResult=$sResult. " and ".$countbroadcast. " broadcast messages sent" ;
}


    $str="select * from volunteers_reports_schedule where Type='Accepter' and TimeToDeliver<now() " ;
    $qryV=sql_query($str);
    while ($rrV = mysql_fetch_object($qryV)) {
        $AccepterReport="<table>" ;

        $StrUpdate="update volunteers_reports_schedule set TimeToDeliver=date_add( TimeToDeliver, INTERVAL DelayInHourForNextOne hour) where id=".$rrV->id ;
        sql_query($StrUpdate);

        $IdVolunteer=$rrV->IdVolunteer ;

        $rr=LoadRow("SELECT concat(concat(' confirmed signup members since ',DelayInHourForNextOne),' Hours') as 'Desc',count(*) as cnt
FROM `members_updating_status`,volunteers_reports_schedule
where volunteers_reports_schedule.IdVolunteer=".$IdVolunteer." and (members_updating_status.created>date_sub( now( ) , INTERVAL DelayInHourForNextOne hour ))
and (OldStatus='mailtoconfirm' and NewStatus='Pending') group by NewStatus") ;
        if (isset($rr->Desc)) {
            $AccepterReport=$AccepterReport."<tr><td colspan=\"2\">".$rr->cnt." ".$rr->Desc."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No confirmed signup</td><td></td></tr>" ;
        }

        $rr=LoadRow("SELECT concat(concat(' accepted members since ',DelayInHourForNextOne),' Hours') as 'Desc',count(*) as cnt
FROM `members_updating_status`,volunteers_reports_schedule
where volunteers_reports_schedule.IdVolunteer=".$IdVolunteer." and (members_updating_status.created>date_sub( now( ) , INTERVAL DelayInHourForNextOne hour ))
and (OldStatus='Pending' and NewStatus='Active') group by NewStatus") ;
        if (isset($rr->Desc)) {
            $AccepterReport=$AccepterReport."<tr><td colspan=\"2\">".$rr->cnt." ".$rr->Desc."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No accepted</td><td></td></tr>" ;
        }

        $rr=LoadRow("SELECT concat(concat(' members set to Needmore (may be duplicated) since ',DelayInHourForNextOne),' Hours') as 'Desc',count(*) as cnt
FROM `members_updating_status`,volunteers_reports_schedule
where volunteers_reports_schedule.IdVolunteer=".$IdVolunteer." and (members_updating_status.created>date_sub( now( ) , INTERVAL DelayInHourForNextOne hour ))
and (OldStatus='Pending' and NewStatus='NeedMore') group by NewStatus ") ;
        if (isset($rr->Desc)) {
        $AccepterReport=$AccepterReport."<tr><td colspan=\"2\">".$rr->cnt." ".$rr->Desc."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No needmore in the period</td><td></td></tr>" ;
        }

        $rPref=LoadRow("select memberspreferences.* from memberspreferences,preferences where preferences.codeName='PreferenceLocalTimeDesc' and preferences.id=memberspreferences.IdPreference and memberspreferences.IdMember=".$IdVolunteer );
        $iSecondOffset=0 ;
        $iSecondOffset=$iSecondOffset+$_SESSION['Param']->DayLightOffset ; // We force the use of daylight offset
        if (isset($rPref->Value)) {
            $iSecondOffset=$iSecondOffset+$rPref->Value ;
        }

        $rr=LoadRow("select concat(' total members are waiting for accepting at ',date_add(now(), INTERVAL ".$iSecondOffset." second)) as 'Desc',count(*)  as cnt from members where Status='Pending'") ;
        if (isset($rr->Desc)) {
            $AccepterReport=$AccepterReport."<tr><td colspan=\"2\"><b>".$rr->cnt."</b> ".$rr->Desc."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No one waiting for accepting in the period</td><td></td></tr>" ;
        }
        $str = "SELECT SQL_CACHE Scope,Level FROM rightsvolunteers,rights WHERE IdMember=".$IdVolunteer." AND rights.id=rightsvolunteers.IdRight AND rights.Name='Accepter'";
        $rr=LoadRow($str) ;

        if (isset($rr->Scope)and $rr->Level>1) {
            $AccepterScope = $rr->Scope ;
            $AccepterScope = str_replace("\"", "'", $AccepterScope); // replace all " with '
            $AccepterScope = str_replace(";", ",", $AccepterScope); // replace all ; with ,
            if (($AccepterScope=="All")or($AccepterScope=="'All'")) {
                $rCount=LoadRow("select count(*)  as cnt from members where Status='Pending'") ;
                $AccepterReport=$AccepterReport."<tr><td coslpan=\"2\">Your accepting Scope is for <b>all</b> countries</td></tr>" ;
                if (!empty($rCount->cnt)) {
                    $AccepterReport=$AccepterReport."<tr><td coslpan=\"2\"  bgcolor=\"yellow\">They are ".$rCount->cnt." pending members <a href=\"http://www.bewelcome.org/bw/admin/adminaccepter.php\">you could accept</a></td></tr>" ;
                }
                else {
                    $AccepterReport=$AccepterReport."<tr><td coslpan=\"2\"  bgcolor=\"lime\"> No pending member you can accept</td></tr>" ;
                }
            }
            else {
                $rCount=LoadRow("select count(*)  as cnt from members,countries,cities where Status='Pending' and cities.id=members.IdCity and cities.IdCountry=countries.id and (cities.IdCountry in (".$AccepterScope.") or  countries.Name in (".$AccepterScope."))") ;
                $AccepterReport=$AccepterReport."<tr><td coslpan=\"2\">Your accepting Scope is for ".$AccepterScope."</td></tr>" ;
                if (!empty($rCount->cnt)) {
                    $AccepterReport=$AccepterReport."<tr><td coslpan=\"2\"  bgcolor=\"yellow\"> They are ".$rCount->cnt." pending members <a href=\"http://www.bewelcome.org/bw/admin/adminaccepter.php\">you could accept</a></td></tr>" ;
                }
                else {
                    $AccepterReport=$AccepterReport."<tr><td coslpan=\"2\"  bgcolor=\"lime\"> No pending member you can accept</td></tr>" ;
                }
            }
        }

        $rr=LoadRow("SELECT concat(concat(' rejected members within the last ',DelayInHourForNextOne),' Hours') as 'Desc',count(*) as cnt
FROM `members_updating_status`,volunteers_reports_schedule
where volunteers_reports_schedule.IdVolunteer=".$IdVolunteer." and (members_updating_status.created>date_sub( now( ) , INTERVAL DelayInHourForNextOne hour ))
and (NewStatus='Rejected') group by NewStatus ") ;
        if (isset($rr->Desc)) {
            $AccepterReport=$AccepterReport."<tr><td colspan=\"2\">".$rr->cnt." ".$rr->Desc."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No member rejected in the period</td><td></td></tr>" ;
        }

        $rr=LoadRow("SELECT concat(concat(' members have left by themself',DelayInHourForNextOne),' Hours') as 'Desc',count(*) as cnt
FROM `members_updating_status`,volunteers_reports_schedule
where volunteers_reports_schedule.IdVolunteer=".$IdVolunteer." and (members_updating_status.created>date_sub( now( ) , INTERVAL DelayInHourForNextOne hour ))
and (OldStatus='Active' and NewStatus='AskToLeave') group by NewStatus") ;
        if (isset($rr->Desc)) {
        $AccepterReport=$AccepterReport."<tr><td colspan=\"2\">".$rr->cnt." ".$rr->Desc."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No member who have left by themself in the period</td><td></td></tr>" ;
        }

        $rr=LoadRow("SELECT concat(concat('Number of members who have been TakenOut by support team because they requested it ',DelayInHourForNextOne),' Hours') as 'Desc',count(*) as cnt
FROM `members_updating_status`,volunteers_reports_schedule
where volunteers_reports_schedule.IdVolunteer=".$IdVolunteer." and (members_updating_status.created>date_sub( now( ) , INTERVAL DelayInHourForNextOne hour ))
and (OldStatus='Active' and NewStatus='AskToLeave') group by NewStatus") ;
        if (isset($rr->Desc)) {
            $AccepterReport=$AccepterReport."<tr><td>".$rr->Desc."</td><td>".$rr->cnt."</td></tr>" ;
        }
        else {
            $AccepterReport=$AccepterReport."<tr><td>No member who have Been TakenOut by support team because they requested it in the period</td><td></td></tr>" ;
        }
        $AccepterReport=$AccepterReport."</table>" ;

        $SenderMail="noreply@bewelcome.org" ;
        $subj="Accepter's report" ;
        $Email = GetEmail($IdVolunteer);

        $text=$AccepterReport ;
        if (!bw_mail($Email, $subj, $text, "", $SenderMail, 0, "html", "", "", "<br /><a href='http://www.bewelcome.org'>BeWelcome site</a><br/> This is an automatic email. Do not answer it but accept the pending members !")) {
            LogStr("Cannot send report to IdVolunteer=#" . $IdVolunteer . " \$Email=[".$Email."]","mailbot");
        }
        else {
            $sResult=$sResult."<br />Accepter report sent to ".$Email ;
        }
        if (IsLoggedIn()) {
            echo "to ".$Email."<br />".$AccepterReport ;
        }
    } // end of while


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

/*
*
*
* fTrad is a duplicate of the MOD_WORD::fTrad function
* todo : find a way to use MOD_WORD::fTRAD instead ! (jeanyves 4/12/2008)
*
*
*/


    /**
     * @param $IdTrad the id of a forum_trads.IdTrad record to retrieve
      * @param $ReplaceWithBr allows
     * @return string translated according to the best language find
     */
    function fTrad($IdTrad,$ReplaceWithBr=false,$IdForceLanguage=-1) {

        global $fTradIdLastUsedLanguage ; // Horrible way of returning a variable you forget when you designed the method (jyh)
        $fTradIdLastUsedLanguage=-1 ; // Horrible way of returning a variable you forget when you designed the method (jyh)
                                                                                    // Will receive the choosen language

        $AllowedTags = "<b><i><br><p><img><ul><li><strong><a>"; // This define the tags wich are not stripped inside a forum_trads
        if (empty($IdTrad)) {
           return (""); // in case there is nothing, return and empty string
        }
        else  {
           if (!is_numeric($IdTrad)) {
              die ("it look like you are using forum::fTrad with and allready translated word, a forum_trads.IdTrad is expected and it should be numeric !") ;
           }
        }

        if ($IdForceLanguage<=0) {
            if (isset($_SESSION['IdLanguage'])) {
                $IdLanguage=$_SESSION['IdLanguage'] ;
            }
            else {
                $IdLanguage=0 ; // by default language 0
            }
        }
        else {
            $IdLanguage=$IdForceLanguage ;
        }
        // Try default language
        $query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=".$IdLanguage ;
        $q = sql_query($query);
        $row = mysql_fetch_object($q) ;
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                LogStr("Blank Sentence for language " . $IdLanguage . " with forum_trads.IdTrad=" . $IdTrad, "Bug");
            }
            else {
                        $fTradIdLastUsedLanguage=$row->IdLanguage ;
                return (strip_tags(ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try default eng
        $query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad." and `IdLanguage`=0" ;
        $q = sql_query($query);
        $row = mysql_fetch_object($q) ;
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                LogStr("Blank Sentence for language 1 (eng) with forum_trads.IdTrad=" . $IdTrad, "Bug");
            } else {
                 $fTradIdLastUsedLanguage=$row->IdLanguage ;
                return (strip_tags(ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try first language available
        $query ="SELECT SQL_CACHE `Sentence`,`IdLanguage` FROM `forum_trads` WHERE `IdTrad`=".$IdTrad."  order by id asc limit 1" ;
        $q = sql_query($query);
        $row = mysql_fetch_object($q) ;
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                LogStr("Blank Sentence (any language) forum_trads.IdTrad=" . $IdTrad, "Bug");
            } else {
                 $fTradIdLastUsedLanguage=$row->IdLanguage ;
               return (strip_tags(ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        $strerror="fTrad Anomaly : no entry found for IdTrad=#".$IdTrad ;
        LogStr($strerror, "Bug");
        return ($strerror); // If really nothing was found, return an empty string
    } // end of fTrad

    function PictureInMail($Username) {
       $PictureFilePath='http://www.bewelcome.org/members/avatar/'. $Username;
       $rval= '<img alt="picture of ' . $Username.'" src="'.$PictureFilePath.'"/>';
        return($rval) ;
    } // End of PictureInMail

    function SetForumNotificationStatus($notifId, $status) {
        $str = "
            UPDATE
                posts_notificationqueue
            SET
                posts_notificationqueue.Status = '" . $status . "'
            WHERE
                posts_notificationqueue.id = $notifId
            ";
        sql_query($str);
    }
