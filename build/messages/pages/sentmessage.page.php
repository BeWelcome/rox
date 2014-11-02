<?php

class MessageSentPage extends ReadMessagePage
{
    protected function column_col3()
    {
        $words = new MOD_words();
        echo '<p class="note">' . $words->get('Message_hasbeensent') . '</p>';
        parent::column_col3();
    }

    protected function getSubmenuActiveItem()
    {
        return 'sent';
    }
}
