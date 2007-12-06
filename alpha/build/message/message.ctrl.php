<?php
/**
 * Message controller
 *
 * @package msg
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class MessageController extends PAppController 
{
    /**
     * @var Message
     * @access private
     */
    private $_model;
    /**
     * @var MessageView
     * @access private
     */
    private $_view;
    
    /**
     * @param void
     */
    public function __construct() 
    {
        parent::__construct();
        $this->_model = new Message();
        $this->_view  = new MessageView($this->_model);
    }
    
    /**
     * @param void
     */
    public function __destruct() 
    {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * index
     *
     * msg/write      # write new message
     * msg/write/<username> # write message to given user
     * msg/write/<username>,<username>,... # write message to given userS
     * msg/inbox      # view inbox
     * msg/sent       # view outbox
     * msg/show/<id>  # view message detailed.
     * 
     * @param void
     * @todo  several users (e.g. separate names in url by comma)
     */
    public function index() 
    {
        if (!($User = APP_User::login()))
            PRequest::home();
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = ''; 

        ob_start();
        $this->_view->userbar();
        $str = ob_get_contents();
        ob_end_clean();
        $Page = PVars::getObj('page');
        $Page->content .= $str;

        switch($request[1]) {
            case 'write':
                $callbackId = $this->writeProcess();
                ob_start();
                $this->_view->writeForm($callbackId);
                PPostHandler::clearVars($callbackId);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;


            case 'sent':
                ob_start();
                if (isset($request[2]) && (!isset($request[3]) || (isset($request[3]) && $request[3] != 'finish'))) {
                    if ($request[2] == 'del') {
                        $callbackId = $this->deleteOutboxProcess();
                        PPostHandler::clearVars($callbackId);
                    } else {
                        if ($this->_model->hasOutboxMessage($User->getId(), $request[2])) {
                            $msg = $this->_model->getMessage($request[2]);
                            $callbackDeleteId = $this->deleteOutboxProcess();
                            PPostHandler::clearVars($callbackDeleteId);
                            $this->_view->message($callbackDeleteId, $msg);
                        }
                    }
                }
                else
                {
                    $messages = $this->_model->getOutbox($User->getId());
                    $callbackDeleteId = $this->deleteOutboxProcess();
                    PPostHandler::clearVars($callbackDeleteId);
                    $this->_view->outbox($callbackDeleteId, $messages);
                }

                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;

            case 'inbox':
            default:
                ob_start();
                if (isset($request[2]) && (!isset($request[3]) || (isset($request[3]) && $request[3] != 'finish'))) {
                    if ($request[2] == 'del') {
                        $this->deleteInboxProcess();
                    } else {
                        if ($this->_model->hasInboxMessage($User->getId(), $request[2])) {
                            $this->_model->setMessageSeen($User->getId(), $request[2]);
                            $msg = $this->_model->getMessage($request[2]);
                            $callbackDeleteId = $this->deleteInboxProcess();
                            $this->_view->message($callbackDeleteId, $msg);
                        }
                    }
                }
                else
                {
                    $messages = $this->_model->getInbox($User->getId());
                    $callbackDeleteId = $this->deleteInboxProcess();
                    $this->_view->inbox($callbackDeleteId, $messages);
                }
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;
        }   
    }


    public function deleteInboxProcess()
    {
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;

            $vars =& PPostHandler::getVars();

            if (!isset($vars['del-id'])) {
                return PVars::getObj('env')->baseuri.'message/inbox';
            }
            foreach ($vars['del-id'] as $id => $trash) {
                $this->_model->deleteInboxMessage($User->getId(), $id);
            }

            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.'message/inbox/del/finish';
        } else {
            $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }

    public function deleteOutboxProcess()
    {
        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;

            $vars =& PPostHandler::getVars();
            if (!isset($vars['del-id'])) {
                return PVars::getObj('env')->baseuri.'message/sent';
            }
            foreach ($vars['del-id'] as $id => $trash) {
                $this->_model->deleteOutboxMessage($User->getId(), $id);
            }

            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.'message/sent/del/finish';
        } else {
            $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
    }
    /**  
     * Processing message to be sent.
     *
     * This is a POST callback function.
     *
     *
     * Sets following errors in POST vars:
     * recipient    - invalid(empty) recipient(s) found.
     * recipient_max - too much recipients.
     * subject      - invalid(empty) subject.
     * text         - invalid(empty) text.
     *
     * inserror     - error performing db insertion.
     * tagerror     - error while updating tags.
     */
    
    public function writeProcess() 
    {    

        if (PPostHandler::isHandling()) {
            if (!$User = APP_User::login())
                return false;
            $max_recipients = 10;
            $vars =& PPostHandler::getVars();

            $recipients = array();

            if (isset($vars['r'])) {
                $users = explode(',', $vars['r']);
                $users = array_map('trim', $users);
                if (count($users)>$max_recipients) {
                    $vars['errors'][] = 'recipient_max';
                    return false;
                }
                $similar = array();
                if (false === $this->_findRecipients($users, $User->getId(), $recipients, $similar)) {
                    $vars['errors'][] = 'recipient';
                    $vars['errors']['similar_recipients'] = $similar;
                    $vars['r'] = join(', ', $recipients);
                    return false;
                }
            }
            if (!isset($vars['s']) || !$vars['s']) {
                $vars['errors'][] = 'subject';
            }
            if (!isset($vars['txt']) || !$vars['txt']) {
                $vars['errors'][] = 'text';
            } else {
                $vars['txt'] = $this->_cleanupText($vars['txt']);
            }

            if (isset($vars['errors']))
                return false;

            // prevents that a user clicks the Next button in the
            // recipients-form and the message is sent immediately.
            // will return to the form so user sees message-form again.
            if (isset($vars['submit-validate'])) {
                return false;
            }

            // save message.
            $refcount = count($recipients);
            if (isset($vars['sto'])) ++$refcount;
            $msgId = $this->_model->writeEntry($User->getId(), implode(',', array_keys($recipients)), $vars['s'], $vars['txt'], $refcount);

            // store in outbox of sender.
            if (isset($vars['sto'])) {
                $this->_model->insertOutbox($User->getId(), $msgId);
            }

            // store in inbox of recipients.
            foreach ($recipients as $id=>$handle) {
                $this->_model->insertInbox($id, $msgId);
            }


            PPostHandler::clearVars();
            return PVars::getObj('env')->baseuri.'message/write/finish/';
        } else {
            $callbackId = PFunctions::hex2base64(sha1(__METHOD__));
            PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
            return $callbackId;
        }
     }

    // 2006-11-23 19:14:14 rs Stolen from Blog class :o
    private function _cleanupText($txt)
    {   
        $str = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$txt.'</body></html>';
        $doc = @DOMDocument::loadHTML($str);
        if ($doc) {
            $sanitize = new PSafeHTML($doc);
            $sanitize->allow('html');
            $sanitize->allow('body');
            $sanitize->allow('p');
            $sanitize->allow('div');
            $sanitize->allow('b');
            $sanitize->allow('i');
            $sanitize->allow('u');
            $sanitize->allow('a');
            $sanitize->allow('em');
            $sanitize->allow('strong');
            $sanitize->allow('hr');
            $sanitize->allow('span');
            $sanitize->allow('ul');
            $sanitize->allow('il');
            $sanitize->allow('font');
            $sanitize->allow('strike');
            $sanitize->allow('br');
            $sanitize->allow('blockquote');
            
            $sanitize->allowAttribute('color');
            $sanitize->allowAttribute('bgcolor');
            $sanitize->allowAttribute('href');
            $sanitize->allowAttribute('style');
            $sanitize->allowAttribute('class');
            $sanitize->allowAttribute('width');
            $sanitize->allowAttribute('height');
            $sanitize->allowAttribute('src');
            $sanitize->allowAttribute('alt');
            $sanitize->allowAttribute('title');
            $sanitize->clean();
            $doc = $sanitize->getDoc();
            $nodes = $doc->x->query('/html/body/node()');
            $ret = '';
            foreach ($nodes as $node) {
                $ret .= $doc->saveXML($node);
            }
            return $ret;
        } else {
            // invalid HTML
            return '';
        }

    }

    /**
     * Looks for the given recipients. All matching
     * handles are filled into $real. while for those
     * that do not match any user we search for nicks
     * containing those names and put it into $similar.
     *
     * @arg array $r Recipients' handles.
     * @return boolean FALSE If one of the recipients does not match exactly otherwise TRUE.
     * @arg array reference $real The id=>handle of the users listed in recipients.
     * @arg array reference $similar Will be filled with handles that contain the previously unmatched recipients.
     */
    private function _findRecipients($r, $userId, &$real, &$similar)
    {
        # maximum number of similar handles we supply.
        $numsimilar = 30;
        # minimal name length
        $minlength = 4;


        $query = ' 
SELECT
    u.`id`,
    u.`handle`
FROM `user` AS u
WHERE u.`id`<>'.$userId.' AND (1=0
        ';
        foreach ($r as $u) {
            if (strlen($u)<$minlength)
                return false;
            $query .= "\n".'OR u.`handle` LIKE \'%'.$this->dao->escape($u).'%\'';
        }
        $query .= ')';
        $q = $this->dao->query($query);
        if (!$q->numRows())
            return false;

        $count = 0;
        while ($row = $q->fetch()) {
            if (in_array($row['handle'], $r) && $row['id']!=$userId) {
                $real[$row['id']] = $row['handle'];
            } else {
                $similar[] = $row['handle'];
            }
            if(++$count > $numsimilar)
                break;
        }
        return (count($real) == count($r));
    }
    
}
?>
