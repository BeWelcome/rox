<?php

class ReplyMessagePage extends ComposeMessagePage
{
    protected function column_col3()
    {
        $message = $this->message;
        // Sender becomes Receiver
        $message->receiverUsername = $message->senderUsername;
        $message->senderUsername = $_SESSION['Username'];
        $disabledTinyMce = $this->sender->getPreference("PreferenceDisableTinyMCE", $default = "No") == 'Yes';
        if ($disabledTinyMce) {
            $html2text = new Html2Text\Html2Text($message->Message, false, array('do_links' => 'inline',
                'width' => 75));
            $message->Message = $html2text->get_text();
            $message->Message = "\n\n>" . str_replace("\n", "\n>", $message->Message);
        } else {
            $purifier = new MOD_htmlpure();
            $purifier = $purifier->getAdvancedHtmlPurifier();
            $message->Message = $purifier->purify($message->Message);
            $message->Message = "<blockquote>" . $message->Message . "</blockquote><p></p>";
        }
        $contact_username = $message->senderUsername;
        $direction_in = true;
        if ($contact_username == $_SESSION['Username']) {
            $contact_username = $message->receiverUsername;
            $direction_in = false;
        }

        parent::column_col3();
    }
}
