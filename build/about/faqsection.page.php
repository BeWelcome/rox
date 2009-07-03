<?php


class AboutFaqsectionPage extends AboutFaqPage
{
    protected function leftSidebar()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        $categories = $this->faq_categories;
        echo '
        <ol class="faq">';
        
        $j = 0;
        foreach ($categories as $key => $category) {
            if ($key == $this->key) {
                $add = 'selected';
            } else $add = '';
            echo '
            <li class="'.($j%2 ? 'odd' : 'even').'"><a href="about/faq/'.$key.'" class="'.$add.'">
            '.$words->get($category->Description).'
            </a>';
            echo '
            </li>';
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
        if (isset($_SESSION["IdLanguage"])) {
			$IdLanguage=$_SESSION["IdLanguage"] ;
		}
		else {
			$IdLanguage=0 ;
		}
        echo '
        <h2>'.$words->get($this->faq_section->Description).'</h2>';
        echo '
        <ol class="bullet">';
        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <li><a href="'.$req.'#question'.$faq_key.'">'.$faq->words_Q[$IdLanguage]->Sentence.'</a></li>';
        }
        echo '
        </ol>';

        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <a name="question'.$faq_key.'"></a>
            <h3>'.$faq->words_Q[$IdLanguage]->Sentence.' <a href="'.$req.'#question'.$faq_key.'"></a></h3>
            <p class="faq_text">'.$faq->words_A[$IdLanguage]->Sentence.'</p>';
        }
    }
}


?>
