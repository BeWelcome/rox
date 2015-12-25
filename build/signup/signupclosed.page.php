<?php


class SignupClosedPage extends SignupBasePage
{
    
    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('SignupClosedTitle');
    }
    
    protected function column_col2()
    {
    }
    
    protected function column_col3()
    {
        $words = $this->layoutkit->words;
        echo '<p class="note big">'.$words->get('SignupClosedText').'</p>';
    }
}
