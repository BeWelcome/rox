<?php


class DeleteTranslationPage extends EditProfilePage
{    

    protected function column_col3()
    {
		$words = new MOD_words();
		$member = $this->member;
		$layoutkit = $this->layoutkit;
		$formkit = $layoutkit->formkit;
		$callback_tag = $formkit->setPostCallback('MembersController', 'deleteTranslationCallback');
		$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request); 
		$lang = $this->model->get_profile_language();
		$profile_language = $lang->id;
		?>
		<form method="post" action="<?=$page_url?>" name="signup" id="profile" enctype="multipart/form-data">
		<input type="hidden"  name="memberid"  value="<?=$member->id?>" />
		<input type="hidden"  name="profile_language"  value="<?=$profile_language?>" />
		<?=$callback_tag?>
			<h3><?=$words->get('deleteProfileTranslation')?>: <?=$lang->Name?></h3>
			<p><?=$words->get('AreYouSure')?></p>
			<button type="submit" name="choice" value="yes" ><?=$words->get('yes')?></button>
			<button type="submit" name="choice" value="no" ><?=$words->get('no')?></button>
		</form>
		<?php
		
	}
}




?>