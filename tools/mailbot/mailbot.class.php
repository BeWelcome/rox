<?php
/**
 * Mailbot is a php script used to automatically send emails to users
 *
 * Copyright (c) 2007 BeVolunteer
 *
 * This file is part of BW Rox.
 *
 * BW Rox is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * BW Rox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/> or•
 * write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,•
 * Boston, MA  02111-1307, USA.
 *
 * @category Tools
 * @package  Mailbot
 * @author   Laurent Savaëte (franskmanen) <laurent.savaete@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) v2
 * @link     http://www.bewelcome.org
 */

$i_am_the_mailbot = true;

// manually define the script base. mailbot MUST be run from the root directory (like php tools/mailbot/mailbot.class.php)
define('SCRIPT_BASE', dirname(__FILE__) . "/../../");

require_once SCRIPT_BASE . 'vendor/autoload.php';
require_once SCRIPT_BASE . 'roxlauncher/roxloader.php';
require_once SCRIPT_BASE . 'roxlauncher/environmentexplorer.php';

/**
 * Mailbot base class
 *
 * @category  Tools
 * @package   Mailbot
 * @author    Laurent Savaëte (franskmanen) <laurent.savaete@gmail.com>
 * @copyright 2012 BeVolunteer Team
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) v2
 * @version   $Id$
 * @link      http://www.bewelcome.org
 */
class Mailbot
{

    protected $count = array(
        'Sent' => 0,
        'Failed' => 0,
        'Freeze' => 0
    );

    /**
     * constructor...
     *
     * @return nothing
     */
    function __construct()
    {
        $this->baseuri = PVars::getObj('env')->baseuri;

        $this->IdTriggerer = 0;   //TODO: set this to bot id

        $this->words = new MOD_words();

        $this->messages_model = new MessagesModel;
        $this->members_model = new MembersModel;

        // setup DB access
        $db_vars = PVars::getObj('config_rdbms');
        if (!$db_vars) {
            throw new PException('DB config error!');
        }
        $this->dao = PDB::get($db_vars->dsn, $db_vars->user, $db_vars->password);
    }

    /**
     * an interface for all DB calls from mailbot
     *
     * @param string $queryString the SQL query to execute
     *
     * @return object the result from the DB call
     */
    protected function queryDB($queryString)
    {
        return $this->dao->query($queryString);
    }

    /**
     * a local replacement for LoadRow
     *
     * @param string $queryString the SQL query to execute
     *
     * @return object the first row returned by the query
     */
    protected function getSingleRow($queryString)
    {
        $qry = $this->queryDB($queryString);
        return $qry->fetch(PDB::FETCH_OBJ);
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        //TODO: check how we are run, and log stuff accordingly
        echo($msg."\n");
    }

    /**
     * actually send out emails using a common BW template
     *
     * @param string $subject   the subject line for the message
     * @param string $from      the email address of the sender
     * @param string $to        the email address of the recipient
     * @param string $body      the plaintext body of the message
     * @param string $title     an optional title to show in the message (HTML H1 tag)
     * @param string $language  the language code used in the message
     * @param bool $html        HTML preference: false -> text-only, true -> multi part (text, html)
     *
     * @return object the result from the MOD_mail::sendEmail call
     */
    protected function sendEmail($subject, $from, $to, $title, $body, $language, $html)
    {
        try {
            return MOD_mail::sendEmail($subject, $from, $to, $title, $body, $language, $html);
        }
        catch (Exception $e) {
            $this->log("Error (" . date("Y-m-d\TH:i:sO") . "): Couldn't send mail to " . $to );
            $this->log($e->getTraceAsString());
        }
        return false;
    }

    protected function getEmailAddress(Member $member) {
        // Nasty hack: Get email address //

        return $member->getEmailWithoutPermissionChecks();
    }

    /**
     * Log results for the bot execution
     *
     * @return nothing
     */
    protected function reportStats()
    {
        // display statistics
        $this->log("Summary for ".get_class($this).":");
        foreach ($this->count as $status => $total) {
            $this->log($total. ' message(s) '.$status);
        }
    }

}
// -----------------------------------------------------------------------------
// broadcast messages for members (massmail)
// -----------------------------------------------------------------------------

/**
 * the mailbot that sends messages for newsletters, etc...
 *
 * @category  Tools
 * @package   Mailbot
 * @author    Laurent Savaëte (franskmanen) <laurent.savaete@gmail.com>
 * @copyright 2012 BeVolunteer Team
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) v2
 * @version   $Id$
 * @link      http://www.bewelcome.org
 */
class MassMailbot extends Mailbot
{
    /**
     * get the list of messages to broadcast from the db
     *
     * @return object the mySQL query result object
     */
    private function _getMessageList()
    {
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
                broadcast.id = broadcastmessages.IdBroadcast AND
                broadcastmessages.IdReceiver = members.id AND
                broadcastmessages.Status = 'ToSend' limit 100
                ";
        return $this->queryDB($str);
    }

    /**
     * update message status in the DB
     *
     * @param int    $id       the message id in the db
     * @param string $status   the status to set for the message
     * @param int    $receiver the id of the email recipient
     *
     * @return object the result from the DB call
     */
    private function _updateMessageStatus($id, $status, $receiver)
    {
        $this->count[$status]++;
        $str = "UPDATE broadcastmessages
            SET Status = '$status'
            WHERE IdBroadcast = $id AND IdReceiver = $receiver";
        return $this->queryDB($str);
    }

    private function _getBroadCastElement($wordCode, $languageId, $username = false, $email = false, $link = false)
    {
        $sentence = "";
        $str = "select SQL_CACHE Sentence,donottranslate from words where code='$wordCode' and IdLanguage='" . $languageId . "'";
        $rr = $this->getSingleRow($str);
        if (isset ($rr->Sentence)){
            $sentence = stripslashes($rr->Sentence);
        }

        if ($sentence == "") {
            $rEnglish = $this->getSingleRow("select SQL_CACHE Sentence,donottranslate from words where code='$wordCode' and IdLanguage=0");
            if (!isset ($rEnglish->Sentence)) {
                $sentence = $wordCode; // The code of the word will be return
            } else {
                $sentence = stripslashes($rEnglish->Sentence);
            }
        }
        if ($username) {
            // backwards compatibility replace %s with username and %% with % (just in case someone
            // wants to send an old newsletter again
            $sentence = str_replace('%s', $username, $sentence);
            $sentence = str_replace('%%', '%', $sentence);

            // replace %username% with real username. allow some different writings.
            $sentence = str_replace('%UserName%', $username, $sentence);
            $sentence = str_replace('%username%', $username, $sentence);
            $sentence = str_replace('%Username%', $username, $sentence);
        }
        if ($email) {
            $sentence = str_replace('%emailaddress%', $email, $sentence);
            $sentence = str_replace('%Emailaddress%', $email, $sentence);
            $sentence = str_replace('%EmailAddress%', $email, $sentence);
        }
        if ($link) {
            $sentence = str_replace('%link%', $link, $sentence);
            $sentence = str_replace('%Link%', $link, $sentence);
        }
        return $sentence;
    }

    /**
     * Actually run the bot
     *
     * @return nothing
     */
    public function run()
    {
        $qry = $this->_getMessageList();
        while ($msg = $qry->fetch(PDB::FETCH_OBJ)) {
            $receiver = new Member($msg->IdReceiver);
            $email = $this->getEmailAddress($receiver);
            $language = $receiver->getLanguagePreferenceId();

            $link = false;
            if ($msg->broadcast_type == 'MailToConfirmReminder') {
                $userId = APP_User::userId($receiver->Username);
                if( !$userId)
                    continue;
                $keyDB = APP_User::getSetting($userId, 'regkey');
                if( !$keyDB)
                    continue;
                $link = $this->baseuri . 'signup/confirm/' . $receiver->Username . '/' . $keyDB->value;
            }

            $subj = $this->_getBroadCastElement("BroadCast_Title_".$msg->word, $language, $msg->Username);
            $text = $this->_getBroadCastElement("BroadCast_Body_".$msg->word, $language, $msg->Username, $email, $link);

            if (empty($msg->EmailFrom)) {
                switch($msg->broadcast_type) {
                    case "RemindToLog":
                    case "MailToConfirmReminder":
                        $sender_mail = "reminder@bewelcome.org";
                        break;
                    case "SuggestionReminder":
                        $sender_mail="suggestions@bewelcome.org" ;
                        break;
                    case "TermsOfUse":
                        $sender_mail="tou@bewelcome.org";
                        break;
                    default:
                        $sender_mail="newsletter@bewelcome.org" ;
                }
            } else {
                $sender_mail=$msg->EmailFrom ;
            }
            $memberPrefersHtml = true;
            if ($receiver->getPreference('PreferenceHtmlMails', 'Yes') == 'No') {
                $memberPrefersHtml = false;
            }
            if (!$this->sendEmail($subj, $sender_mail, $email, $subj, $text, $language, $memberPrefersHtml)) {
                $this->_updateMessageStatus($msg->IdBroadcast, 'Failed', $msg->IdReceiver);
                $this->log("Cannot send broadcastmessages.id=#" . $msg->IdBroadcast . " to <b>".$msg->Username."</b>
                \$Email=[".$email."] Type=[".$msg->broadcast_type."]");
            } else {
                if ($msg->broadcast_type == "RemindToLog") {
                    $this->queryDB("update members set NbRemindWithoutLogingIn=NbRemindWithoutLogingIn+1 where members.id=".$msg->IdReceiver);
                }
                $this->_updateMessageStatus($msg->IdBroadcast, 'Sent', $msg->IdReceiver);
            }
        }
        $this->reportStats();
    }
}

// -----------------------------------------------------------------------------
// Forums/groups notifications
// -----------------------------------------------------------------------------
/**
 * the mailbot that sends messages for forum/group posts
 *
 * @category  Tools
 * @package   Mailbot
 * @author    Laurent Savaëte (franskmanen) <laurent.savaete@gmail.com>
 * @copyright 2012 BeVolunteer Team
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) v2
 * @version   $Id$
 * @link      http://www.bewelcome.org
 */
class ForumNotificationMailbot extends Mailbot
{

    /**
     * get the list of notifications (new posts) to send out
     *
     * @param int $grace_period the number of minutes before which to email out notifications for new posts
     *
     * @return object the mySQL query object
     */
    private function _getNotificationList($grace_period)
    {
        $str = "
            SELECT
                posts_notificationqueue.*,
                Username
            FROM
                posts_notificationqueue
            RIGHT JOIN members ON posts_notificationqueue.IdMember = members.id  AND
                (members.Status = 'Active' OR members.Status = 'ActiveHidden')
            WHERE
                posts_notificationqueue.Status = 'ToSend' AND
                (posts_notificationqueue.created < subtime(now(), sec_to_time($grace_period *60)) OR NOT
                (posts_notificationqueue.Type = 'newthread' OR
                posts_notificationqueue.Type = 'reply'));
                ";
        return $this->queryDB($str);
    }

    /**
     * get the group/forum post from the DB
     *
     * @param int $postId the unique id for the post
     *
     * @return object the data about the post
     */
    private function _getPost($postId)
    {
        return $this->getSingleRow("
            SELECT
                forums_posts.*,
                members.Username,
                members.id AS IdMember,
                forums_threads.title AS thread_title,
                forums_threads.IdTitle,
                forums_threads.threadid AS IdThread,
                forums_threads.IdGroup AS groupId,
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
                forums_posts.postid = $postId AND
                geonames_cache.geonameid = members.IdCity  AND
                geonames_cache2.geonameid = geonames_cache.parentCountryId;"
        );
    }

    private function _getGroupName($groupId) {
        return $this->getSingleRow("
            SELECT
                Name
            FROM
                groups
            WHERE
                id = $groupId
        ");
    }

    /**
     * build a url to unsubscribe from forum notifications
     *
     * @param object $notification     the notification object returned by the SQL query
     * @param string $MemberIdLanguage the language code to use
     * @param integer $post            The associated post
     *
     * @return string the url to unsubscribe
     */
    private function _buildUnsubscribeLink($notification, $MemberIdLanguage, $post)
    {
        $link = '<hr><span class="unsubscribe">' ;
        if ($notification->TableSubscription == 'membersgroups') {
            // Prefer group subscriptions over tags or threads
            $link .= ''
                . $this->words->getFormattedInLang('ForumUnSubscribeGroup', $MemberIdLanguage) . '<br />'
                . '<a href="' .$this->baseuri.'forums/subscriptions/disable/thread/' . $post->IdThread . '">' . $this->words->getFormattedInLang('MailbotDisableThread', $MemberIdLanguage) . '</a><br />'
                . '<a href="' .$this->baseuri.'forums/subscriptions/disable/group/' . $post->groupId . '">' . $this->words->getFormattedInLang('MailbotDisableGroup', $MemberIdLanguage) . '</a><br />'
                . '<a href="' .$this->baseuri.'forums/subscriptions/unsubscribe/group/' . $post->groupId . '">' . $this->words->getFormattedInLang('MailbotUnsubscribeGroup', $MemberIdLanguage) . '</a>'
                . '</span>';
            ;
        } elseif ($notification->IdSubscription!=0) {
            // Compute the unsubscribe link according to the table where the subscription was coming from
            $rSubscription = $this->getSingleRow(
                "SELECT
                  *
                FROM
                  $notification->TableSubscription
                WHERE
                  id = $notification->IdSubscription"
            );
            if ($notification->TableSubscription == "members_threads_subscribed") {
                $link .= '<a href="'.$this->baseuri.'forums/subscriptions/unsubscribe/thread/'.$rSubscription->id.'/'.$rSubscription->UnSubscribeKey.'">'.$this->words->getFormattedInLang('MailbotUnsubscribeThread', $MemberIdLanguage).'</a><br>';
                $link .= '<a href="' .$this->baseuri.'forums/subscriptions/disable/thread/' . $rSubscription->IdThread .'">'.$this->words->getFormattedInLang('MailbotDisableThread', $MemberIdLanguage).'</a>';
            }
            if ($notification->TableSubscription == "members_tags_subscribed") {
                $link .= '<a href="'.$this->baseuri.'forums/subscriptions/unsubscribe/tag/'.$rSubscription->id.'/'.$rSubscription->UnSubscribeKey.'">'.$this->words->getFormattedInLang('MailbotUnsubscribeTag', $MemberIdLanguage).'</a><br>';
                $link .= '<a href="' .$this->baseuri.'forums/subscriptions/disable/tag/' . $rSubscription->IdTag .'">'.$this->words->getFormattedInLang('MailbotDisableTag', $MemberIdLanguage).'</a>';
            }
        }
        $link .= '</span></div>';
        return $link;
    }

    /**
     * update the message status in the DB
     *
     * @param int    $id     the message identifier in the DB
     * @param string $status the status to set for the message
     *
     * @return nothing
     */
    private function _updateNotificationStatus($id, $status)
    {
        $str = "
            UPDATE
                posts_notificationqueue
            SET
                posts_notificationqueue.Status = '$status'
            WHERE
                posts_notificationqueue.id = $id
            ";
        $this->count[$status]++;
        $this->queryDB($str);
    }

    /**
     * return the formatted email content for $msg
     *
     * @param object $notification the message notification object as returned by mysql_fetch_object
     * @param object $post         the post returned by the SQL query
     * @param object $author       the member who wrote the post
     * @param string $language     the language code used for the message
     *
     * @return string the formatted email message body
     */
    private function _buildMessage($notification, $post, $author, $language)
    {
        $msg = array();
        $NotificationType = '';
        switch ($notification->Type) {
        case 'newthread':
            break ;
        case 'reply':
            $NotificationType = 'Re: ';
            break ;
        case 'moderatoraction':
        case 'deletepost':
        case 'deletethread':
        case 'useredit':
            $NotificationType = $this->words->getFormattedInLang("ForumMailbotEditedPost", $language);
            break ;
        case 'translation':
            break ;
        case 'buggy':
        default :
            break ;
        }

        $msg['subject'] = $NotificationType . $post->thread_title;
        if ($post->groupId) {
            $msg['subject'] .= ' [' . $this->_getGroupName($post->groupId)->Name . ']';
            $msg['title'] = '<a href="' .$this->baseuri. 'groups/' . $post->groupId . '/forum/s' . $post->IdThread .'">' . $msg['subject'] . '</a>';
        } else {
            $msg['title'] = '<a href="' .$this->baseuri. '/forums/s' . $post->IdThread . '">' . $msg['subject'] . '</a>';
        }

        $text = $post->message;

        $UnsubscribeLink = $this->_buildUnsubscribeLink($notification, $language, $post);
        if ($UnsubscribeLink!="") {
            $text .= $UnsubscribeLink;
        } else {
            // This case should be for moderators only
            $text .= 'IdPost #' . $notification->IdPost . ' action=' . $NotificationType;
        }

        $msg['body'] = $text;

        return $msg;
    }

    /**
     * Actually run the bot
     *
     * @param integer grace_period Wait for grace period minutes before sending email notifications to allow author to edit post
     * @return nothing
     */
    public function run($grace_period = 5)
    {
        $qry = $this->_getNotificationList($grace_period);
        while ($notification = $qry->fetch(PDB::FETCH_OBJ)) {

            // Skip to next item in queue if there was no result from database
            $post = $this->_getPost($notification->IdPost);
            if (!is_object($post)) {
                continue;
            }

            $author = $this->members_model->getMemberWithId($post->IdWriter);
            $recipient = $this->members_model->getMemberWithId($notification->IdMember);
            $MemberIdLanguage = $recipient->getLanguagePreferenceId();

            // Rewrite the title and the message to the corresponding default language for this member if any
            $post->thread_title = $this->words->fTrad($post->IdTitle, $MemberIdLanguage);
            $post->message = $this->words->fTrad($post->IdContent, $MemberIdLanguage);
            $post->message = str_replace('<p><br>\n</p>', '', $post->message);

            $msg = $this->_buildMessage($notification, $post, $author, $MemberIdLanguage);

            if ($post->groupId) {
                $from = array('group@bewelcome.org' => '"BeWelcome - ' . $author->Username . '"');
            } else {
                $from = array('forum@bewelcome.org' => '"BeWelcome - ' . $author->Username . '"');
            }

            $to = $this->getEmailAddress($recipient);
            if (empty($to)) {
                continue;
            }
            $memberPrefersHtml = true;
            if ($recipient->getPreference('PreferenceHtmlMails', 'Yes') == 'No') {
                $memberPrefersHtml = false;
            }
            if (!$this->sendEmail($msg['subject'], $from, $to, $msg['title'], $msg['body'], $MemberIdLanguage,
                $memberPrefersHtml)) {
                $this->_updateNotificationStatus($notification->id, 'Failed');
                $this->log("Could not send posts_notificationqueue=#" . $notification->id . " to <b>".$post->Username
                    ."</b> \$Email=[" . $to . "]");
            } else {
                $this->_updateNotificationStatus($notification->id, 'Sent');
            }
        }
        $this->reportStats();

    }
} // class ForumNotificationMailbot


// -----------------------------------------------------------------------------
// Normal messages between members
// -----------------------------------------------------------------------------
/**
 * the mailbot that sends private messages between members
 *
 * @category  Tools
 * @package   Mailbot
 * @author    Laurent Savaëte (franskmanen) <laurent.savaete@gmail.com>
 * @copyright 2012 BeVolunteer Team
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) v2
 * @version   $Id$
 * @link      http://www.bewelcome.org
 */
class MemberToMemberMailbot extends Mailbot
{
    /**
     * get the list of messages to be sent from the database
     *
     * @return object a mysql query object
     */
    private function _getMessageList()
    {
        return $this->messages_model->filteredMailbox(
            array(
                'messages.Status = "ToSend"',
                'messages.MessageType = "MemberToMember"'
                )
        );
    }

    /**
     * return the formatted email content for $msg
     *
     * @param object $message the msg object as returned by the SQL query
     * @param bool   $html    whether to format message in html (true) or plaintext (false)
     *
     * @return string the formatted email message body
     */
    private function _formatMessage($message)
    {
        $inboxUrl = $this->baseuri."messages";
        $messageUrl = $inboxUrl . '/' . $message->id;
        $purifier = MOD_htmlpure::get()->getPurifier();
        $direction_in = true;   // true means received message (false is sent)

        $contact_username = $this->Sender->Username;
        $contactProfileUrl = $this->baseuri.'members/'.$contact_username;
        $member = $this->Sender;

        $languages = $this->Sender->get_languages_spoken();
        $words = $this->words;
        $templateUsedInEmail = true;
        $baseuri = $this->baseuri;

        ob_start();
        include SCRIPT_BASE . 'tools/mailbot/templates/readMessage.php';
        $text = ob_get_contents();
        ob_end_clean();

        return $text;
    }

    /**
     * update the DB with new message statuses
     *
     * @param int    $msgId       the id of the message for which to update the DB
     * @param string $status      the status to set for the message
     * @param int    $IdTriggerer the user id of the user running the bot (default to 0)
     *
     * @return nothing
     */
    private function _updateMessageStatus($msgId, $status, $IdTriggerer = 0)
    {
        $status_values = Array('Sent', 'Failed', 'Freeze');
        if (!in_array($status, $status_values)) {
            die("ERROR! Mailbot is trying to set some incorrect Status for a message.");
        }

        $this->messages_model->markSent($msgId, $status, $IdTriggerer);

        $this->count[$status]++;
    }

    /**
     *
     */
    private function _calculateReplyAddress() {
        return PVars::getObj('syshcvol')->MessageSenderMail;
    }

    /**
     * Actually run the bot
     *
     * @return nothing
     */
    public function run()
    {

        $msg_list = $this->_getMessageList();

        foreach ($msg_list as $msg) {
            $FreezeMsgFor = Array('Active', 'ActiveHidden', 'NeedMore', 'Pending');
            $this->Sender = $this->members_model->getMemberWithId($msg->IdSender);
            $this->Receiver = $this->members_model->getMemberWithId($msg->IdReceiver);

            if (!in_array($this->Sender->Status, $FreezeMsgFor)) {
                // Don't send messages from e.g. banned members, unless it is a reply
                // TODO: replies are marked with IdParent != 0 in DB, check that earlier than in markMsgStatus if possible

                $this->_updateMessageStatus($msg->id, 'Freeze');
                $this->log("Message ".$msg->id." from ". $this->Sender->Username." is rejected ("
                    .$this->Sender->Status.")");
            } else {
                $from = array($this->_calculateReplyAddress() => '"BeWelcome - ' . $msg->senderUsername . '"' );
                $to = $this->getEmailAddress($this->Receiver);
                if (empty($to)) {
                    $this->_updateMessageStatus($msg->id, 'Failed');
                    continue;
                }
                $MemberIdLanguage = $this->Receiver->getLanguagePreference();
                $memberPrefersHtml = true;
                if ($this->Receiver->getPreference('PreferenceHtmlMails', 'Yes') == 'No') {
                    $memberPrefersHtml = false;
                }
                $subject = $this->words->get("YouveGotAMail", $this->Sender->Username);
                $title = $this->words->get("YouveGotAMail", '<a href="https://www.bewelcome.org/members/' . $this->Sender->Username . '"">'
                    . $this->Sender->Username . '</a>');
                $body = $this->_formatMessage($msg);

                // send email and update DB according to result
                if (!$this->sendEmail($subject, $from, $to, $title, $body, $MemberIdLanguage, $memberPrefersHtml)) {
                    $this->_updateMessageStatus($msg->id, 'Failed');
                    $this->log("Cannot send messages.id=#" . $msg->id . " to <b>".$this->Receiver->Username."</b> \$Email=[".$to."]");
                } else {
                    $this->_updateMessageStatus($msg->id, 'Sent');
                }
            }
        }
        $this->reportStats();
    }

}   // class MemberToMemberMailbot
/**
 * main function instantiating and running the mailbots
 *
 * @return none
 */
function runMailbots()
{
    // load Rox environment
    $env_explorer = new EnvironmentExplorer;
    $env_explorer->initializeGlobalState();

    $m2mbot = new MemberToMemberMailbot();
    $m2mbot->run();

    $forum_bot = new ForumNotificationMailbot();
    $forum_bot->run();

    $massmailbot = new MassMailbot();
    $massmailbot->run();
}

runMailbots();

?>
