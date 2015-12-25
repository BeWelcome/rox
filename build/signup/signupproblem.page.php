<?php


class SignupProblemPage extends SignupBasePage
{
    
    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('SignupProblemTitle');
    }
    
    protected function column_col2()
    {
    }
    
    protected function column_col3()
    {
        $words = $this->layoutkit->words;
        echo '<p class="big error">'.$words->get('SignupProblemText','<a href="feedback">','</a>').'</p>';
    }
}
