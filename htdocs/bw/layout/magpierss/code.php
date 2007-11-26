<?php

    require_once ("rss_fetch.inc");
    $url = 'http://blogs.bevolunteer.org/feed';
    
    $num_items = 10;
    $rss = fetch_rss($url);

    $items = array_slice($rss->items, 0, $num_items);
    
    echo "Site: ", $rss->channel['title'], "<br>
    ";
    echo "<ul>\n";
    foreach ($items as $item ) {
    	$title = $item['title'];
    	$url   = $item['link'];
    	$description   = $item['description'];   
    /*	$subject = $item ['dc'] ['subject']; */
    	/*$startdate   = $item['date'];
    	$type   = $item['type'];   
    	$author   = $item['author'];     */     
    	echo "<li><h2><a href=\"",$url,"\">",$title,"</a></h2>
        <p>",$description,"</p>
        </li>
    ";
    } 
        echo "</ul>\n";
        echo "<a href=\"http://blogs.bevolunteer.org\">",$words->get(moreEntriesandComments),"</a>\n";        
    ?>