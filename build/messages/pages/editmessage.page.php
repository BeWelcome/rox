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
            MOD_html2text::setHtml($message->Message);
            $message->Message = MOD_html2text::getMarkdown();
        } else {
            $purifier = new MOD_htmlpure();
            $purifier = $purifier->getAdvancedHtmlPurifier();
            $message->Message = $purifier->purify($message->Message);
            $message->Message = "<blockquote>" . $message->Message . "</blockquote><p></p>";
        }

        parent::column_col3();
    }
}

