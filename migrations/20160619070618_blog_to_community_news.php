<?php

use Rox\Tools\RoxMigration;

class BlogToCommunityNews extends RoxMigration
{
    public function up()
    {
        $this->table('community_news')
            ->addColumn('title', 'string',
                [
                    'comment' => 'Stores the title of the community news',
                    'limit' => 255
                ])
            ->addColumn('text', 'text',
                [
                    'comment' => 'Stores the text of the community news'
                ])
            ->addColumn('public', 'boolean',
                [
                    'comment' => 'Controls if the community news is shown to all users yet',
                    'default' => 0
                ])
            ->addColumn('created_at', 'datetime',
                [
                    'null' => true,
                    'comment' => 'Eloquent standard column',
                    'default' => 'CURRENT_TIMESTAMP'
                ])
            ->addColumn('created_by', 'biginteger',
                [
                    'comment' => 'Stores the member who created the news'
                ])
            ->addColumn('updated_at', 'datetime',
                [
                    'null' => true,
                    'comment' => 'Eloquent standard column',
                    'default' => 'CURRENT_TIMESTAMP'
                ])
            ->addColumn('updated_by', 'biginteger',
                [
                    'null' => true,
                    'comment' => 'Stores the member who updated the news'
                ])
            ->addColumn('deleted_at', 'datetime',
                [
                    'null' => true,
                    'default' => null,
                    'comment' => 'Eloquent standard column',
                ])
            ->addColumn('deleted_by', 'biginteger',
                [
                    'null' => true,
                    'default' => null,
                    'comment' => 'Stores the member who deleted the news'
                ])
            ->create();
        $communityTable = $this->table('community_news');
        $statement = $this->query("
        SELECT 
            b.blog_id,
            b.IdMember,
            bd.blog_title,
            bd.blog_text,
            blog_created
        FROM   
            blog AS b
        JOIN 
            blog_data AS bd ON b.blog_id = bd.blog_id
        LEFT JOIN 
            `blog_to_tag` b2t ON b.`blog_id` = b2t.`blog_id_foreign`
        LEFT JOIN 
            `blog_tags` bt ON b2t.`blog_tag_id_foreign` = bt.`blog_tag_id`
        WHERE bt.`name` LIKE 'Community News for the frontpage'");

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', './data');
        $config->set('HTML.Allowed', 'p,b,a[href|target],br,i,strong,em,ol,ul,li,dl,dt,dd,blockquote');
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.Linkify', true); // automatically turn stuff like http://domain.com into links

        $htmlPurifier = new HTMLPurifier($config);

        $communityNews = $statement->fetchAll(PDO::FETCH_OBJ);
        foreach($communityNews as $news) {
            $communityTable->insert(
                [
                    'title' => $news->blog_title,
                    'text' => $htmlPurifier->purify($news->blog_text),
                    'created_at' => $news->blog_created,
                    'created_by' => $news->IdMember,
                    'updated_at' => $news->blog_created,
                    'updated_by' => $news->IdMember,
                    'public' => 1
                ]
            );
        }
        $communityTable->saveData();
    }

    public function down()
    {
        $this->dropTable('community_news');
    }
}
