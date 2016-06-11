<?php

class ContactNotPossible extends MessagesBasePage
{
    function column_col3()
    {
        $words = new MOD_words();
        echo '<p class="note">' . $words->get('Message_ComposeNotPossible', $this->_session->get("MemberStatus")) . '</p>';
        // parent::column_col3();
    }
}

