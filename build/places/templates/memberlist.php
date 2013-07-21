<?php

$User = new APP_User;
$words = new MOD_words();
$layoutbits = new MOD_layoutbits;

if (!$members) {
    return $text['no_members'];
} else {
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = array();

    // determine on what page we are
    if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    
    // divide members into pages of 15
    define('ITEMSPERPAGE',15);
    $p = PFunctions::paginate($members, $page, $itemsPerPage = ITEMSPERPAGE);
    $members = $p[0];
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;

    // show members if there are any to show
    if (count($members)>0){
        echo '<ul class="floatbox">';
        foreach ($members as $member) {
            $image = new MOD_images_Image('',$member->username);
            if ($member->HideBirthDate=="No") {
                $member->age = floor($layoutbits->fage_value($member->BirthDate));
            } else {
                $member->age = $words->get("Hidden");
            }
            echo '<li class="userpicbox float_left">';
            echo MOD_layoutbits::PIC_50_50($member->username,'',$style='framed float_left');
            echo '<div class="userinfo">';
            echo '  <a class="username" href="members/'.$member->username.'">'.
                    $member->username.'</a><br />';
            echo '  <span class="small">'.$words->get("yearsold",$member->age).
                    '<br />'.$member->city.'</span>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
    }

    // display hint to login when that would show more members
    if (!APP_User::isBWLoggedIn('NeedMore,Pending') AND $currentPage == $maxPage
            OR !APP_User::isBWLoggedIn('NeedMore,Pending') AND !$members) {
        // prepare login link with redirect to current page
        $login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);
        $loginstr = '<a href="'.$login_url.
            '#login-widget" alt="login" id="header-login-link">'.
            $words->getBuffered('GroupsMoreMemberLogin') . '</a>';
        // count the number of members that can be seen as the number on the last page
        // plus 15 for each page before the last one.
        $visibleMemberCount = count($members) + ITEMSPERPAGE*max($maxPage-1,0);
        // display actual hint if some members are hidden
        if ($placeinfo->memberCount > $visibleMemberCount){
            echo $words->get("GroupMoreMembers",
                             $placeinfo->memberCount - $visibleMemberCount,
                             $loginstr);
            // if there are no visible members, there is no pagination and we
            // need an extra line to separate from the wiki
            if ($visibleMemberCount==0){echo '<p/>';}
        } 
    }
    $request = $requestStr.'/=page%d';
    require 'pages.php';
}

?>
