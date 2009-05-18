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
		<tr class="<?=$styles[$ii%2] ?>">
		<td>
           <?php  
		echo "<a href=\"members/".$c->UsernameFrom."\"><img src=\"members/avatar/".$c->UsernameFrom."\"></a>" ;
		echo "<a class=\"username\" href=\"bw/member.php?cid=",$c->UsernameFrom,"\">",$c->UsernameFrom,"</a>" ; 
		echo "<br />",$c->CountryNameFrom ;
		?>
		</td>
		<td>
           <?php  
		echo "<a href=\"members/".$c->UsernameTo."\"><img src=\"members/avatar/".$c->UsernameTo."\"></a>" ;
		echo "<a class=\"username\" href=\"bw/member.php?cid=",$c->UsernameTo,"\">",$c->UsernameTo,"</a>" ; 
		echo "<br />",$c->CountryNameTo ;
		?>
		</td>
		<td>
           <?php  echo $c->updated; ?>
		</td>
		<td>
		<?php  echo $c->TextWhere,"<br/>",$c->TextFree ; ?>
		</td>
		<td>
		<?php echo $words->getFormatted("CommentQuality_".$c->Quality) ; ?>
		</td>
	</tr>
	<?php
	}
	?>
	</table> 
	<?php
