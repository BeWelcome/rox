<?php
$comment_byloggedinmember =
    $this->member->get_comments_commenter($this->loggedInMember->id);

if ($this->loggedInMember && !$this->myself) {
    if ($comment_byloggedinmember && $comment_byloggedinmember[0]) {
        echo '  <p><a href="members/' . $username
            . '/comments/add" class="button">' . $words->get('editcomments')
            . '</a></p>' . "\n";
    } else {
        echo '  <p><a href="members/' . $username
            . '/comments/add" class="button">' . $words->get('addcomments')
            . '</a></p>' . "\n";
    }
}
require_once 'comment_template.php';
?>