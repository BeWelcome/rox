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

        if (!$visitor_count)
        {
			echo $words->get("ProfileNoVisitors");
            return;
        }

        $params->strategy = new HalfPagePager('right');
        $params->items = $visitor_count;
        $params->items_per_page = 20;
        $pager = new PagerWidget($params);

        $pager->render();

        echo "<div style='clear:right'>";

        foreach ($member->getVisitorsSubset($pager) as $m)
        {
            $image = new MOD_images_Image('',$m->Username);
            $image = MOD_layoutbits::PIC_50_50($m->Username,'',$style='float_left framed');
            if ($m->HideBirthDate=="No") $m->age = floor($layoutbits->fage_value($m->BirthDate));
            else $m->age = $words->get("Hidden");
            echo <<<HTML
<li class="userpicbox float_left">
    <a href="members/{$m->Username}">{$image}</a>
    <p>
        <a href="members/{$m->Username}">{$m->Username}</a>
        <a href="blog/{$m->Username}" title="Read blog by {$m->Username}"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/{$m->Username}" title="Show trips by {$m->Username}"><img src="images/icons/world.gif" alt="" /></a>
        <br />
        <span class="small">{$words->getFormatted("yearsold",$m->age)}<br />{$m->city}</span>
    </p>
</li>
HTML;
        }
        echo "</div>";
    }
}
