<?php


use Rox\Tools\RoxMigration;

class AddTranslationWordCodes extends RoxMigration
{
    public function up()
    {
        $this->AddWordCode('translations.headline','Translations', 'Headline for the translation pages.');
        $this->AddWordCode('translations.abstract','This shows all translations for the locale \'%locale%\'.', 'Short introduction to the translation pages.');
        $this->AddWordCode('translation.wordcode','Wordcode', 'Label on translation forms.');
        $this->AddWordCode('translation.locale','Locale', 'Label on translation forms.');
        $this->AddWordCode('translation.description','Description', 'Label on translation forms.');
    }

    public function down()
    {
        $this->RemoveWordCode('translations.headline');
        $this->RemoveWordCode('translations.abstract');
        $this->RemoveWordCode('translation.wordcode');
        $this->RemoveWordCode('translation.locale');
        $this->RemoveWordCode('translation.description');
    }
}
