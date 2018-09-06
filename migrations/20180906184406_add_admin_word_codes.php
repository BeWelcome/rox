<?php


use Rox\Tools\RoxMigration;

class AddAdminWordCodes extends RoxMigration
{

    public function up()
    {
        $this->addWordcode('AdminComment', 'Comments', 'Headline in admin comments');
        $this->addWordcode('translations.title', 'Translations', 'Title of the translations page');
        $this->addWordcode('translations.headline', 'Translations', 'Headline for the translations page');
        $this->addWordcode('translations.abstract', 'This shows a list of all translations.', 'Abstract above the list of translations.');
        $this->addWordcode('admin.comments.reported', 'Reported Comments', 'Headline for the list of reported comments (possibly spam).');
        $this->addWordcode('admin.spam.reported', 'Reported Messages', 'Headline for the list of reported messages (possibly spam).');
    }

    public function down()
    {
        // remove all wordcodes
        $this->removeWordcode('AdminComment');
        $this->removeWordcode('translations.title');
        $this->removeWordcode('translations.headline');
        $this->removeWordcode('translations.abstract');
        $this->removeWordcode('admin.comments.reported');
        $this->removeWordcode('admin.spam.reported');
    }

}
