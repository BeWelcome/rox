<h3><?=$words->get('Actions')?></h3>
<ul class="linklist" >
  <li class="icon contactmember16" >
    <a href="bw/contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
  </li>
  <li class="icon addcomment16" >
    <a href="bw/addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></a>
  </li>
  <li class="icon forumpost16" >
    <a href="forums/members/<?=$member->Username?>" ><?=$words->get('ViewForumPosts', 7);?>View Forum Posts</a>
  </li>
</ul>
<h3><?=$words->get('MyRelations');?></h3>
<ul class="linklist" >
    <?php 
        $relations = $member->relations;
        foreach ($relations as $rel) {
    ?>

  <li>
    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>" >
      <img class="framed"  src="members/avatar/<?=$rel->Username?>?xs"  height="50px"  width="50px"  alt="Profile" >
    </a>
    <br />
    <a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
    <br />
  </li>
  <?php } ?>
</ul>
