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
        $this->_model=$model = $this->getModel();

        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

        $formkit = $layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('MessagesController', 'sendMessageCallback');

        // defaults
        $text = '';
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

        if (!$memory = $formkit->getMemFromRedirect()) {
            // no memory
            // echo 'no memory';
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
                require_once SCRIPT_BASE . 'build/messages/templates/compose_warning.php';
            }
        }

        require_once SCRIPT_BASE . 'build/messages/templates/compose.php';
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

    public function getLateLoadScriptFiles()
    {
        $scripts = parent::getLateLoadScriptfiles();
        if ($this->sender && $this->sender->getPreference("PreferenceDisableTinyMCE", $default = "No") == 'No') {
            $scripts[] = 'tinymce-4.0.28/tinymce.min.js';
            $scripts[] = 'tinymceconfig_noimages.js?1';
        }
        return $scripts;
    }
}
