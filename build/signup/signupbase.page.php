<?php


class SignupBasePage extends PageWithRoxLayout
{
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->getBuffered('signup') . ' - BeWelcome';
    }
    
    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('signup');
    }

    protected function getColumnNames()
    {
        // we don't need the other columns
        return array('col3');
    }

    protected function getLateLoadScriptfiles()
    {
        $scripts = parent::getLateLoadScriptfiles();
        $scripts[] = 'build/signup/signup.js';
        return $scripts;
    }

    private function _cmpEditLang($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
    }
}
