<?php


class RelationsPage extends ProfilePage
{    

    protected function column_col3()
    {
		$words = new MOD_words();
		$member = $this->member;
		?>
		<h3><?=$words->get('MyRelations');?></h3>
		<ul class="linklist">
			<?php
				$relations = $member->relations;
				foreach ($relations as $rel) {
			?>
		  <li>
			<a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
			  <img class="framed"  src="members/avatar/<?=$rel->Username?>?xs"  height="50px"  width="50px"  alt="Profile" >
			</a>
			<br />
			<a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
			<br />
		  </li>
		  <?php } ?>
		</ul>
		<?php
	}
}




?>