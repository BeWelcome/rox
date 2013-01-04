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
    if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    $p = PFunctions::paginate($members, $page, $itemsPerPage = 15);
    $members = $p[0];
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
?>
<ul class="floatbox">
<?php
    foreach ($members as $member) 
    {
        $image = new MOD_images_Image('',$member->username);
        if ($member->HideBirthDate=="No") $member->age = floor($layoutbits->fage_value($member->BirthDate));
        else $member->age = $words->get("Hidden");
        echo '<li class="userpicbox float_left">';
        echo MOD_layoutbits::PIC_50_50($member->username,'',$style='framed float_left');
        echo '<div class="userinfo">';
        echo '  <a class="username" href="members/'.$member->username.'">'.$member->username.'</a><br />';
        echo '  <span class="small">'.$words->get("yearsold",$member->age).'<br />'.$member->city.'</span>';
        echo '</div>';
        echo '</li>';
        }
    if (!APP_User::isBWLoggedIn('NeedMore,Pending') AND $currentPage == $maxPage OR !APP_User::isBWLoggedIn('NeedMore,Pending') AND !$members) 
        {
        $request = PRequest::get()->request;
        $login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);
        echo '<li class="userpicbox float_left">';
        echo '<a href="' . $login_url .'">';
        echo '<img width="50" height="50" alt="Profile" src="images/misc/empty_avatar_xs.png" class="framed float_left">';
        echo '</a>';
        echo '<div class="userinfo">';
        if (!APP_User::isBWLoggedIn('NeedMore,Pending') AND !$members){ //not logged in and all profiles non public
            echo $words->get('PlacesLoginToSeeOurMembers', '<a class="username" href="' . $login_url .'">' , '</a><br /><span class="small">' , '<br />' , '</span>');
        } else { //not logged in and some public profiles
            echo $words->get('PlacesLoginToSeeMore', '<a class="username" href="' . $login_url .'">' , '</a><br /><span class="small">' , '<br />' , '</span>');
        }     
        echo '</div>';
        echo '</li>';
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
