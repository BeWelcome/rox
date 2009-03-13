<?php


class AddRelationPage extends RelationsPage
{    

    protected function column_col3()
    {
		$words = new MOD_words();
		$member = $this->member;
		$layoutkit = $this->layoutkit;
		$formkit = $layoutkit->formkit;
		$callback_tag = $formkit->setPostCallback('MembersController', 'addRelationCallback');
		$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request); 
		$TabRelationsType = $member->get_TabRelationsType();
		$MyRelation = $member->get_relation_with_member();
		?>
		<form method="post" action="<?=$page_url?>" name="relation" id="relation" enctype="multipart/form-data">
			<input type="hidden"  name="IdRelation"  value="<?=$member->id?>" />
			<input type="hidden"  name="IdOwner"  value="<?=$_SESSION['IdMember']?>" />
			<?=$callback_tag?>
			<h3><?=$words->get('AddRelation')?></h3>
			<p><?=$words->get('MyRelationListExplanation',$member->Username,$member->Username)?></p>
			<div class="row">
			<label class="grey"><?=$words->get('RelationListCategory')?></label><br />
			<?php
				$tt=$TabRelationsType;
				$max=count($tt);
				for ($ii = 0; $ii < $max; $ii++) {
					echo "<input type=checkbox name=\"Type_" . $tt[$ii] . "\"";
					if (count($MyRelation) > 0 && strpos(" ".$MyRelation->Type,$tt[$ii] )!=0)
					echo " checked ";
					echo "> ".$words->get("Relation_Type_" . $tt[$ii])."<br />";
				}
			?>
            </div>
            <div class="row">
                <label class="grey"><?=$words->get("RelationText",$member->Username)?></label>
                <textarea rows="4" cols="60" name="Comment">
                <?php
                    if (isset($TRelation->Comment)) {
                       echo $TRelation->Comment;
                    }
                ?>
                </textarea>
            </div>
			<?php
            if (isset($TRelation->id)) {
			   echo "<input type=hidden name=RelationId value=",$TRelation->id,">";
			   echo "<input type=hidden name=action value=doupdate>";
			}
			else {
			   echo "<input type=hidden name=action value=doadd>";
			}
            ?><br />
   			<input type="submit" name="submit" value="<?=$words->get('Submit')?>" />
		</form>
		<?php
	
	}
}




?>