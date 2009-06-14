<?php


class MyVisitorsPage extends ProfilePage
{   
    
    protected function getSubmenuActiveItem()
    {
        return 'profile';
    }
    
    
    protected function column_col3()
    {
        $words = $this->getWords();
    	$member = $this->member;
    	$visitors = $member->visitors_raw;
        $layoutbits = new MOD_layoutbits();
        // FIXME: Not the best way to provide pagination. But for now there's not better choice.
        if (!$visitors) {
            return false;
        } else {
            $request = PRequest::get()->request;
            $requestStr = implode('/', $request);
            $matches = array();
            if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
                $page = $matches[1];
                $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
            } else {
                $page = 1;
            }
            $p = PFunctions::paginate($visitors, $page, $itemsPerPage = 15);
            $visitors = $p[0];
        }

        ?>
        <h3><?=$words->get('')?></h3>
        <?php

    foreach ($visitors as $member) {
        $image = new MOD_images_Image('',$member->Username);
        if ($member->HideBirthDate=="No") $member->age = floor($layoutbits->fage_value($member->BirthDate));
        else $member->age = $words->get("Hidden");
        echo '<a href="#"><li class="userpicbox float_left" style="cursor:pointer;" onclick="javascript: window.location.href = \'members/'.$member->Username.'\'; return false"><a href="bw/member.php?cid='.$member->Username.'">'.MOD_layoutbits::PIC_50_50($member->Username,'',$style='float_left framed').'</a><p><a href="bw/member.php?cid='.$member->Username.'">'.$member->Username.'</a>
        <a href="blog/'.$member->Username.'" title="Read blog by '.$member->Username.'"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/'.$member->Username.'" title="Show trips by '.$member->Username.'"><img src="images/icons/world.gif" alt="" /></a>
        <br /><span class="small">'.$words->getFormatted("yearsold",$member->age).'<br />'.$member->city.'</span></p></li></a>';
    }

    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    if (isset($requestStrNew)) $requestStr = $requestStrNew;
    $request = $requestStr.'/=page%d';
    require TEMPLATE_DIR.'misc/pages.php';

    }
}


?>
