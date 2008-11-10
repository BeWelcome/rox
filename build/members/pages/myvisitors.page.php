<?php


class MyVisitorsPage extends MemberPage
{
    protected function leftSidebar()
    {
    	$member = $this->member;
    	//print_r($this->member);
    	//$lang = $this->model->get_profile_language();
		//$lang = $this->member->get_profile_language();
		//$profile_language = $lang->id;
		//$profile_language_code = $lang->ShortCode;
		$words = $this->getWords();
    	
        ?>
          <h3><?=$words->get('Actions')?></h3>
          <ul class="linklist" >
            <li class="icon contactmember16" >
              <a href="bw/contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
            </li>
            <li class="icon addcomment16" >
              <a href="bw/addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></a>
            </li>
          </ul>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'visitors';
    }
    
    
    protected function column_col3()
    {
        $words = $this->getWords();
    	$member = $this->member;
    	$visitors = $member->visitors;
    	$members = $member->visitors;
    	//$visitors = $member->relations;
        
        // fage_value return a  the age value corresponding to date
        function fage_value($dd) {
            $pieces = explode("-",$dd);
            if(count($pieces) != 3) return 0;
            list($year,$month,$day) = $pieces;
            $year_diff = date("Y") - $year;
            $month_diff = date("m") - $month;
            $day_diff = date("d") - $day;
            if ($month_diff < 0) $year_diff--;
            elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
            return $year_diff;
        } // end of fage_value
        ?>
                    <div class="info clearfix"> 
                      <h3>Visitors for admin</h3>
        <?php
    foreach ($members as $member) {
        $image = new MOD_images_Image('',$member->Username);
        if ($member->HideBirthDate=="No") $member->age = floor(fage_value($member->BirthDate));
        else $member->age = $words->get("Hidden");
        echo '<a href="#"><li class="userpicbox float_left" style="cursor:pointer;" onclick="javascript: window.location.href = \'members/'.$member->Username.'\'; return false"><a href="bw/member.php?cid='.$member->Username.'">'.MOD_layoutbits::PIC_50_50($member->Username,'',$style='float_left framed').'</a><p><a href="bw/member.php?cid='.$member->Username.'">'.$member->Username.'</a>
        <a href="blog/'.$member->Username.'" title="Read blog by '.$member->Username.'"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/'.$member->Username.'" title="Show trips by '.$member->Username.'"><img src="images/icons/world.gif" alt="" /></a>
        <br /><span class="small">'.$words->getFormatted("yearsold",$member->age).'<br />'.$member->city.'</span></p></li></a>';
    }
    ?>
                    </div>
        <?php
    }
}


?>