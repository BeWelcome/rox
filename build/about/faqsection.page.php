<?php


class AboutFaqsectionPage extends AboutFaqPage
{
    protected function leftSidebar()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        $categories = $this->faq_categories;
        echo '
        <div class="list-group">';
        
        $j = 0;
        foreach ($categories as $key => $category) {
            if ($key == $this->key) {
                $add = 'active';
            } else $add = '';
            echo '
            <a href="about/faq/'.$key.'" class="list-group-item '.$add.'">
            '.$words->get($category->Description).'
            </a>';
            ++ $j;
        }
        
        echo '
        </div>';
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
        echo '<h2>'.$words->get($this->faq_section->Description).'</h2>';

        foreach ($this->faq_section->faqs as $faq_key => &$faq) {

            echo '
            <p class="h5 p-l-1">
                <i class="fa fa-plus-circle"></i> <a class="question" data-toggle="collapse" href="#question' . $faq_key . '" aria-expanded="false" aria-controls="question' . $faq_key . '">';
                echo $words->get("FaqQ_".$faq->QandA) . '</a></p>';
            echo '
            <div class="collapse" id="question' . $faq_key . '">
                <div class="card card-block">';
                    echo $words->get("FaqA_".$faq->QandA) . '</div></div>';
        }
?>

<?php
    }
}