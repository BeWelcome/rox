<h3><?=$words->get('Actions')?></h3>
<ul class="linklist">
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
  <? } ?>
  <li class="icon forumpost16">
    <a href="forums/member/<?=$member->Username?>" ><?=$words->get('ViewForumPosts', 7);?></a>
  </li>

</ul>

