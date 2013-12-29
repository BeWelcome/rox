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
        echo '<h2>'.$words->get($this->faq_section->Description).'</h2>
        <dl id="faqs">';
        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <dt>'.$words->get("FaqQ_".$faq->QandA).'</dt>
            <dd id="question'.$faq_key.'">'.$words->get("FaqA_".$faq->QandA).'</dd>';
        }
?>
        </dl>
        <script type="text/javascript"><!--
    jQuery("#faqs dd").hide();
    jQuery("#faqs dt").click(function (e) {
        e.preventDefault();
        jQuery(this).next("#faqs dd").slideToggle(500);
        jQuery(this).toggleClass("expanded");

});
jQuery(function(){

  // Bind the event.
  jQuery(window).hashchange( function(){
    // Alerts every time the hash changes!
    hash = location.hash;
      jQuery( hash ).show();
      jQuery(document).scrollTop( jQuery( hash ).offset().top );
  })

  // Trigger the event (useful on page load).
  jQuery(window).hashchange();

});
        --></script>';
<?php
    }
}