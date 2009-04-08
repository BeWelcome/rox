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
  <li class="icon forumpost16">
    <a href="link/myself/<?=$member->Username?> "><?=$words->get("MyLinks");?></a>
  </li>
</ul>
<h3><a href="members/<?=$member->Username?>/relations/ "><?=$words->get('MyRelations');?></a></h3>
<ul class="linklist">
    <?php
        $relations = $member->relations;
        foreach ($relations as $rel) {
    ?>

  <li>
    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
      <img class="framed"  src="members/avatar/<?=$rel->Username?>?xs"  height="50px"  width="50px"  alt="Profile" >
    </a>
    <br />
    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
    <br />
    <?=$rel->Comment?>
  </li>
  <?php } ?>
</ul>
