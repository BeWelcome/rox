<?php

$words = new MOD_words($this->getSession());
?>
      <h3><?=$words->get('Actions')?></h3>
      <ul class="linklist">
		<li><img src="images/icons/blog.gif" alt="" /> <a href="blog/<?=$_SESSION['Username']?>"><?=$words->getFormatted('Your blog posts')?></a></li>
		<li><img src="images/icons/page_white_star.png" alt="" /> <a href="blog/create"><?=$words->get('Blog_CreateEntry')?></a></li>
		<li><img src="images/icons/page_white_stack.png" alt="" /> <a href="blog/cat"><?=$words->get('Blog_ManageCats')?></a></li>
      </ul>
      

