<?php

class ReplyMessagePage extends ComposeMessagePage
{
    protected function column_col3()
    {
        $message = $this->message;
        // Sender becomes Receiver
        $message->receiverUsername = $message->senderUsername;
        $message->senderUsername = $this->_session->get('Username');
        $disabledTinyMce = $this->sender->getPreference("PreferenceDisableTinyMCE", $default = "No") == 'Yes';
        if ($disabledTinyMce) {
            $html2text = new Html2Text\Html2Text($message->Message, false, array('do_links' => 'inline',
                'width' => 75));
            $message->Message = $html2text->get_text();
            $message->Message = "\n\n>" . str_replace("\n", "\n>", $message->Message);
        } else {
            $purifier = new MOD_htmlpure();
            $purifier = $purifier->getMessagesHtmlPurifier();
            $message->Message = $purifier->purify($message->Message);
            $message->Message = "<p></p><blockquote>" . $message->Message . "</blockquote><p></p>";
        }
        $contact_username = $message->senderUsername;
        $direction_in = true;
        if ($contact_username == $this->_session->get('Username')) {
            $contact_username = $message->receiverUsername;
            $direction_in = false;
        }

        parent::column_col3();
    }
}
