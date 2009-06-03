<?php
$member = $this->member;
$lang = $this->model->get_profile_language();
$profile_language = $lang->id;
$profile_language_code = $lang->ShortCode;        
$words = $this->getWords();
$ww = $this->ww;
$wwsilent = $this->wwsilent;
$comments_count = $member->count_comments(); 

$layoutbits = new MOD_layoutbits;
$right = new MOD_right();

$agestr = "";
if ($member->age == "hidden") {
    $agestr .= $ww->AgeHidden;
} else {
    $agestr= $ww->AgeEqualX($layoutbits->fage_value($member->BirthDate));
}
$languages = $member->get_profile_languages(); 
$occupation = $member->get_trad("Occupation", $profile_language);
?>


<!--
        <img src="images/icons/<?=($member->Accomodation) ? $member->Accomodation : 'neverask'?>.gif"  class="float_left"  title="<?=$member->Accomodation?>"  width="30"  height="30"  alt="<?=$member->Accomodation?>" />
        <?php
        // specific icon according to members.TypicOffer
        if (strstr($member->TypicOffer, "guidedtour"))
        {
            $title = $words->getSilent("TypicOffer_guidedtour");
			$image_name = 'icon_castle.gif';
			echo "<img src='images/icons/{$image_name}' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />";
        }
        if (strstr($member->TypicOffer, "dinner"))
        {
            $title = $words->getSilent("TypicOffer_dinner");
			$image_name = 'icon_food.gif';
			echo "<img src='images/icons/{$image_name}' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />";
        }
        if (strstr($member->TypicOffer, "CanHostWeelChair"))
        {
            $title = $words->getSilent("TypicOffer_CanHostWeelChair");
			$image_name = 'wheelchair.gif';
			echo "<img src='images/icons/{$image_name}' class='float_left' title='{$title}' width='30' height='30' alt='icon_castle' />";
        }
		echo $words->flushBuffer();
        ?>
    -->
<ul class="linklist" id="profile_linklist">
  <? if (!$myself) { ?>
  <li class="icon contactmember16">
    <a href="messages/compose/<?=$member->Username?>" ><?=$words->get('ContactMember');?></a>
  </li>
  <li class="icon addcomment16">
    <a href="members/<?=$member->Username?>/comments/add" ><?=$words->get('addcomments');?></a>
  </li>
  <li class="icon forumpost16">
    <a href="members/<?=$member->Username?>/relations/add "><?=$words->get("addRelation");?></a>
  </li>
  <? } else { ?>
      <li class="icon contactmember16">
        <a href="myvisitors" ><?=$words->get('MyVisitors');?></a>
      </li>
  <? } ?>

</ul>


