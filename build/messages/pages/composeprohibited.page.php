<?php


/**
 * Page for writing a new message
 *
 * @package messages
 * @author Andreas (lemon-head)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */
class ComposeMessageProhibitedPage extends MessagesBasePage
{
    /**
     * content of the middle column - this is the most important part
     */
    protected function column_col3()
    {
        require_once 'templates/composeprohibited.php';
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
