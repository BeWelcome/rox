<?php


use Rox\Tools\RoxMigration;

class CreateCommunityNewsCommentTable extends RoxMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other distructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $communityNewsComment = $this->table('community_news_comment');
        $communityNewsComment
            ->addColumn('community_news_id', 'integer')
            ->addColumn('author_id', 'integer')
            ->addColumn('created', 'date')
            ->addColumn('title', 'string', [ 'length' => 75])
            ->addColumn('text', 'string', [ 'length' => 1000])
            ->addForeignKey('community_news_id', 'community_news')
            ->addForeignKey('author_id', 'members')
            ->create();
    }
}
