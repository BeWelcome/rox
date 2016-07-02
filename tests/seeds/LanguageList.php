<?php

use Rox\Tools\RoxSeed;

class LanguageList extends RoxSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        // Make sure the data is written correctly...
        $this->execute("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");

        $data = [
            [
                'id' => 1,
                'EnglishName' => 'English',
                'Name' => 'English',
                'ShortCode' => 'en',
                'WordCode' => 'Lang_en',
                'FlagSortCriteria' => '-1017',
                'IsWrittenLanguage' => '1',
                'IsSpokenLanguage' => '1',
                'IsSignLanguage' => '0',
            ],
            [
                'id' => 2,
                'EnglishName' => 'French',
                'Name' => 'fran&ccedil;ais',
                'ShortCode' => 'fr',
                'WordCode' =>  'Lang_fr',
                'FlagSortCriteria' => '-492',
                'IsWrittenLanguage' => '1',
                'IsSpokenLanguage' => '1',
                'IsSignLanguage' => '0',
            ],
        ];

        $languages = $this->table('languages');
        $languages->insert($data)
            ->save();

        $data = [
            [
                'IdLanguage' => 1,
                'code' => 'WelcomeToSignup',
                'ShortCode' => 'en',
                'Sentence' => 'Welcome to the sign-up page',
                ],
            [
                'IdLanguage' => 2,
                'code' => 'WelcomeToSignup',
                'ShortCode' => 'fr',
                'Sentence' => 'Bienvenue sur la page d’inscription',
            ],
        ];

        $words = $this->table('words');
        $words->insert($data)
            ->save();
    }
}
