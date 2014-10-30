<?php

class EditMessagePage extends ComposeMessagePage
{
    public $edit = true;

    protected function column_col3()
    {
        $message = $this->message;
        // Sender becomes Receiver
        $disabledTinyMce = $this->sender->getPreference("PreferenceDisableTinyMCE", $default = "No") == 'Yes';
        if ($disabledTinyMce) {
            $html2text = new Html2Text\Html2Text($message->Message, false, array('dolinks' => 'inline',
                'width' => 75));
            $message->Message = $html2text->get_text();
        } else {
            $purifier = new MOD_htmlpure();
            $purifier = $purifier->getMessagesHtmlPurifier();
            $message->Message = $purifier->purify($message->Message);
        }

        parent::column_col3();
    }
}

