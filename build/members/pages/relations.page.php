<?php


class RelationsPage extends ProfilePage
{    

    protected function column_col3()
    {
		$words = new MOD_words();
		if (isset($_GET['delete']) && isset($_GET['IdRelation'])) {
			$vars['confirm'] = 'No';
			$vars['IdOwner'] = $_SESSION['IdMember'];
			$vars['IdRelation'] = $_GET['IdRelation'];
			$this->model->deleteRelation($vars);
		}
		$member = $this->member;
		?>
		<h3><?=$words->get('MyRelations');?></h3>
		<ul class="linklist">
			<?php
				$relations = $member->relations;
				foreach ($relations as $rel) {
			?>
  		  <li class="floatbox">
    		<span class="float_left">
    		<a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>"  title="See profile <?=$rel->Username?>">
      			<img class="framed"  src="members/avatar/<?=$rel->Username?>?xs"  height="50px"  width="50px"  alt="Profile" >
    		</a>
    		</span>
    		<a href="<?=PVars::getObj('env')->baseuri."members/".$rel->Username?>" ><?=$rel->Username?></a>
			<br />
            <?=$rel->Comment?>
            <? if ($this->myself) : ?>
            	<br />
                <a class="button" href="<?=PVars::getObj('env')->baseuri."members/".$member->Username."/relations?delete&IdRelation=".$rel->id?>" onclick="return confirm('Are you sure?');"><?=$words->get('Delete')?></a>
            <? endif ?>
		  </li>
		  <?php } ?>
		</ul>
		<?php
	}
}




?>