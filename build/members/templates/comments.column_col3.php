<?php
if ($this->loggedInMember && !$this->myself) {
    $comment_byloggedinmember = $this->member->get_comments_commenter(
        $this->loggedInMember->id);
}

require_once 'comment_template.php';
?>