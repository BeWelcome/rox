<?php
if ($this->loggedInMember && !$this->myself) {
    $comment_byloggedinmember = $this->member->get_comments_commenter(
        $this->loggedInMember->id);

    if(count($comment_byloggedinmember) == 0) {
      // Show "Add comment" button
      echo '  <p><a href="members/' . $username
          . '/comments/add" class="button">' . $words->get('addcomments')
          . '</a></p>' . "\n";
    }
}

require_once 'comment_template.php';
?>