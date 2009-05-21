	<?php echo $words->getFormatted("LastCommentsExplanation",$iiMax) ; ?>


	<table class="full">
	
	<tr>
		<th><?=$words->getFormatted("CommentFrom","") ?></th>
		<th><?=$words->getFormatted("CommentTo") ?></th>
		<th><?=$words->getFormatted("CommentWhen") ?></th>
		<th><?=$words->getFormatted("CommentText") ?></th>
		<th></th>
	</tr>
	<?php
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$c = $data[$ii];
	?>
		<?php 
		echo "<tr class=\"",$styles[$ii%2],"\">" ;
		?>
		<td>
           <?php  
		echo "<a href=\"members/".$c->UsernameFrom."\"><img src=\"members/avatar/".$c->UsernameFrom."\"></a>" ;
		echo "<a class=\"username\" href=\"bw/member.php?cid=",$c->UsernameFrom,"\">",$c->UsernameFrom,"</a>" ; 
		echo "<br /><a  href=\"members/",$c->UsernameFrom,"/comments\">",$words->getFormatted("ViewComments"),"(".$c->FromNbComment.")</a>" ; 
		echo "<br />",$c->CountryNameFrom ;
		?>
		</td>
		<td>
           <?php  
		echo "<a href=\"members/".$c->UsernameTo."\"><img src=\"members/avatar/".$c->UsernameTo."\"></a>" ;
		echo "<a class=\"username\" href=\"bw/member.php?cid=",$c->UsernameTo,"\">",$c->UsernameTo,"</a>" ; 
		echo "<br /><a  href=\"members/",$c->UsernameTo,"/comments\">",$words->getFormatted("ViewComments"),"(".$c->ToNbComment.")</a>" ; 
		echo "<br />",$c->CountryNameTo ;
		?>
		</td>
		<td>
		<?php 
		echo "<strong class=\"",$c->Quality,"\">" ;
		echo $words->getFormatted("CommentQuality_".$c->Quality) ;
		echo "</strong>" ;
		echo "<br /><br />" ; 
		echo MOD_layoutbits::ago($c->unix_updated); 
		?>
		</td>
		<td>
		<?php  echo $c->TextWhere,"<br/>",$c->TextFree ; ?>
		</td>
		<td>
		
		</td>
	</tr>
	<?php
	}
	?>
	</table> 
	<?php
