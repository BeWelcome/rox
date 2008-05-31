<?php


class AboutFaqsectionPage extends AboutFaqPage
{
    protected function getTeaserHeadline()
    {
        return 'About - FAQ';
    }
    
    protected function column_col3()
    {
        $words = $this->getWords();
        
        echo '
        <h2>'.$words->get($this->faq_section->Description).'</h2>';
        
        foreach ($this->faq_section->faqs as &$faq) {
            echo '
            <h3>'.$faq->words_Q[0]->Sentence.'</h3>
            <div>'.$faq->words_A[0]->Sentence.'</div>';
        }
    }
}


?>