<?php
$userbarText = array();
$words = new MOD_words();
?>

           <h3><?php echo $words->get('Donate_Stats'); ?></h3>
           <p><?php echo $words->get('Donate_StatsText'); ?></p>
           <ul class="linklist">
<?php 
echo		"<li><a href=\"donate#why\">" . $words->get('Donate_Why') . "</a></li>\n";
echo		"<li><a href=\"donate#how\">" . $words->get('Donate_How') . "</a></li>\n";
echo		"<li><a href=\"donate#tax\">" . $words->get('Donate_Tax') . "</a></li>\n" ;
echo		"<li><a href=\"donate#transparency\">". $words->get('Donate_Transparency') . "</a></li>\n";
?>					
           </ul>
		   