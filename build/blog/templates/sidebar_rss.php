<?php
$words = new MOD_words($this->getSession());
$request = PRequest::get()->request;
$requestStr = implode('/', $request);
?>
<h3><?=$words->get('Meta')?></h3>
<ul class="linklist">
    <li><a href="rss/<?=htmlspecialchars($requestStr, ENT_QUOTES)?>"><img src="images/icons/feed.png" alt="<?=$words->getSilent('GetRSSFeed')?>" /> <?=$words->getSilent('BlogEntriesRSS')?><?=$words->flushBuffer()?></a></li>
</ul>
