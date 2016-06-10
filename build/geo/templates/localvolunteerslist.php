<?php

$User = new APP_User;
$words = new MOD_words($this->getSession());

// This was quicly duplicated from the memberslist.php template, this imply om redudancies

if (!$volunteers) {
    return $text['no_volunteers_yet'];
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
    $p = PFunctions::paginate($volunteers, $page, $itemsPerPage = 15);
    $volunteers = $p[0];
?>
<ul class="clearfix">
<?php
    foreach ($volunteers as $member) {
        $image = new MOD_images_Image('',$member->username);
        echo '<a href="#"><li class="userpicbox float_left" style="cursor:pointer;" onclick="javascript: window.location.href = \'bw/member.php?cid='.$member->username.'\'; return false"><a href="bw/member.php?cid='.$member->username.'">'.MOD_layoutbits::PIC_50_50($member->username,'',$style='float_left framed').'</a><p><a href="bw/member.php?cid='.$member->username.'">'.$member->username.'</a>' ;
				 
        echo '<br /><span class="small">'.$member->city.'</span>' ;
				echo $words->mTrad($member->VolComment,true) ;
				echo "</p></li></a>";
    }
    ?>
    </ul>
<?php    
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    require 'pages.php';
}

?>
