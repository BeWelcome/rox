<?php


/**
 * Page for writing a new message
 * 
 * @package messages
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ComposeMessagePage extends MessagesBasePage
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();
        
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        
        // defaults
        $text = 'type your message';
        $attach_picture = '';
        
        if ($message = $this->message) {
            $receiver = $model->getMember($message->receiverUsername);
            $text = $message->Message;
            $attach_picture = ($message->JoinMemberPict == 'yes' ? ' checked' : ''); 
        } else if (!$receiver = $this->receiver) {
            // can't show message...
            echo 'no receiver set.';
            return;
        }
        $receiver_username = $receiver->Username;
        $receiver_id = $receiver->id;
        
        if (!$memory = $this->memory) {
            // no memory
        } else {
            
            // from previous form
            if ($memory->post) {
                if (isset($memory->post['text'])) {
                    $text = $memory->post['text'];
                }
                if (isset($memory->post['attach_picture'])) {
                    $attach_picture = ' checked';
                }
            }
            
            if ($memory->expired) {
                ?>your session has expired while you were typing. Try again?<?php
            } else if ($memory->already_sent_as) {
                if ($message = $this->getModel()->getMessage($memory->already_sent_as)) {
                    ?><p>Looks like you've already sent this message as</p>
                    <p><i><?=$message->Message ?></i></p>
                    <p>Do you want to send again with the modified text below?</p>
                    <?php
                } else {
                    ?>looks like you've already sent this message, but system can't find it. Send again?<?php
                }
            }
            
            // problems from previous form
            if (is_array($memory->problems)) {
                ?>
                <h3>There were problems in your sent data.</h3>
                <table>
                <tr><th>Field name</th><th>Problem</th></tr>
                <?php
                foreach ($memory->problems as $key => $value) {
                    ?>
                    <tr>
                    <td><?=$key ?></td>
                    <td><?=$value ?></td>
                    </tr>
                    <?php
                }
                ?>
                </table>
                <?php
            }
        }
        
        ?>
        <h3>Message to <a href="bw/member.php?cid=<?=$receiver_username ?>"><?=$receiver_username ?></a></h3>
        
        <form method="POST" action="<?=$page_url ?>">
            
            <?=$layoutkit->registerPosthandlerCallback('MessagesController', 'sendMessageCallback') ?>
            
            <?php if ($receiver_username) { ?>
            <input type="hidden" name="receiver_id" value="<?=$receiver_id ?>"/>
            <?php } else { ?>
            <p>To: <input name="receiver_username"/></p>
            <?php } ?>
            
            <p>
                <textarea name="text" rows="15" cols="80"><?=$text ?></textarea>
            </p>
            
            <p>
                I confirm that I have read the
                <a href="http://www.bevolunteer.org/wiki/index.php/Spam_Info_Page">Infos about Spam</a>
                and agree with them.
            </p>
            
            <p>
                <input type="checkbox" name="agree_spam_policy" id="IamAwareOfSpamCheckingRules">
                <label for="IamAwareOfSpamCheckingRules">I agree</label>
            </p>
            
            <p>
                <input type="checkbox" name="attach_picture" id="JoinMemberPict"<?=$attach_picture ?>/>
                <label for="JoinMemberPict">Attach my profile picture</label>
            </p>
            
            <p>
                <input type="submit" value="send"/>
            </p>
        
        </form>
        
        <?php
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




?>