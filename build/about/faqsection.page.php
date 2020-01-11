<?php


class AboutFaqsectionPage extends AboutFaqPage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        $req = $_SERVER['REQUEST_URI'];
        $IdLanguage = $this->session->get( "IdLanguage", 0 );
        echo '<div class="row"><div class="col-12"><h2>'.$words->get($this->faq_section->Description).'</h2></div>
        <div class="col-12"><dl id="faqs">';
        foreach ($this->faq_section->faqs as $faq_key => &$faq) {
            echo '
            <dt class="h5">'.strip_tags($words->get("FaqQ_".$faq->QandA)).'</dt>
            <dd id="question'.$faq_key.'">'.$words->get("FaqA_".$faq->QandA).'</dd>';
        }
        ?>
        </dl></div>
        <script type="text/javascript"><!--
            var faqs = jQuery('#faqs');
            faqs.find("dd").hide();
            faqs.find("dt").click(function (e) {
                e.preventDefault();
                jQuery(this).next("#faqs dd").slideToggle(500);
                jQuery(this).toggleClass("expanded");

            });
            jQuery(function(){

                // Bind the event.
                jQuery(window).hashchange( function(){
                    // Alerts every time the hash changes!
                    hash = location.hash;
                    if( hash) {
                        jQuery(hash).show();
                        jQuery(document).scrollTop(jQuery(hash).offset().top);
                    }
                })

                // Trigger the event (useful on page load).
                jQuery(window).hashchange();

            });
            --></script>
        </div>
        <?php
    }

    protected function teaserHeadline()
    {
        echo $this->ww->AboutFAQ;
    }

    protected function column_col1()
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

}
