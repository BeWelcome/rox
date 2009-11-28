<?php


class SignupPrivacyPopup extends PageWithHTML
{
    protected function body()
    {
        $words = $this->layoutkit->words;
        echo $words->get('PrivacyStatement');
    }
}
