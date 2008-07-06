<h3><?=$words->get('Actions')?></h3>
<ul class="linklist" >
  <li class="icon contactmember16" >
    <A href="contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
  </li>
  <li class="icon addcomment16" >
    <A href="addcomments.php?cid=<?=$member->id?>" ><?=$words->get('addcomments');?></a>
  </li>
  <li class="icon forumpost16" >
    <A href="http://localhost/bw-trunk-new/htdocs/forums/member/<?=$member->Username?>" ><?=$words->get('ViewForumPosts', 7);?>View Forum Posts</a>
  </li>
</ul>
<h3><?=$words->get('MyRelations');?></H3>
<ul class="linklist" >
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
  <!--<li>
    <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri"  title="See profile henri" >
      <IMG class="framed"  src="http://localhost/bw-trunk-new/htdocs/bw/"  height="50px"  width="50px"  alt="Profile" >
    </a>
    <br />
    <A href="http://localhost/bw-trunk-new/htdocs/bw/member.php?cid=henri" >henri</a>
    <br />
  </li>-->
</ul>
