<?php
$words = new MOD_words();
?>

<div id="teaser_index"> 

<div id="teaser_l1"> 
<?php	
	// Random teaser content generation
	$chKey = rand(2,8); //case 1 is deactivated as showing random user pics doesn't work yet
	
	switch ($chKey) {
		case 1:
			echo "		<div class=\"subcolumns\">\n"; 
			// Display the last created members with a picture
			$m=$mlastpublic ;
			echo "			  <div class=\"c75l\">\n"; 
				echo "<h1>", $words->get('IndexPageWord2a'),"</h1>\n"; // Needs to be something like "Go, travel the world!"
				echo "			  <div class=\"c50l\">\n"; 
				echo "			    <div class=\"subl\">\n"; 
				echo "<h2>", $words->get('IndexPageWord1a'),"</h2>\n"; // Needs to be something like "Some are tired of discovering the world only in front of their TV:"
				echo "			    </div>\n"; 
				echo "			  </div>\n"; 
				echo "			  <div class=\"c50l\">\n"; 
				echo "			  <div class=\"c50l\">\n"; 
					echo "			    <div class=\"subl\">\n"; 
					echo "				<p class=\"floatbox UserpicFloated\">";
					echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
					echo "				</p>\n"; 
					echo "			    </div>\n"; 
				echo "			    </div>\n"; 
				echo "			  <div class=\"c50r\">\n"; 
					echo "			    <div class=\"subr\">\n"; 
					echo "				<p class=\"floatbox UserpicFloated\">";
					echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
					echo "				</p>\n"; 
					echo "			    </div>\n"; 
				echo "			    </div>\n";  
				echo "			  </div>\n"; 
			echo "			  </div>\n"; 
			
			echo "			  <div class=\"c25l\">\n"; 
			echo "			    <div class=\"subl\">\n"; 
			echo "				<p class=\"floatbox\">";
			echo "				</p>\n"; 
			echo "			    </div>\n"; 
			echo "			  </div>\n"; 
			echo "		</div>\n"; 
			
			break;
		case 2:
			echo "<h2>", $words->get('IndexPageWord1'),"</h2>\n";
			echo "<h1>", $words->get('IndexPageWord2'),"</h1>\n";
			break;
		case 3:
			echo "<h2>", $words->get('IndexPageWord1b'),"</h2>\n";
			echo "<h1>", $words->get('IndexPageWord2'),"</h1>\n";
			break;
		case 4:
			echo "<h1><span>\"", $words->get('slogan_Pathsaremadebywalking'),"\"</span></h2>\n";
			echo "<h2>Frank Kafka (1883 - 1924)</h2>\n";
			break;
		case 5:
			echo "<h1><span>\"", $words->get('slogan_Theworldisabook'),"\"</span></h2>\n";
			echo "<h2>Saint Augustin (354 - 430)</h2>\n";
			break;
		case 6:
			echo "<h1><span>\"", $words->get('slogan_Donttellme'),"\"</span></h2>\n";
			echo "<h2>Muhammad (570 - 632)</h2>\n";
			break;
		case 7:
			echo "<h1><span>\"", $words->get('slogan_Travellingislikeflirting'),"\"</span></h2>\n";
			echo "<h2>Advertisement</h2>\n";
			break;
		case 8:
			echo "<h1><span>\"Meeting people is what makes life worth living.\"</span></h2>\n";
			echo "<h2>Guy de Maupassant</h2>\n";
			// "Es sind die Begegnungen mit Menschen, die das Leben lebenswert machen." / "Meeting people is what makes life worth living.
			break;
	}
?>
</div>
<!--<div id="teaser_r"> 
</div>-->
</div>