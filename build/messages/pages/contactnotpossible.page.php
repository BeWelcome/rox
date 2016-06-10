<?php

class ContactNotPossible extends MessagesBasePage
{
    function column_col3()
    {
        $words = new MOD_words($this->getSession());
        echo '<p class="note">' . $words->get('Message_ComposeNotPossible', $_SESSION["MemberStatus"]) . '</p>';
        // parent::column_col3();
    }
}

