<?php


class MyVisitorsPage extends ProfilePage
{   
    
    protected function getSubmenuActiveItem()
    {
        return 'visitors';
    }
    
    
    protected function column_col3()
    {
        $words = $this->getWords();
    	$member = $this->member;
    	$visitor_count = $this->member->getVisitorCount() ;
        $layoutbits = new MOD_layoutbits();
        $purifier = MOD_htmlpure::getBasicHtmlPurifier();

        if (!$visitor_count)
        {
			echo $words->get("ProfileNoVisitors");
            return;
        }

        $params = new StdClass();
        $params->strategy = new HalfPagePager('right');
        $params->items = $visitor_count;
        $params->items_per_page = 20;
        $pager = new PagerWidget($params);

        echo '<div class="row no-gutters">';
        echo '<div class="col-12 col-sm-6 offset-sm-6">';
        $pager->render();
        echo '</div>';
        echo '</div>';

        echo '<div class="myvisitors card-columns">';

        foreach ($member->getVisitorsSubset($pager) as $m)
        {
            $image = MOD_layoutbits::PIC_50_50($m->Username,'',$style='float_left framed');
            $aboutMe = MOD_layoutbits::truncate_words(stripslashes($words->mInTrad($m->ProfileSummary, $language_id=0, true)), 70);

            if ($m->HideBirthDate=="No") $m->age = floor($layoutbits->fage_value($m->BirthDate));
            else $m->age = $words->get("Hidden");
            echo <<<HTML
<div class="card">
    <div class="card-body p-2">
        <div class="card-title">
            <div class="float-left">{$image}</div>
            <div class="float-left">
                <a class="username" href="members/{$m->Username}">{$m->Username}</a><br>
                <small>{$words->getFormatted("yearsold",$m->age)}, {$m->city}</small>
             </div>
             <div class="clearfix"></div>
        </div>
        {$purifier->purify($aboutMe)}
    </div>
    <div class="card-footer text-right p-2">
        <small>{$words->getFormatted("visited")}: {$layoutbits->ago(strtotime($m->visited))}</small>
    </div>
</div>
HTML;
        }
        echo "</div>";

        echo '<div class="row no-gutters">';
        echo '<div class="col-12 col-sm-6 offset-sm-6">';
        $pager->render();
        echo '</div>';
        echo '</div>';

    }
}
