<?php


use Rox\Tools\RoxMigration;

class CommentRenameLenghtToRelation extends RoxMigration
{
    public function change()
    {
        $comments = $this->table('comments');
        $comments
            ->renameColumn('Lenght', 'Relations')
            ->save()
        ;
    }
}
