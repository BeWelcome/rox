<?php
        $member = $this->member;
        $comments = $this->member->comments;
        $comments_written = $this->member->get_comments_written();
        
        $username = $this->member->Username;
        $layoutbits = new MOD_layoutbits();
?>
