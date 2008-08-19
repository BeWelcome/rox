<h3><?=$words->get('Actions')?></h3>
<ul class="linklist" >
  <li class="icon contactmember16" >
    <A href="bw/contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
  </li>
  <li class="icon addcomment16" >
    <A href="bw/addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></a>
  </li>
  <li class="icon forumpost16" >
    <A href="forums/member/<?=$member->Username?>" ><?=$words->get('ViewForumPosts', 7);?></a>
  </li>
</ul>
<h3><?=$words->get('MyRelations');?></h3>
<ul class="linklist">
	<?php 
		$relations = $member->relations;
		foreach ($relations as $rel) {
	?>

  <li>
    <A href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>" >
      <IMG class="framed"  src="<?=PVars::getObj('env')->baseuri?>/photos/???"  height="50px"  width="50px"  alt="Profile" >
    </a>
    <br />
    <A href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
    <br />
  </li>
  <?php } ?>
</ul>
