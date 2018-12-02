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

        $pager->render();

        echo '<div class="myvisitors card-columns">';

        foreach ($member->getVisitorsSubset($pager) as $m)
        {
            $image = MOD_layoutbits::PIC_50_50($m->Username,'',$style='float_left framed');
            if ($m->HideBirthDate=="No") $m->age = floor($layoutbits->fage_value($m->BirthDate));
            else $m->age = $words->get("Hidden");
            echo <<<HTML
<div class="card">
    <div class="card-header">
        {$image}
        <div class="userinfo">
            <a class="username" href="members/{$m->Username}">{$m->Username}</a><br />
        </div>
    </div>
    <div class="card-body">
            <p class="small">{$words->getFormatted("visited")}: {$layoutbits->ago(strtotime($m->visited))}</p>
            <p class="small">{$words->getFormatted("yearsold",$m->age)}, {$m->city}</p>
            <div class="profilesummary">{$purifier->purify(stripslashes($words->mInTrad($m->ProfileSummary, $language_id=0, true)))}</div>
    </div>
</div>
HTML;
        }
        echo "</div>";
        $pager->render();

    }
}
