<?php


/**
 * Page for inviting a friend
 * 
 * @package inviteafriend
 * @author Micha (lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class InvitePage extends RoxPageView
{

    protected function teaserContent() {
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();

        ?>
        <div id="teaser" class="page-teaser clearfix">
            <div id="teaser_l1"> 
                <h1><?php echo $words->getFormatted("InviteAFriend"); ?></h1>
            </div>
        </div><?php
    }

    /**
     * show empty column by default
     */
    protected function column_col2()
    {

    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();
        $member = $model->getMember($_SESSION['IdMember']);
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('InviteController', 'InviteCallback');
        
        // defaults
        $email = '';
        $subject = $words->get("MailInviteAFriendSubject", $member->name(),$_SESSION['Username']);
        $urltosignup = PVars::getObj('env')->baseuri.'signup';
        $text = str_replace('<br />','',$words->getFormatted('InviteAFriendStandardText','<a href="'.PVars::getObj('env')->baseuri.'members/'.$_SESSION["Username"].'">'.$_SESSION["Username"].'</a>',$urltosignup));

        if (!$memory = $formkit->getMemFromRedirect()) {
            // no memory
        } else {
            // from previous form
            if ($memory->post) {
                if (isset($memory->post['email'])) {
                    $email = $memory->post['email'];
                }
                if (isset($memory->post['subject'])) {
                    $subject = $memory->post['subject'];
                }
                if (isset($memory->post['text'])) {
                    $text = $memory->post['text'];
                }
            }
            
            if ($memory->expired) {
                ?><p><?php echo $words->getFormatted("InviteSessionExpired"); ?></p><?php
            } else if ($memory->already_sent_as) {
                if ($message = $this->getModel()->getMessage($memory->already_sent_as)) {
                    ?><p><?php echo $words->getFormatted("InviteMessageAlreadySent"); ?></p>
                    <p><i><?=$message->Message ?></i></p>
                    <p><?php echo $words->getFormatted("InviteMessageSendAgain"); ?></p>
                    <?php
                } else {
                    ?><p><?php echo $words->getFormatted("InviteMessageNotFound"); ?><?php
                }
            }
            
            // problems from previous form
            if (is_array($memory->problems)) {
                $problems = $memory->problems;
            }
        }
        
        require_once 'templates/compose.php';
    }
    
    protected function getFieldValues()
    {
        $field_values = array(
            'message_id' => 0,
            'receiver_id' => $this->_recipient->id, 
            'text' => 'type something'
        );
        
        return $field_values;
    }
}

/**
 * Page for inviting a friend
 * 
 * @package inviteafriend
 * @author Micha (lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class InviteSentPage extends RoxPageView
{

    protected function teaserContent() {
    
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        
        ?>
        <div id="teaser" class="page-teaser clearfix">
            <div id="teaser_l1"> 
                <h1><?php echo $words->getFormatted("InviteAFriend"); ?></h1>
            </div>
        </div>
        <?php
    }

    /**
     * show empty column by default
     */
    protected function column_col2()
    {

    }

    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        ?>
        <p class="note big">
            <img src="images/icons/accept.png" class="float_left"><?php echo $words->getFormatted("InviteMoreFriends"); ?></a>
        </p>
        <?
        
    }

    
}



?>
