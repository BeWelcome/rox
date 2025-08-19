<?php


class SignupBasePage extends PageWithRoxLayout
{
    public function __construct()
    {
        parent::__construct();
//        $this->addLateLoadScriptFile('build/rangeslider.js');
//        $this->addLateLoadScriptFile('build/signup/signup.js');
    }

    #[\Override]
    protected function getSubmenuItems()
    {
        return '';
    }

    #[\Override]
    protected function getPageTitle() {
        $words = $this->getWords();
        return $words->getBuffered('signup') . ' - BeWelcome';
    }

    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('signup');
    }

    #[\Override]
    protected function getColumnNames()
    {
        // we don't need the other columns
        return ['col3'];
    }

    private function _cmpEditLang($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return (strtolower((string) $a->TranslatedName) < strToLower((string) $b->TranslatedName)) ? -1 : 1;
    }
}
