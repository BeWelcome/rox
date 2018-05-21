<?php


use Rox\Tools\RoxMigration;

class ShortenKeysInBlogDataAndWords extends RoxMigration
{
    /**
     * Shorten keys in blog_data and words to ensure that the migration to utf8mb4 works fine.
     */
    public function up()
    {
        $this->execute("ALTER TABLE `blog_data` CHANGE COLUMN `blog_title` `blog_title` VARCHAR(100) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE `words` CHANGE COLUMN `code` `code` VARCHAR(100) NOT NULL COMMENT 'Key code used in php programs to retrieve the matching translation'");
    }

    public function down()
    {
        // Leave keys shorten (no impact)
    }
}
