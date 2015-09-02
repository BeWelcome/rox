<?php

use Phinx\Migration\AbstractMigration;

class AddCommentWordCode extends Rox\Tools\RoxMigration
{
    public function up()
    {
        $this->AddWordCode('CommentSomethingWentWrong','Sorry, something went wrong with your comment post. Please try again. If the problem persists please contact the support team.', 'Used on the comment page if someone tries to spam.');
    }

    public function down()
    {
        $this->RemoveWordCode('CommentSomethingWentWrong');
    }
}
