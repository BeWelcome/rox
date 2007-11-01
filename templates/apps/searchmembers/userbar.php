<?php
$words = new MOD_words();
?>

           <h3>Actions</h3>
           <ul class="linklist">
<?php // TODO: Add new words to database 
?>
            <li><a style="cursor:pointer;" onClick="$('FindPeopleFilter').toggle();"><?php echo $words->getFormatted('FindPeopleFilter'); ?></a></li>
            <li><a style="cursor:pointer;" onClick="$('FindPeopleResults').toggle();"><?php echo $words->getFormatted('FindPeopleResults'); ?></a></li>
	        <li><a href="searchmembers/index"><?php echo $words->getFormatted('SearchNew'); ?></a></li>
	        <li><a href="searchmembers/simple"><?php echo $words->getFormatted('SearchSimple'); ?></a></li>
					
           </ul>
           
           <h3>Map Actions</h3>	
           <ul class="linklist">
           	   
		   <input class="button" type="button" value="Clear the map"
	        onclick="map.clearOverlays(); put_html('member_list', '');"/>&nbsp;	        
            <input class="button" type="button" value="Disable map"
        	onclick="window.location='searchmembers/index/mapoff';"/>
            <input class="button" type="button" value="Enable map"
        	onclick="window.location='searchmembers/index';"/>        	
           </ul>