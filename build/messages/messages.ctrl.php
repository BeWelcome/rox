<?php

/**
 * Messages controller
 *
 * @package hellouniverse
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class MessagesController extends PAppController
{
    /**
     * decide which view page to show.
     * This method is called automatically
     */
    public function index()
    {
        $request = PRequest::get()->request;
        $model = new MessagesModel();
        // look at the request.
        if (!isset($request[1])) {
            // simple, ugly page
            $page = new MessagesInboxPage();
        } else switch ($request[1]) {
            case 'received':
            case 'inbox':
                $page = new MessagesInboxPage();
                break;
            case 'sent':
            case 'outbox':
                $page = new MessagesSentboxPage();
                break;
            case 'spam':
                $page = new MessagesSpamboxPage();
                break;
            case 'drafts':
                $page = new MessagesDraftsboxPage();
                break;
            case 'new':
                $page = new ComposeMessagePage();
                break;
            default:
                if (!is_numeric($request[1])) {
                    // the request does not mean a message id
                    $page = new MessagesInboxPage();
                } else if (!$message = $model->getMessage($request[1])) {
                    // no message with that id found
                    $page = new MessagesInboxPage();
                } else {
                    if (!isset($request[2])) {
                        $page = new ReadMessagePage();
                    } else switch ($request[2]) {
                        case 'edit':
                            $page = new EditMessagePage();
                            break;
                        case 'reply':
                            $page = new ReplyMessagePage();
                            break;
                        case 'read':
                        default:
                            $page = new ReadMessagePage();
                    }
                    $page->setMessage($message);
                }
                $page = new MessagesInboxPage();
                break;
        }
        
        // finally display the page.
        // the render() method will call other methods to render the page.
        $page->setModel($model);
        $page->render();
    }
}


?>