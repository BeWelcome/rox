<?php


class AboutFaqsectionPage extends AboutFaqPage
{
    protected function leftSidebar()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        $categories = $this->faq_categories;
        echo '
        <ol class="faqs">';
        
        $j = 0;
        foreach ($categories as $key => $category) {
            if ($key == $this->key) {
                $add = 'selected';
            } else $add = '';
            echo '
            <li><div class="'.($j%2 ? 'odd' : 'even').'"><h3><a href="about/faq/'.$key.'" class="'.$add.'">
            '.$words->get($category->Description).'
            </a></h3>';
            echo '
            </div></li>
            <hr>';
            ++ $j;
        }
        
        echo '
        </ol>';
    }
    
    protected function teaserHeadline()
    {
        echo $this->ww->AboutFAQ;
    }
    
    protected function column_col3()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        echo '
        <h2>'.$words->get($this->faq_section->Description).'</h2>';
        echo '<div class="faqs">';
        echo '
        <ul>';
        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <li><a href="'.$req.'#question'.$faq_key.'">'.$faq->words_Q[0]->Sentence.'</a></li>';
        }
        echo '
        </ul>';
        echo '</div>';

        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <div>
            <a name="question'.$faq_key.'"></a>
            <h3>'.$faq->words_Q[0]->Sentence.' <a href="'.$req.'#question'.$faq_key.'">*</a></h3>
            <p class="faqs_text">'.$faq->words_A[0]->Sentence.'</p>
            </div>';
        }
    }
}


?>