<?php
/**
 * Message view
 *
 * @package message
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class MessageView extends PAppView 
{
    /**
     * @var Message
     * @access private
     */
    private $_model;
    
    /**
     * @param Message $model
     */
    public function __construct(Message $model) 
    {
        $this->_model = $model;
    }

    public function userbar()
    {   
        $userbarText = array();
        $i18n = new MOD_i18n('apps/message/userbar.php');
        $userbarText = $i18n->getText('userbarText');

        require TEMPLATE_DIR.'apps/message/userbar.php';
    }   


    public function writeForm($callbackId)
    {
        $User = APP_User::login();
        $Msg = new Message;
        $vars =& PPostHandler::getVars($callbackId);
        $request = PRequest::get()->request;

        

        $writeText = array();
        $errorText = array();
        $i18n = new MOD_i18n('apps/message/write.php');
        $writeText = $i18n->getText('writeText');
        $errorText = $i18n->getText('errorText');

        // urls 'msg/write/<name>' do autofill recipient
        if(isset($request[2]))
            $vars['r'] = $request[2];

        if (isset($vars['errors']) && is_array($vars['errors']))
            $errors = $vars['errors'];
        else
            $errors = array();

        if (isset($request[2]) && $request[2] == 'finish') {
            echo '<h2>'.$writeText['finish_write_title']."</h2>\n";
            echo '<p>'.$writeText['finish_write_text']."</p>\n";
            echo '<p>'.$writeText['finish_write_info']."</p>\n";
            return;
        }


        require TEMPLATE_DIR.'apps/message/writeform.php';
    }

    public function inbox($callbackId, $messages)
    {
        $i18n = new MOD_i18n('apps/message/inbox.php');
        $inboxText = array();
        $inboxText = $i18n->getText('inboxText');
        $i18n = new MOD_i18n('date.php');
        $format = array();
        $format = $i18n->getText('format');
        $request = PVars::get()->request;
        
        require TEMPLATE_DIR.'apps/message/inbox.php';
    }

    public function outbox($callbackId, $messages)
    {
        $i18n = new MOD_i18n('apps/message/outbox.php');
        $outboxText = array();
        $outboxText = $i18n->getText('outboxText');
        $i18n = new MOD_i18n('date.php');
        $format = array();
        $format = $i18n->getText('format');
        $request = PVars::get()->request;

        require TEMPLATE_DIR.'apps/message/outbox.php';
    }

    public function message($callbackDeleteId, $message)
    {
        $i18n = new MOD_i18n('apps/message/message.php');
        $msgText = array();
        $msgText = $i18n->getText('msgText');
        $i18n = new MOD_i18n('date.php');
        $format = array();
        $format = $i18n->getText('format');
        $User = APP_User::login();
        $U = new User();
        $is_outbox = ($User->getId() == $message->sender_id_foreign);
        $request = PRequest::get()->request;

        require TEMPLATE_DIR.'apps/message/message.php';
    }


}
?>
