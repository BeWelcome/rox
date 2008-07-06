<?php ?>

<h3><?=$words->get('Actions')?></h3>

  <ul class="linklist">
    <li class="icon contactmember16">
      <a href="contactmember.php?cid=<?=$this->member->id?>"><?=$words->get('ContactMember');?></a>
    </li>
    <li class="icon addcomment16" >
      <a href="members/<?=$this->member->Username?>/comments/add" ><?=$words->get('addcomments');?></a>
    </li>
  </ul>
