<?php


class AboutFaqPage extends AboutBasePage
{
    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/faq.css?1';
        $stylesheets[] = 'styles/css/minimal/screen/custom/font-awesome.min.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/font-awesome-ie7.min.css';
        return $stylesheets;
    }
    
    protected function leftSidebar()
    {
        $words = $this->getWords();
        
        $j = 0;
        foreach ($this->faq_categories as $key => $category) {
            echo '
            <div class="'.($j%2 ? 'odd' : 'even').'"><a href="about/faq/'.$key.'">
            '.$words->get($category->Description).'
            </a></div>
            <hr />';
            ++ $j;
        }
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'faq';
    }
    
    protected function column_col3()
    {
        $words = $this->getWords();
        
        /*
        echo '<h3>'.$words->get('FaqQ_'.$this->faq_key).'</h3>';
        echo '<div>'.$words->get('FaqA_'.$this->faq_key).'</div>';
        */
        
        $categories = $this->faq_categories;
        
        foreach ($categories as $key => $category) {
            echo '
            <h3><a href="about/faq/'.$key.'">'.$words->get($category->Description).'</a></h3>';
            foreach ($category->faqs as &$faq) {
                echo '
                <p>'.$faq->words_Q[0]->Sentence.'</p>';
            }
        }
        
        // echo '<pre>'; print_r($this->model->getFaqsCategorized()); echo '</pre>';
    }
}

