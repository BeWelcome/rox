<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$requestStr = implode('/', $request);
?>
      
      <h3>Meta</h3>
      <ul class="linklist">
		<li><a href="rss/<?=$requestStr?>" alt="Get the RSS-Feed of this page" class="float_right"><img src="images/icons/feed.png"></a> Entries RSS</li>
		<li><a href="rss/<?=$requestStr?>" alt="Get the RSS-Feed of this page" class="float_right"><img src="images/icons/feed.png"></a> Comments RSS</li>
      </ul>
