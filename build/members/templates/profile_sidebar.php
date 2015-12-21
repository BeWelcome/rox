<h3><?=$words->get('Actions')?></h3>
<ul class="list-group" >
  <li class="list-item icon contactmember16" >
    <a class="list-item" href="bw/contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
  </li>
  <li class="list-item icon addcomment16" >
    <a class="list-item" href="bw/addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></a>
  </li>
  <li class="list-item icon forumpost16" >
    <a class="list-item" href="forums/members/<?=$member->Username?>" ><?=$words->get('ViewForumPosts', 7);?>View Forum Posts</a>
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
