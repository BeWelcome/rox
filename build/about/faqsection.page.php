<?php


class AboutFaqsectionPage extends AboutFaqPage
{
    protected function leftSidebar()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        echo '
        <ol>';
        
        $j = 0;
        foreach ($this->faq_categories as $key => $category) {
            echo '
            <li><div class="'.($j%2 ? 'odd' : 'even').'"><strong><a href="about/faq/'.$key.'">
            '.$words->get($category->Description).'
            </a></strong>';
            
            if ($key == $this->key) {
                echo '
                <p><ul>';
                foreach ($this->faq_section->faqs as $faq_key => &$faq) {
                    echo '
                    <li><a href="'.$req.'#question'.$faq_key.'">'.substr($faq->words_Q[0]->Sentence, 0, 16).' ...</a></li>';
                }
                echo '
                </ul></p>';
            } else {
                echo '-';
            }
            
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
        echo 'About - FAQ';
    }
    
    protected function column_col3()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        echo '
        <h2>'.$words->get($this->faq_section->Description).'</h2>';
        
        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <div>
            <a name="question'.$faq_key.'"></a>
            <h3>'.$faq->words_Q[0]->Sentence.' <a href="'.$req.'#question'.$faq_key.'">*</a></h3>
            <div>'.$faq->words_A[0]->Sentence.'</div>
            </div>
            <br>';
        }
    }
}


?>