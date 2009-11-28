<?php


class SignupTermsPopup extends PageWithHTML
{
    protected function body()
    {
        $words = $this->layoutkit->words;
        echo $words->get('SignupTerms');
    }
}
