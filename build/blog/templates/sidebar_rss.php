<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$requestStr = implode('/', $request);
?>
<h3><?=$words->get('Meta')?></h3>
<ul class="linklist">
    <li><a href="rss/<?=htmlspecialchars($requestStr, ENT_QUOTES)?>"><img src="images/icons/feed.png" alt="<?=$words->get('GetRSSFeed')?>" /> <?=$words->get('BlogEntriesRSS')?></a></li>
</ul>
